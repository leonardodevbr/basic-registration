<?php

namespace App\Http\Controllers;

use App\Events\DeliveryStatusUpdated;
use App\Http\Requests\BenefitDeliveryStoreRequest;
use App\Http\Requests\BenefitDeliveryUpdateRequest;
use App\Jobs\ProcessSelfieImage;
use App\Models\Benefit;
use App\Models\BenefitDelivery;
use App\Models\Person;
use App\Models\Unit;
use App\Services\RealtimeNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;
use Mpdf\Mpdf;

class BenefitDeliveryController extends Controller
{
    protected WebPushController $webPushController;
    public function __construct(Agent $agent, WebPushController $webPushController)
    {
        parent::__construct($agent);
        $this->webPushController = $webPushController;
    }

    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'status');
        $sortOrder = $request->get('sort_order', 'desc');

        // Definir ordenação padrão
        $query = BenefitDelivery::query();

        if ($sortBy === 'status' || $sortBy === 'default') {
            // Ordenação por status personalizado
            $query->orderByRaw("
            CASE
                WHEN status = 'PENDING' THEN 0
                WHEN status = 'DELIVERED' THEN 1
                WHEN status = 'REISSUED' THEN 3
                ELSE 2
            END, id $sortOrder
        ");
        } elseif ($sortBy === 'id') {
            // Ordenação apenas pelo ID
            $query->orderBy('id', $sortOrder);
        } elseif ($sortBy === 'name') {
            // Ordenação por nome da pessoa associada
            $query->join('people', 'people.id', '=', 'benefit_deliveries.person_id')
                ->orderBy('people.name', $sortOrder)
                ->select('benefit_deliveries.*');
        } elseif ($sortBy === 'created_at') {
            // Ordenação por data de criação
            $query->orderBy('created_at', $sortOrder);
        }

        $benefitDeliveries = $query->paginate($this->agent->isDesktop() ? 10 : 30)
            ->withPath(url()->current());

        if ($request->ajax()) {
            $html = view('benefit-deliveries.partials.table', compact('benefitDeliveries'))->render();
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        }

        return view('benefit-deliveries.index', compact('benefitDeliveries'));
    }

    public function create()
    {
        $benefits = Benefit::when(auth()->user()->can('update unities'), function ($query) {
            $query->when(!empty(auth()->user()->unit_id), function ($q) {
                $q->where('unit_id', auth()->user()->unit_id);
            });
        })
            ->get();
        $unities = Unit::all();
        return view('benefit-deliveries.create', compact('benefits', 'unities'));
    }

    public function store(BenefitDeliveryStoreRequest $request)
    {
        $inputData = $request->all();

        // 🔹 1️⃣ Verifica se a pessoa já existe pelo CPF
        $person = Person::where('cpf', $inputData['person']['cpf'])->first();

        $personData = [
            'name' => $inputData['person']['name'],
            'phone' => $inputData['person']['phone'] ?? null,
            'mother_name' => $inputData['person']['mother_name'] ?? null,
            'father_name' => $inputData['person']['father_name'] ?? null,
            'birth_date' => $inputData['person']['birth_date'] ?? null,
            'gender' => $inputData['person']['gender'] ?? null,
            'nis' => $inputData['person']['nis'] ?? null,
            'rg' => $inputData['person']['rg'] ?? null,
            'issuing_agency' => $inputData['person']['issuing_agency'] ?? null,
            'marital_status' => $inputData['person']['marital_status'] ?? null,
            'race_color' => $inputData['person']['race_color'] ?? null,
            'nationality' => $inputData['person']['nationality'] ?? null,
            'naturalness' => $inputData['person']['naturalness'] ?? null,
        ];

        if ($person) {
            $person->update($personData);

            // 🔹 2️⃣ Impede duplicidade de benefício
            $existingBenefit = BenefitDelivery::where('person_id', $person->id)
                ->where('benefit_id', $inputData['benefit_id'])
                ->whereIn('status', ['PENDING', 'DELIVERED'])
                ->exists();

            if ($existingBenefit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Essa pessoa já possui este benefício ativo ou entregue.',
                ], 422);
            }

        } else {
            $person = Person::create(array_merge(
                ['cpf' => $inputData['person']['cpf']],
                $personData
            ));
        }

        // 🔹 3️⃣ Endereços (1:N) — se vierem
        if (!empty($inputData['person']['addresses']) && is_array($inputData['person']['addresses'])) {
            foreach ($inputData['person']['addresses'] as $address) {
                $person->addresses()->create([
                    'zipcode' => $address['zipcode'] ?? null,
                    'street' => $address['street'] ?? null,
                    'number' => $address['number'] ?? null,
                    'complement' => $address['complement'] ?? null,
                    'neighborhood' => $address['neighborhood'] ?? null,
                    'city' => $address['city'] ?? null,
                    'state' => $address['state'] ?? null,
                    'latitude' => $address['latitude'] ?? null,
                    'longitude' => $address['longitude'] ?? null,
                    'type' => $address['type'] ?? null,
                    'reference' => $address['reference'] ?? null,
                ]);
            }
        }

        // 🔹 4️⃣ Se veio selfie, processa
        if (!empty($inputData['person']['selfie'])) {
            $base64Image = str_replace('data:image/png;base64,', '', $inputData['person']['selfie']);
            $cacheKey = 'selfie_' . uniqid('selfie_', true);
            Cache::put($cacheKey, $base64Image, now()->addMinutes(10));
            ProcessSelfieImage::dispatchAfterResponse($cacheKey, $person->id);
        }

        // 🔹 5️⃣ Criar código do ticket
        $ticketCode = random_int(100000, 999999);
        $validUntil = now()->addWeek();

        $benefitDelivery = BenefitDelivery::create([
            'benefit_id' => $inputData['benefit_id'],
            'person_id' => $person->id,
            'ticket_code' => $ticketCode,
            'valid_until' => $validUntil,
            'status' => 'PENDING',
            'registered_by_id' => auth()->check() ? auth()->user()->id : null,
            'delivered_at' => null,
            'unit_id' => auth()->user()->unit_id ?? $inputData['unit_id'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registro Efetuado',
            'data' => [
                'benefit_delivery' => $benefitDelivery,
                'person' => $person,
                'ticket_code' => $ticketCode
            ],
        ]);
    }


    public function edit(BenefitDelivery $benefitDelivery)
    {
        $benefits = Benefit::when(auth()->user()->can('update unities'), function ($query) {
            $query->when(!empty(auth()->user()->unit_id), function ($q) {
                $q->where('unit_id', auth()->user()->unit_id);
            });
        })
            ->get();
        $unities = Unit::all();
        return view('benefit-deliveries.edit', compact('benefitDelivery', 'benefits', 'unities'));
    }

    public function update(BenefitDeliveryUpdateRequest $request, BenefitDelivery $benefitDelivery)
    {
        $inputData = $request->all();
        $person = $benefitDelivery->person;

        // 🔹 1️⃣ Impedir duplicidade de benefício
        $existingBenefit = BenefitDelivery::where('person_id', $person->id)
            ->where('benefit_id', $inputData['benefit_id'])
            ->whereIn('status', ['PENDING', 'DELIVERED'])
            ->where('id', '!=', $benefitDelivery->id)
            ->exists();

        if ($existingBenefit) {
            return response()->json([
                'success' => false,
                'message' => 'Essa pessoa já possui este benefício ativo ou entregue.',
            ], 422);
        }

        // 🔹 2️⃣ Atualizar dados da pessoa (exceto CPF)
        $personData = [
            'name' => $inputData['person']['name'],
            'phone' => $inputData['person']['phone'] ?? $person->phone,
            'mother_name' => $inputData['person']['mother_name'] ?? null,
            'father_name' => $inputData['person']['father_name'] ?? null,
            'birth_date' => $inputData['person']['birth_date'] ?? null,
            'gender' => $inputData['person']['gender'] ?? null,
            'nis' => $inputData['person']['nis'] ?? null,
            'rg' => $inputData['person']['rg'] ?? null,
            'issuing_agency' => $inputData['person']['issuing_agency'] ?? null,
            'marital_status' => $inputData['person']['marital_status'] ?? null,
            'race_color' => $inputData['person']['race_color'] ?? null,
            'nationality' => $inputData['person']['nationality'] ?? null,
            'naturalness' => $inputData['person']['naturalness'] ?? null,
        ];

        $person->update($personData);

        // 🔹 3️⃣ Atualiza selfie se enviada
        if (!empty($inputData['person']['selfie'])) {
            $base64Image = str_replace('data:image/png;base64,', '', $inputData['person']['selfie']);
            $cacheKey = 'selfie_' . uniqid('selfie_', true);

            Cache::put($cacheKey, $base64Image, now()->addMinutes(10));

            ProcessSelfieImage::dispatchAfterResponse(
                $cacheKey,
                $person->id,
                $person->selfie_path,
                $person->thumb_path
            );

            $person->update([
                'selfie_path' => "",
                'thumb_path' => "",
            ]);
        }

        // 🔹 4️⃣ Atualizar endereços se enviados
        if (!empty($inputData['person']['addresses']) && is_array($inputData['person']['addresses'])) {
            $person->addresses()->delete(); // Remove todos os antigos

            foreach ($inputData['person']['addresses'] as $address) {
                $person->addresses()->create([
                    'zipcode' => $address['zipcode'] ?? null,
                    'street' => $address['street'] ?? null,
                    'number' => $address['number'] ?? null,
                    'complement' => $address['complement'] ?? null,
                    'neighborhood' => $address['neighborhood'] ?? null,
                    'city' => $address['city'] ?? null,
                    'state' => $address['state'] ?? null,
                    'latitude' => $address['latitude'] ?? null,
                    'longitude' => $address['longitude'] ?? null,
                    'type' => $address['type'] ?? null,
                    'reference' => $address['reference'] ?? null,
                ]);
            }
        }

        // 🔹 5️⃣ Atualizar a entrega
        $dataToUpdate = [
            'benefit_id' => $inputData['benefit_id']
        ];

        if (!empty($inputData['unit_id']) && auth()->user()->can('update unities')) {
            $dataToUpdate['unit_id'] = $inputData['unit_id'];
        }

        $benefitDelivery->update($dataToUpdate);

        return response()->json([
            'success' => true,
            'message' => 'Registro Atualizado',
            'data' => [
                'benefit_delivery' => $benefitDelivery,
                'person' => $person,
                'ticket_code' => $benefitDelivery->ticket_code
            ],
        ]);
    }

    public function destroy(BenefitDelivery $benefitDelivery)
    {
        try {
            $benefitDelivery->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir: '.$e->getMessage()
            ], 500);
        }
    }

    public function show(int $benefitDeliveryId)
    {
        $person = BenefitDelivery::with('person', 'benefit', 'registeredBy', 'deliveredBy')->find($benefitDeliveryId);
        return response()->json($person);
    }

    public function deliver(BenefitDelivery $benefitDelivery)
    {
        if ($benefitDelivery->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Esta entrega não está pendente ou já foi finalizada.'
            ], 400);
        }

        $benefitDelivery->update([
            'status' => 'DELIVERED',
            'delivered_at' => now(),
            'delivered_by_id' => auth()->check() ? auth()->user()->id : null,
        ]);
        $this->notifyStatusUpdated($benefitDelivery);

        return response()->json([
            'success' => true,
            'message' => 'Entrega registrada com sucesso!'
        ]);
    }

    public function filter(Request $request)
    {
        $filter = $request->get('filter', '');

        $benefitDeliveries = BenefitDelivery::with(['person', 'benefit'])
            ->when($filter, function ($query, $filter) {
                $query->where('ticket_code', 'like', "%{$filter}%")
                    ->orWhereHas('person', function ($q) use ($filter) {
                        $q->where('cpf', 'like', "%{$filter}%")
                            ->orWhere('name', 'like', "%{$filter}%");
                    });
            })
            ->orderByRaw("CASE WHEN status = 'REISSUED' THEN 1 ELSE 0 END, id DESC")
            ->get(); // 🔥 Removemos o `paginate(10)`, agora traz **todos os registros**

        $html = view('benefit-deliveries._table_body', ['deliveries' => $benefitDeliveries])->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function quickDeliver(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
        ]);

        $ticketCode = $request->input('ticket_code');

        $benefitDelivery = BenefitDelivery::where('ticket_code', $ticketCode)
            ->where('status', 'PENDING')
            ->first();

        if (!$benefitDelivery) {
            return response()->json([
                'success' => false,
                'message' => 'Entrega não encontrada ou já finalizada.'
            ], 404);
        }

        $benefitDelivery->update([
            'status' => 'DELIVERED',
            'delivered_at' => now(),
            'delivered_by_id' => auth()->check() ? auth()->user()->id : null,
        ]);
        $this->notifyStatusUpdated($benefitDelivery);

        return response()->json([
            'success' => true,
            'message' => 'Baixa realizada com sucesso!'
        ]);
    }

    public function reissue($id)
    {
        // Encontrar o benefício original
        $oldBenefit = BenefitDelivery::findOrFail($id);

        // Atualizar o status do antigo registro para "REISSUED"
        $oldBenefit->update(['status' => 'REISSUED']);
        $this->notifyStatusUpdated($oldBenefit);

        // Gerar novo ticket
        $ticketCode = random_int(100000, 999999);
        $validUntil = now()->addWeek();

        // Criar novo registro usando os dados da pessoa original
        $newBenefit = BenefitDelivery::create([
            'benefit_id' => $oldBenefit->benefit_id,
            'person_id' => $oldBenefit->person_id,
            'ticket_code' => $ticketCode,
            'valid_until' => $validUntil,
            'status' => 'PENDING',
            'registered_by_id' => auth()->id(),
            'unit_id' => $oldBenefit->unit_id,
            'reissued_from' => $oldBenefit->id, // Relaciona com o registro antigo
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Novo ticket reemitido com sucesso!',
            'data' => [
                'benefit_delivery' => $newBenefit,
                'ticket_code' => $ticketCode,
                'previous_id' => $oldBenefit->id,
            ],
        ]);
    }

    public function generateReceipt($id)
    {
        // 🔹 Buscar a entrega do benefício e carregar seus relacionamentos
        $benefitDelivery = BenefitDelivery::with(['person', 'benefit', 'registeredBy', 'deliveredBy'])
            ->findOrFail($id);

        // 🔹 Gerar o HTML do recibo usando uma view Blade específica
        $html = View::make('benefit-deliveries.receipt', compact('benefitDelivery'))->render();

        // 🔹 Criar uma nova instância do mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P', // P = Retrato (Portrait), L = Paisagem (Landscape)
            'default_font' => 'Arial'
        ]);

        // 🔹 Definir o HTML no mPDF
        $mpdf->WriteHTML($html);

        // 🔹 Definir nome do arquivo PDF
        $fileName = "Recibo_Beneficio_{$benefitDelivery->id}.pdf";


        // 🔹 Exibir o PDF diretamente no navegador
        $mpdf->Output("Recibo_Beneficio_{$benefitDelivery->id}.pdf", 'I'); // 'I' = Inline (abre no navegador)
    }

    private function notifyStatusUpdated(BenefitDelivery $benefitDelivery): void
    {
        $this->webPushController->sendNotification($benefitDelivery);
        RealtimeNotificationService::trigger('benefit-status', 'status.updated', [
            'personId'   => $benefitDelivery->person_id,
            'status'     => $benefitDelivery->status,
            'updatedBy' => auth()->id()
        ]);
    }

}

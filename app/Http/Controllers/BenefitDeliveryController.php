<?php

namespace App\Http\Controllers;

use App\Http\Requests\BenefitDeliveryStoreRequest;
use App\Http\Requests\BenefitDeliveryUpdateRequest;
use App\Jobs\ProcessSelfieImage;
use App\Models\Base\Benefit;
use App\Models\Base\BenefitDelivery;
use App\Models\Person;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class BenefitDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $benefitDeliveries = BenefitDelivery::orderByRaw(
            "CASE
        WHEN status = 'REISSUED' THEN 1 ELSE 0 END, id DESC"
        )
            ->paginate($this->agent->isDesktop() ? 10 : 30)->withPath(url()->current());

        if ($request->ajax()) {
            return view('benefit-deliveries.partials.table', compact('benefitDeliveries'))->render();
        }

        return view('benefit-deliveries.index', compact('benefitDeliveries'));
    }

    public function create()
    {
        $benefits = Benefit::all();
        return view('benefit-deliveries.create', compact('benefits'));
    }

    public function store(BenefitDeliveryStoreRequest $request)
    {
        $inputData = $request->all();
        // 🔹 1️⃣ Verifica se a pessoa já existe pelo CPF
        $person = Person::where('cpf', $inputData['person']['cpf'])->first();

        if ($person) {
            // Atualiza os dados da pessoa existente
            $person->update([
                'name' => $inputData['person']['name'],
                'phone' => $inputData['person']['phone'] ?? $person->phone,
            ]);

            // 🔹 2️⃣ Impede cadastrar o mesmo benefício para a mesma pessoa se estiver PENDENTE ou ENTREGUE
            $existingBenefit = BenefitDelivery::where('person_id', $person->id)
                ->where('benefit_id', $inputData['benefit_id']) // ✅ Apenas para o mesmo benefício
                ->whereIn('status', ['PENDING', 'DELIVERED']) // ✅ Apenas se estiver pendente ou entregue
                ->exists();

            if ($existingBenefit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Essa pessoa já possui este benefício ativo ou entregue.',
                ], 422);
            }

        } else {
            // 🔹 Criar uma nova pessoa caso não exista
            $person = Person::create([
                'name' => $inputData['person']['name'],
                'cpf' => $inputData['person']['cpf'],
                'phone' => $inputData['person']['phone'] ?? null,
            ]);
        }

        // 🔹 Se veio selfie nova, salva a imagem temporariamente e envia para processamento
        if (!empty($inputData['person']['selfie'])) {
            // Remove o prefixo do Base64
            $base64Image = str_replace('data:image/png;base64,', '', $inputData['person']['selfie']);

            // Gerar um identificador único para a imagem
            $cacheKey = 'selfie_' . uniqid('selfie_', true);

            // Armazena a imagem no cache (expira em 10 minutos para evitar acúmulo)
            Cache::put($cacheKey, $base64Image, now()->addMinutes(10));

            // Chama o job passando apenas a chave do cache
            ProcessSelfieImage::dispatchAfterResponse($cacheKey, $person->id);
        }

        // 🔹 Gerar código do ticket e definir validade
        $ticketCode = random_int(100000, 999999);
        $validUntil = now()->addWeek();

        // 🔹 Criar a entrega do benefício
        $benefitDelivery = BenefitDelivery::create([
            'benefit_id' => $inputData['benefit_id'],
            'person_id' => $person->id,
            'ticket_code' => $ticketCode,
            'valid_until' => $validUntil,
            'status' => 'PENDING',
            'registered_by' => auth()->check() ? auth()->user()->id : null,
            'delivered_at' => null,
            'unit_id' => $inputData['unit_id'] ?? null,
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
        $benefits = Benefit::all();
        return view('benefit-deliveries.edit', compact('benefitDelivery', 'benefits'));
    }

    public function update(BenefitDeliveryUpdateRequest $request, BenefitDelivery $benefitDelivery)
    {
        $inputData = $request->all();
        $person = $benefitDelivery->person;

        // 🔹 1️⃣ Impedir atualização se já existir esse benefício para essa pessoa com status PENDENTE ou ENTREGUE
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

        // 🔹 2️⃣ Atualizar os dados da pessoa (exceto CPF)
        $person->update([
            'name' => $inputData['person']['name'],
            'phone' => $inputData['person']['phone'] ?? $person->phone,
        ]);

        // 🔹 3️⃣ Processar selfie nova apenas se foi enviada
        if (!empty($inputData['person']['selfie'])) {
            // Remove o prefixo do Base64
            $base64Image = str_replace('data:image/png;base64,', '', $inputData['person']['selfie']);

            // Gerar um identificador único para a imagem
            $cacheKey = 'selfie_' . uniqid('selfie_', true);

            // 🔥 Salvar a imagem no cache com expiração de 10 minutos
            Cache::put($cacheKey, $base64Image, now()->addMinutes(10));

            // 🔹 Chama o job, enviando também as imagens antigas para remoção
            ProcessSelfieImage::dispatchAfterResponse(
                $cacheKey,
                $person->id,
                $person->selfie_path, // Selfie antiga
                $person->thumb_path   // Thumbnail antiga
            );

            $person->update([
                'selfie_path' => "",
                'thumb_path' => "",
            ]);
        }

        // 🔹 4️⃣ Atualizar a entrega do benefício
        $benefitDelivery->update([
            'benefit_id' => $inputData['benefit_id'],
        ]);

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
        $person = BenefitDelivery::with('person', 'benefit', 'registered_by', 'delivered_by')->find($benefitDeliveryId);
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
            'delivered_by' => auth()->check() ? auth()->user()->id : null,
        ]);

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
            'delivered_by' => auth()->check() ? auth()->user()->id : null,
        ]);

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
            'registered_by' => auth()->id(),
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

}

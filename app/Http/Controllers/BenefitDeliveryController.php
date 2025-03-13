<?php

namespace App\Http\Controllers;

use App\Http\Requests\BenefitDeliveryStoreRequest;
use App\Http\Requests\BenefitDeliveryUpdateRequest;
use App\Models\Base\Benefit;
use App\Models\Base\BenefitDelivery;
use App\Models\Person;
use Google\Cloud\Storage\StorageClient;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class BenefitDeliveryController extends Controller
{
    public function index()
    {
        $benefitDeliveries = BenefitDelivery::paginate(10);
        return view('benefit-deliveries.index', compact('benefitDeliveries'));
    }

    public function create()
    {
        $benefits = Benefit::all();
        return view('benefit-deliveries.create', compact('benefits'));
    }

    public function store(BenefitDeliveryStoreRequest $request)
    {
        $validated = $request->validated();

        // Decode the base64 selfie image
        $imageData = base64_decode(str_replace('data:image/png;base64,', '', $validated['person']['selfie']));

        // Create unique file names
        $selfieName = 'selfies/' . uniqid() . '.png';
        $thumbName  = 'selfies/thumbs/' . uniqid() . '.png';

        $manager = new ImageManager(new Driver());

        // Process full image
        $imageFull = $manager->read($imageData)
            ->cover(500, 500)
            ->encode();

        // Process thumbnail
        $imageThumb = $manager->read($imageData)
            ->cover(150, 150)
            ->encode();

        // Initialize Google Cloud Storage
        $storage = new StorageClient(['keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE_PATH')]);
        $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));

        // Upload images
        $bucket->upload($imageFull, ['name' => $selfieName]);
        $bucket->upload($imageThumb, ['name' => $thumbName]);

        // Create Person record
        $person = Person::create([
            'name'        => $validated['person']['name'],
            'cpf'         => $validated['person']['cpf'],
            'phone'       => $validated['person']['phone'] ?? null,
            'selfie_path' => $selfieName,
            'thumb_path'  => $thumbName,
        ]);

        // Generate a 6-digit password code
        $passwordCode = random_int(100000, 999999);

        // Define validity period (e.g., 1 hour from now)
        $validUntil = now()->addHour();

        // Create BenefitDelivery record with new columns
        $benefitDelivery = BenefitDelivery::create([
            'benefit_id'    => $validated['benefit_id'],
            'person_id'     => $person->id,
            'password_code' => $passwordCode,
            'valid_until'   => $validUntil,
            'status'        => 'PENDING',
            'registered_by' => auth()->check() ? auth()->user()->id : null,
            'delivered_at'  => null,
            'unit_id'       => $validated['unit_id'] ?? null,
        ]);

        // Return JSON response for AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Benefit delivery registered successfully!',
                'data'    => [
                    'benefit_delivery' => $benefitDelivery,
                    'person' => $person,
                    'password_code' => $passwordCode
                ],
            ]);
        }

        return redirect()->route('benefit-deliveries.index')
            ->with('success', 'Benefit delivery registered successfully!');
    }


    public function edit(BenefitDelivery $benefitDelivery)
    {
        $benefits = Benefit::all();
        return view('benefit-deliveries.edit', compact('benefitDelivery', 'benefits'));
    }

    public function update(BenefitDeliveryUpdateRequest $request, BenefitDelivery $benefitDelivery)
    {
        $validated = $request->validated();
        $person = $benefitDelivery->person;

        // Inicializa o Google Cloud Storage
        $storage = new StorageClient(['keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE_PATH')]);
        $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));

        if (!empty($validated['person']['selfie'])) {
            // Decodifica a nova selfie Base64 corretamente
            $imageData = base64_decode(str_replace('data:image/png;base64,', '', $validated['person']['selfie']));

            // Criar nomes únicos
            $selfieName = 'selfies/' . uniqid() . '.png';
            $thumbName = 'selfies/thumbs/' . uniqid() . '.png';

            // Criar as imagens com Intervention
            $manager = new ImageManager(new Driver());

            // Criar a imagem completa
            $imageFull = $manager->read($imageData)
                ->cover(500, 500) // Recorte quadrado centralizado
                ->encode();

            // Criar a miniatura quadrada sem distorção
            $imageThumb = $manager->read($imageData)
                ->cover(150, 150) // Mantém proporção e corta centralizado
                ->encode();

            // Excluir as imagens antigas no Google Cloud Storage
            if (!empty($person->selfie_path)) {
                $bucket->object($person->selfie_path)->delete();
            }
            if (!empty($person->thumb_path)) {
                $bucket->object($person->thumb_path)->delete();
            }

            // Upload da nova imagem grande
            $bucket->upload($imageFull, ['name' => $selfieName]);

            // Upload da nova thumbnail
            $bucket->upload($imageThumb, ['name' => $thumbName]);

            // Atualizar os caminhos das imagens
            $validated['person']['selfie_path'] = $selfieName;
            $validated['person']['thumb_path'] = $thumbName;
        }

        // Atualizar os dados da pessoa
        $person->update([
            'name' => $validated['person']['name'],
            'cpf' => $validated['person']['cpf'],
            'phone' => $validated['person']['phone'] ?? null,
            'selfie_path' => $validated['person']['selfie_path'] ?? $person->selfie_path,
            'thumb_path' => $validated['person']['thumb_path'] ?? $person->thumb_path,
        ]);

        // Atualizar a entrega do benefício
        $benefitDelivery->update([
            'benefit_id' => $validated['benefit_id'],
        ]);

        return redirect()->route('benefit-deliveries.index')->with('success', 'Registro de entrega atualizado com sucesso!');
    }

    public function destroy(BenefitDelivery $benefitDelivery)
    {
        $benefitDelivery->delete();
        return redirect()->route('benefit-deliveries.index')->with('success', 'Registro de entrega excluído com sucesso!');
    }

    public function show(int $benefitDeliveryId)
    {
        $person = BenefitDelivery::with('person', 'benefit')->find($benefitDeliveryId);
        return response()->json($person);
    }
}

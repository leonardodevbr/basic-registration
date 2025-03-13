<?php
namespace App\Http\Controllers;

use App\Http\Requests\BenefitDeliveryStoreRequest;
use App\Http\Requests\BenefitDeliveryUpdateRequest;
use App\Models\Person;
use Google\Cloud\Storage\StorageClient;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PersonController extends Controller
{
    public function index()
    {
        $people = Person::paginate(10);
        return view('people.index', compact('people'));
    }

    public function create()
    {
        return view('people.create');
    }

    public function store(BenefitDeliveryStoreRequest $request)
    {
        $imageData = base64_decode(str_replace('data:image/png;base64,', '', $request->selfie));

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

        // Inicializa o Google Cloud Storage
        $storage = new StorageClient(['keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE_PATH')]);
        $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));

        // Upload da imagem grande
        $bucket->upload($imageFull, ['name' => $selfieName]);
        // Upload da thumbnail
        $bucket->upload($imageThumb, ['name' => $thumbName]);

        // Criar o registro no banco
        $person = Person::create([
            'name' => $request->validated()['name'],
            'cpf' => $request->validated()['cpf'],
            'phone' => $request->validated()['phone'],
            'selfie_path' => $selfieName,
            'thumb_path' => $thumbName, // Salvamos o caminho da versão menor
        ]);

        return redirect()->route('people.index')->with('success', 'Pessoa cadastrada com selfie!');
    }

    public function edit(Person $person)
    {
        return view('people.edit', compact('person'));
    }

    public function update(BenefitDeliveryUpdateRequest $request, Person $person)
    {
        $data = $request->validated();

        // Inicializa o Google Cloud Storage
        $storage = new StorageClient(['keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE_PATH')]);
        $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));

        if ($request->has('selfie') && !empty($request->selfie)) {
            // Decodificar a nova selfie
            $imageData = base64_decode(str_replace('data:image/png;base64,', '', $request->selfie));

            // Criar nomes únicos para a nova imagem
            $selfieName = 'selfies/' . uniqid() . '.png';
            $thumbName = 'selfies/thumbs/' . uniqid() . '.png';

            // Criar imagens com Intervention Image
            $manager = new ImageManager(new Driver());

            // Criar a imagem original
            $imageFull = $manager->read($imageData)
                ->cover(600, 600) // Recorte quadrado centralizado
                ->encode();

            // Criar a miniatura quadrada sem distorção
            $imageThumb = $manager->read($imageData)
                ->cover(150, 150) // Recorte quadrado centralizado
                ->encode();

            // Excluir as imagens antigas no Google Cloud Storage
            if ($person->selfie_path) {
                $bucket->object($person->selfie_path)->delete();
            }
            if ($person->thumb_path) {
                $bucket->object($person->thumb_path)->delete();
            }

            // Upload das novas imagens
            $bucket->upload($imageFull, ['name' => $selfieName]);
            $bucket->upload($imageThumb, ['name' => $thumbName]);

            // Atualizar os caminhos no banco de dados
            $data['selfie_path'] = $selfieName;
            $data['thumb_path'] = $thumbName;
        }

        // Atualizar os dados da pessoa
        $person->update($data);

        return redirect()->route('people.index')->with('success', 'Dados da pessoa atualizados com sucesso!');
    }

    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()->route('people.index')->with('success', 'Pessoa excluída com sucesso!');
    }

    public function show($personId)
    {
        $person = Person::find($personId);
        return response()->json($person);
    }
}

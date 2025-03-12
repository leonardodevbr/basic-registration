<?php
namespace App\Http\Controllers;

use App\Http\Requests\PersonStoreRequest;
use App\Http\Requests\PersonUpdateRequest;
use App\Models\Person;
use App\Services\FaceRecognitionService;
use App\Services\GoogleVisionService;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;

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

    public function store(PersonStoreRequest $request)
    {
        // Decodificar a selfie
        $imageData = base64_decode(str_replace('data:image/png;base64,', '', $request->selfie));
        $imagePath = storage_path('app/temp_selfie.png');
        file_put_contents($imagePath, $imageData);

        // Verificar se a selfie tem um rosto válido
        if (!GoogleVisionService::detectFace($imagePath)) {
            unlink($imagePath);
            return redirect()->back()->withErrors('Nenhum rosto detectado na selfie.');
        }

        // Salvar no Laravel Storage
        $imageName = 'selfies/' . uniqid() . '.png';
        Storage::disk('public')->put($imageName, $imageData);

        // Criar o registro no banco
        $person = Person::create([
            'name' => $request->validated()['name'],
            'cpf' => $request->validated()['cpf'],
            'phone' => $request->validated()['phone'],
            'selfie_path' => $imageName,
        ]);

        return redirect()->route('people.index')->with('success', 'Pessoa cadastrada com selfie!');
    }

    public function edit(Person $person)
    {
        return view('people.edit', compact('person'));
    }

    public function update(PersonUpdateRequest $request, Person $person)
    {
        $person->update($request->validated());

        return redirect()->route('people.index')->with('success', 'Pessoa atualizada com sucesso!');
    }

    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()->route('people.index')->with('success', 'Pessoa excluída com sucesso!');
    }
}

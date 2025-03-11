<?php
namespace App\Http\Controllers;

use App\Http\Requests\PersonStoreRequest;
use App\Http\Requests\PersonUpdateRequest;
use App\Models\Person;

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
        Person::create($request->validated());

        return redirect()->route('people.index')->with('success', 'Pessoa cadastrada com sucesso!');
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

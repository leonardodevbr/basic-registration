<?php

namespace App\Http\Controllers;

use App\Models\BenefitDelivery;
use Illuminate\Http\Request;

class BenefitDeliveryController extends Controller
{
    public function index()
    {
        $deliveries = BenefitDelivery::with('person')->paginate(10);
        return view('benefit_deliveries.index', compact('deliveries'));
    }

    public function create()
    {
        $people = \App\Models\Person::all();
        return view('benefit_deliveries.create', compact('people'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'person_id' => 'required|exists:people,id',
            'selfie' => 'required|image|max:2048',
        ]);

        if (BenefitDelivery::where('person_id', $request->person_id)->exists()) {
            return back()->withErrors('Benefício já entregue.');
        }

        $selfie_path = $request->file('selfie')->store('selfies');

        BenefitDelivery::create([
            'person_id' => $request->person_id,
            'delivered_at' => now(),
            'selfie_path' => $selfie_path,
        ]);

        return redirect()->route('benefit-deliveries.index')->with('success', 'Entrega registrada com sucesso!');
    }

    public function show(BenefitDelivery $benefitDelivery)
    {
        $benefitDelivery->load('person');
        return view('benefit_deliveries.show', compact('benefitDelivery'));
    }

    public function edit(BenefitDelivery $benefitDelivery)
    {
        return view('benefit_deliveries.edit', compact('benefitDelivery'));
    }

    public function update(Request $request, BenefitDelivery $benefitDelivery)
    {
        $request->validate([
            'selfie' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('selfie')) {
            $selfie_path = $request->file('selfie')->store('selfies');
            $benefitDelivery->selfie_path = $selfie_path;
        }

        $benefitDelivery->save();

        return redirect()->route('benefit-deliveries.index')->with('success', 'Entrega atualizada com sucesso!');
    }

    public function destroy(BenefitDelivery $benefitDelivery)
    {
        $benefitDelivery->delete();
        return redirect()->route('benefit-deliveries.index')->with('success', 'Entrega excluída com sucesso!');
    }
}

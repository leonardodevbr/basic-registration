<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BenefitDeliveryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'benefit_id' => 'required|integer|exists:benefits,id',

            'person' => 'required|array',
            'person.cpf' => 'required|string|size:14',
            'person.name' => 'required|string|max:255',
            'person.phone' => 'nullable|string|min:10|max:15',
            'person.selfie' => ['required', 'string', 'regex:/^data:image\/(png|jpeg|jpg);base64,/'],

            // Novos campos pessoais
            'person.mother_name' => 'nullable|string|max:255',
            'person.father_name' => 'nullable|string|max:255',
            'person.birth_date' => 'nullable|date|before:today',
            'person.gender' => 'nullable|in:masculino,feminino,outro',
            'person.nis' => 'nullable|string|max:20',
            'person.rg' => 'nullable|string|max:20',
            'person.issuing_agency' => 'nullable|string|max:50',
            'person.marital_status' => 'nullable|string|max:50',
            'person.race_color' => 'nullable|string|max:50',
            'person.nationality' => 'nullable|string|max:100',
            'person.naturalness' => 'nullable|string|max:100',

            // Endereços (opcional)
            'person.addresses' => 'nullable|array',
            'person.addresses.*.zipcode' => 'nullable|string|max:10',
            'person.addresses.*.street' => 'nullable|string|max:255',
            'person.addresses.*.number' => 'nullable|string|max:20',
            'person.addresses.*.complement' => 'nullable|string|max:100',
            'person.addresses.*.neighborhood' => 'nullable|string|max:100',
            'person.addresses.*.city' => 'nullable|string|max:100',
            'person.addresses.*.state' => 'nullable|string|max:2',
            'person.addresses.*.latitude' => 'nullable|numeric|between:-90,90',
            'person.addresses.*.longitude' => 'nullable|numeric|between:-180,180',
            'person.addresses.*.type' => 'nullable|string|max:50',
            'person.addresses.*.reference' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'benefit_id.required' => 'O benefício é obrigatório.',
            'person.name.required' => 'O nome é obrigatório.',
            'person.cpf.required' => 'O CPF é obrigatório.',
            'person.cpf.size' => 'O CPF deve conter exatamente 11 números.',
            'person.phone.min' => 'O telefone deve conter no mínimo 10 números.',
            'person.phone.max' => 'O telefone deve conter no máximo 15 caracteres.',
            'person.selfie.required' => 'A selfie é obrigatória.',
            'person.selfie.regex' => 'A selfie deve ser uma imagem válida em base64.',
            'person.birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            'person.gender.in' => 'O campo gênero deve ser: masculino, feminino ou outro.',
        ];
    }
}

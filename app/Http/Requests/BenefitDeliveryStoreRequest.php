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
            'person.phone.max' => 'O telefone deve conter no máximo 11 números.',
            'person.selfie.required' => 'A selfie é obrigatória.',
            'person.selfie.starts_with' => 'A selfie deve ser uma imagem válida em base64.',
        ];
    }
}

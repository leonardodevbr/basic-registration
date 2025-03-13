<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BenefitDeliveryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'person' => [
                'name' => $this->input('person.name'),
                'cpf' => preg_replace('/\D/', '', $this->input('person.cpf', '')),
                'phone' => preg_replace('/\D/', '', $this->input('person.phone', '')),
                'selfie' => $this->input('person.selfie')
            ],
        ]);
    }

    public function rules(): array
    {
        return [
            'benefit_id' => 'required|integer|exists:benefits,id',

            'person' => 'required|array',
            'person.cpf' => 'required|string|size:11|unique:people,cpf',
            'person.name' => 'required|string|max:255',
            'person.phone' => 'nullable|string|min:10|max:11',
            'person.selfie' => ['required', 'string', 'regex:/^data:image\/(png|jpeg|jpg);base64,/'],
        ];
    }

    public function messages(): array
    {
        return [
            'benefit_id.required' => 'O benefício é obrigatório.',
            'person.name.required' => 'O nome é obrigatório.',
            'person.cpf.required' => 'O CPF é obrigatório.',
            'person.cpf.unique' => 'Esse CPF já está cadastrado.',
            'person.cpf.size' => 'O CPF deve conter exatamente 11 números.',
            'person.phone.min' => 'O telefone deve conter no mínimo 10 números.',
            'person.phone.max' => 'O telefone deve conter no máximo 11 números.',
            'person.selfie.required' => 'A selfie é obrigatória.',
            'person.selfie.starts_with' => 'A selfie deve ser uma imagem válida em base64.',
        ];
    }
}

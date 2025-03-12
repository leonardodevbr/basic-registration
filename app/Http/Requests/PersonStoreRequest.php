<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'cpf' => preg_replace('/\D/', '', $this->cpf),
            'phone' => preg_replace('/\D/', '', $this->phone),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:people,cpf',
            'phone' => 'nullable|string|max:20',
            'selfie' => 'required|string|starts_with:data:image/png;base64,',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Esse CPF já está cadastrado.',
            'cpf.size' => 'O CPF deve conter exatamente 11 números.',
            'selfie.required' => 'A selfie é obrigatória.',
            'selfie.starts_with' => 'A selfie deve ser uma imagem válida em base64.',
        ];
    }
}

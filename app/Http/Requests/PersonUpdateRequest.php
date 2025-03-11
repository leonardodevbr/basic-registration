<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonUpdateRequest extends FormRequest
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
        $personId = $this->route('person')->id;

        return [
            'name' => 'required|string|max:255',
            'cpf' => "required|string|size:11|unique:people,cpf,{$personId}",
            'phone' => 'nullable|string|min:10|max:11',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Esse CPF já está cadastrado.',
            'cpf.size' => 'O CPF deve conter exatamente 11 números.',
            'phone.min' => 'O telefone deve conter no mínimo 10 números.',
            'phone.max' => 'O telefone deve conter no máximo 11 números.',
        ];
    }
}

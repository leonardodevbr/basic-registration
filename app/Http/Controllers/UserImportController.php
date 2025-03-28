<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserImportController extends Controller
{
    public function form()
    {
        return view('users.import');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx']);

        $file = $request->file('file');
        $rows = Excel::toArray([], $file)[0];

        unset($rows[0]); // remove cabeçalho

        foreach ($rows as $row) {
            $name = $row[0] ?? null;
            $cpf = preg_replace('/\D/', '', $row[1] ?? '');
            $email = $row[2] ?? null;

            if (!$name || !$cpf || !$email) continue;

            $existing = User::where('email', $email)->orWhere('cpf', $cpf)->first();
            if ($existing) continue;

            $user = new User();
            $user->name = $name;
            $user->cpf = $cpf;
            $user->email = $email;
            $user->registration_number = 'MATR' . rand(1000, 9999);
            $user->password = Hash::make(substr($cpf, 0, 6));
            $user->save();

            $user->assignRole('Funcionario');
        }

        return back()->with('success', 'Importação concluída com sucesso!');
    }
}

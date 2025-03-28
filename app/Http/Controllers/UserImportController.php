<?php

namespace App\Http\Controllers;

use App\Models\Person;
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
        $rows = Excel::toArray((object)[], $file)[0];

        unset($rows[0]); // remove cabeçalho

        foreach ($rows as $row) {
            $registrationNumber = $row[0] ?? null;
            $name = $row[1] ?? null;
            $cpf = preg_replace('/\D/', '', $row[2] ?? '');
            $email = $row[3] ?? null;

            if (!$registrationNumber || !$name || !$cpf || !$email) continue;

            // 🔹 Cria o usuário apenas se não existir
            $existing = User::where('email', $email)->first();
            if ($existing) continue;

            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->registration_number = $registrationNumber;
            $user->password = Hash::make(substr($cpf, 0, 6));
            $user->save();

            $user->assignRole('Colaborador');

            // 🔹 Atualiza ou cria a pessoa
            $person = Person::where('cpf', $cpf)->first();
            if ($person) {
                $person->update([
                    'name' => $name,
                ]);
            } else {
                $person = Person::create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'cpf' => $cpf,
                ]);
            }
        }

        return back()->with('success', 'Importação concluída com sucesso!');
    }
}

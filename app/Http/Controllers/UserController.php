<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        return redirect()->route('access-control.users');
    }

    public function create()
    {
        $roles = Role::all();
        return view('access-control.users.create', [
            'user' => new User(),
            'roles' => $roles,
            'action' => route('users.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->filled('roles')) {
            $user->roles()->sync($request->roles);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Usuário criado com sucesso!']);
        }

        return redirect()->route('access-control.users')->with('success', 'Usuário criado com sucesso!');
    }

    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('access-control.users.show', compact('user'));
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();

        return view('access-control.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'action' => route('users.update', $user->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($request->filled('roles')) {
            $user->roles()->sync($request->roles);
        } else {
            $user->roles()->detach();
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Usuário atualizado com sucesso!']);
        }

        return redirect()->route('access-control.users')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Usuário removido com sucesso!']);
        }

        return redirect()->route('access-control.users')->with('success', 'Usuário removido com sucesso!');
    }
}

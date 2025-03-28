<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        return redirect()->route('access-control.users');
    }

    public function create()
    {
        return view('access-control.users.create', [
            'user' => new User(),
            'roles' => Role::all(),
            'permissions' => Permission::all(),
            'action' => route('users.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'registration_number' => 'string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $deniedIds = $request->input('denied_permissions', []);
            $deniedNames = Permission::whereIn('id', $deniedIds)->pluck('name')->toArray();

            $user = User::create([
                'registration_number' => $request->registration_number ?? "",
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'denied_permissions' => $deniedNames
            ]);

            $rolesPermissions = collect(Role::find($request->input('roles', [])))
                ->flatMap(fn($role) => $role->permissions)
                ->pluck('id')
                ->unique()
                ->toArray();

            $selectedPermissions = $request->input('permissions', []);
            $directPermissions = array_diff($selectedPermissions, $rolesPermissions);

            $user->permissions()->sync($directPermissions);

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Usuário criado com sucesso!']);
            }

            return redirect()->route('access-control.users')->with('success', 'Usuário criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erro ao criar usuário.', 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors('Erro ao criar usuário: ' . $e->getMessage());
        }
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
        $permissions = Permission::all();

        $inheritedPermissions = $user->roles
            ->flatMap(fn($role) => $role->permissions)
            ->pluck('id')
            ->unique()
            ->toArray();

        return view('access-control.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
            'inheritedPermissions' => $inheritedPermissions,
            'action' => route('users.update', $user->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'registration_number' => 'string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'denied_permissions' => 'array',
        ]);

        try {
            DB::beginTransaction();

            $deniedIds = $request->input('denied_permissions', []);
            $deniedNames = Permission::whereIn('id', $deniedIds)->pluck('name')->toArray();

            $user->registration_number = $request->registration_number ?? $user->registration_number;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->denied_permissions = $deniedNames;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // ✅ Sincroniza roles normalmente
            $user->roles()->sync($request->input('roles', []));

            // 🧠 Agora só sincroniza as permissões diretas, sem incluir as herdadas
            $rolesPermissions = $user->roles
                ->flatMap(fn($role) => $role->permissions)
                ->pluck('id')
                ->unique()
                ->toArray();

            $selectedPermissions = $request->input('permissions', []);

            // 🔍 Remove as herdadas do array antes de sincronizar
            $directPermissions = array_diff($selectedPermissions, $rolesPermissions);

            $user->permissions()->sync($directPermissions);

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Usuário atualizado com sucesso!']);
            }

            return redirect()->route('access-control.users')->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erro ao atualizar usuário.', 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors('Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            $user->delete();
            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Usuário removido com sucesso!']);
            }

            return redirect()->route('access-control.users')->with('success', 'Usuário removido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erro ao remover usuário.', 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors('Erro ao remover usuário: ' . $e->getMessage());
        }
    }
}

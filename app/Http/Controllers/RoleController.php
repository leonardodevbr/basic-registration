<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        return redirect()->route('access-control.roles');
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('access-control.roles.create', [
            'permissions' => $permissions,
            'role' => new Role(),
            'action' => route('roles.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name]);
        $permissionNames = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();
        $role->syncPermissions($permissionNames);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Papel criado com sucesso!']);
        }

        return redirect()->route('access-control.roles')->with('success', 'Papel criado com sucesso!');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('access-control.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'action' => route('roles.update', $role->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);
        $permissionNames = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();
        $role->syncPermissions($permissionNames);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Papel atualizado com sucesso!']);
        }

        return redirect()->route('access-control.roles')->with('success', 'Papel atualizado com sucesso!');
    }

    public function destroy(Request $request, Role $role)
    {
        $role->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Papel removido com sucesso!']);
        }

        return redirect()->route('access-control.roles')->with('success', 'Papel removido com sucesso!');
    }
}

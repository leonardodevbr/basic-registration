<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return view('roles.index', compact('permissions'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions,name']);
        Permission::create(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Permissão criada com sucesso!');
    }

    public function edit(Permission $permission)
    {
        return view('roles.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate(['name' => 'required|unique:permissions,name,' . $permission->id]);
        $permission->update(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Permissão atualizada com sucesso!');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('roles.index')->with('success', 'Permissão removida com sucesso!');
    }
}

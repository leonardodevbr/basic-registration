<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return redirect()->route('access-control.permissions');
    }

    public function create()
    {
        return view('access-control.permissions.create', [
            'permission' => new Permission(),
            'action' => route('permissions.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        Permission::create(['name' => $request->name]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Permissão criada com sucesso!']);
        }

        return redirect()->route('access-control.permissions')->with('success', 'Permissão criada com sucesso!');
    }

    public function edit(Permission $permission)
    {
        return view('access-control.permissions.edit', [
            'permission' => $permission,
            'action' => route('permissions.update', $permission->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Permissão atualizada com sucesso!']);
        }

        return redirect()->route('access-control.permissions')->with('success', 'Permissão atualizada com sucesso!');
    }

    public function destroy(Request $request, Permission $permission)
    {
        $permission->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Permissão removida com sucesso!']);
        }

        return redirect()->route('access-control.permissions')->with('success', 'Permissão removida com sucesso!');
    }
}

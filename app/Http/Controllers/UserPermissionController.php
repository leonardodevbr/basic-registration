<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPermissionController extends Controller
{
    public function edit(User $user)
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('users.permissions', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'permissions' => 'array'
        ]);

        $user->syncRoles($request->roles ?? []);
        $user->syncPermissions($request->permissions ?? []);

        return redirect()->route('users.permissions.edit', $user)->with('success', 'Permissões atualizadas!');
    }
}

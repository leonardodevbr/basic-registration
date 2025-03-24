<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessControlController extends Controller
{
    public function index()
    {
        $roles = Role::where("name", "!=", "SuperAdmin")->get();
        $permissions = Permission::orderBy('module', 'asc')->get();
        $users = User::where('id', '!=', auth()->id())->get();

        $route = request()->route()->getName();

        $tab = match ($route) {
            'access-control.permissions' => 'permissions',
            'access-control.users' => 'users',
            default => 'roles',
        };

        return view('access-control.index', compact('roles', 'permissions', 'users', 'tab'));
    }
}

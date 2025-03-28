<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Guard;

class CustomPermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission, $guard = null)
    {
        $authGuard = Auth::guard($guard);
        $user = $authGuard->user();

        // Suporte a Passport (client_credentials)
        if (! $user && $request->bearerToken() && config('permission.use_passport_client_credentials')) {
            $user = Guard::getPassportClient($guard);
        }

        if (! $user) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (! method_exists($user, 'hasAnyPermission')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

        // ⚠️ Bypass para SuperAdmin
        if ($user->hasRole('SuperAdmin')) {
            return $next($request);
        }

        // Trata permissão como array ou string separada por "|"
        $permissions = explode('|', self::parsePermissionsToString($permission));

        foreach ($permissions as $perm) {
            // 🔴 Se o usuário tiver essa permissão na lista de bloqueios explícitos
            if (in_array($perm, $user->denied_permissions ?? [])) {
                throw UnauthorizedException::forPermissions([$perm]);
            }

            // ✅ Permissão direta
            if ($user->hasDirectPermission($perm)) {
                return $next($request);
            }

            // ✅ Permissão via roles
            if ($user->hasPermissionTo($perm)) {
                return $next($request);
            }
        }

        throw UnauthorizedException::forPermissions($permissions);
    }

    public static function using(array|string|\BackedEnum $permission, $guard = null): string
    {
        $permissionString = self::parsePermissionsToString($permission);
        $args = is_null($guard) ? $permissionString : "$permissionString,$guard";
        return static::class.':'.$args;
    }

    protected static function parsePermissionsToString(array|string|\BackedEnum $permission): string
    {
        if ($permission instanceof \BackedEnum) {
            return $permission->value;
        }

        if (is_array($permission)) {
            return implode('|', array_map(fn ($p) => $p instanceof \BackedEnum ? $p->value : $p, $permission));
        }

        return (string) $permission;
    }
}

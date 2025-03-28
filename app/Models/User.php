<?php

namespace App\Models;

use App\Models\Base\User as BaseUser;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends BaseUser
{
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // Bloqueio manual via denied_permissions (por nome)
        if (in_array($permission, $this->denied_permissions ?? [])) {
            return false;
        }

        // Continua o fluxo normal da trait HasRoles
        return parent::hasPermissionTo($permission, $guardName);
    }
}

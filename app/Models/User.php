<?php

namespace App\Models;

use App\Models\Base\User as BaseUser;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseUser
{
    use HasRoles;
}

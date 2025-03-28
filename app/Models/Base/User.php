<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BenefitDelivery;
use App\Models\Person;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @property int $id
 * @property int|null $unit_id
 * @property string|null $registration_number
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property array|null $denied_permissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Unit|null $unit
 * @property Collection|BenefitDelivery[] $benefit_deliveries
 * @property Collection|Person[] $people
 *
 * @package App\Models\Base
 */
class User extends \Illuminate\Foundation\Auth\User
{
	use HasPermissions;
	use HasRoles;
	protected $table = 'users';

	protected $casts = [
		'unit_id' => 'int',
		'email_verified_at' => 'datetime',
		'denied_permissions' => 'array'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'unit_id',
		'registration_number',
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token',
		'denied_permissions'
	];

	public function unit(): BelongsTo
	{
		return $this->belongsTo(Unit::class);
	}

	public function benefit_deliveries(): HasMany
	{
		return $this->hasMany(BenefitDelivery::class, 'registered_by_id');
	}

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'user_id', 'id');
    }
}

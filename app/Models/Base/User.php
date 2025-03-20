<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BenefitDelivery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|BenefitDelivery[] $benefit_deliveries
 *
 * @package App\Models\Base
 */
class User extends \Illuminate\Foundation\Auth\User
{
	use HasFactory;
	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token'
	];

	public function benefit_deliveries(): HasMany
	{
		return $this->hasMany(BenefitDelivery::class, 'registered_by_id');
	}
}

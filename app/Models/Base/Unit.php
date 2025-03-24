<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Benefit;
use App\Models\BenefitDelivery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Unit
 * 
 * @property int $id
 * @property string $name
 * @property string $city
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|BenefitDelivery[] $benefit_deliveries
 * @property Collection|Benefit[] $benefits
 * @property Collection|User[] $users
 *
 * @package App\Models\Base
 */
class Unit extends Model
{
	use HasFactory;
	protected $table = 'units';

	protected $fillable = [
		'name',
		'city'
	];

	public function benefit_deliveries(): HasMany
	{
		return $this->hasMany(BenefitDelivery::class);
	}

	public function benefits(): HasMany
	{
		return $this->hasMany(Benefit::class);
	}

	public function users(): HasMany
	{
		return $this->hasMany(User::class);
	}
}

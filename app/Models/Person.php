<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Person
 * 
 * @property int $id
 * @property string $name
 * @property string $cpf
 * @property string|null $phone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|BenefitDelivery[] $benefit_deliveries
 *
 * @package App\Models
 */
class Person extends Model
{
	use HasFactory;
	protected $table = 'people';

	protected $fillable = [
		'name',
		'cpf',
		'phone'
	];

	public function benefit_deliveries(): HasMany
	{
		return $this->hasMany(BenefitDelivery::class);
	}
}

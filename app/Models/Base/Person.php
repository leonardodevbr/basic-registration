<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BenefitDelivery;
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
 * @property string|null $selfie_path
 * @property string|null $thumb_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|BenefitDelivery[] $benefit_deliveries
 *
 * @package App\Models\Base
 */
class Person extends Model
{
	use HasFactory;
	protected $table = 'people';

	protected $fillable = [
		'name',
		'cpf',
		'phone',
		'selfie_path',
		'thumb_path'
	];

	public function benefit_deliveries(): HasMany
	{
		return $this->hasMany(BenefitDelivery::class);
	}
}

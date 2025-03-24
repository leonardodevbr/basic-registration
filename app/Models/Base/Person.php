<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BenefitDelivery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Person
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string $cpf
 * @property string|null $phone
 * @property string|null $selfie_path
 * @property string|null $thumb_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User|null $user
 * @property Collection|BenefitDelivery[] $benefit_deliveries
 *
 * @package App\Models\Base
 */
class Person extends Model
{
	use HasFactory;
	protected $table = 'people';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'name',
		'cpf',
		'phone',
		'selfie_path',
		'thumb_path'
	];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function benefit_deliveries(): HasMany
	{
		return $this->hasMany(BenefitDelivery::class);
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BenefitDelivery;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Benefit
 * 
 * @property int $id
 * @property int|null $unit_id
 * @property string $name
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Unit|null $unit
 * @property Collection|BenefitDelivery[] $benefit_deliveries
 *
 * @package App\Models\Base
 */
class Benefit extends Model
{
	use HasFactory;
	protected $table = 'benefits';

	protected $casts = [
		'unit_id' => 'int'
	];

	protected $fillable = [
		'unit_id',
		'name',
		'description'
	];

	public function unit(): BelongsTo
	{
		return $this->belongsTo(Unit::class);
	}

	public function benefit_deliveries(): HasMany
	{
		return $this->hasMany(BenefitDelivery::class);
	}
}

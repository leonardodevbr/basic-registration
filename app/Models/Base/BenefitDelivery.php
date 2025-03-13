<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Benefit;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BenefitDelivery
 * 
 * @property int $id
 * @property int $benefit_id
 * @property int $person_id
 * @property Carbon $delivered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Benefit $benefit
 * @property Person $person
 *
 * @package App\Models\Base
 */
class BenefitDelivery extends Model
{
	use HasFactory;
	protected $table = 'benefit_deliveries';

	protected $casts = [
		'benefit_id' => 'int',
		'person_id' => 'int',
		'delivered_at' => 'datetime'
	];

	protected $fillable = [
		'benefit_id',
		'person_id',
		'delivered_at'
	];

	public function benefit(): BelongsTo
	{
		return $this->belongsTo(Benefit::class);
	}

	public function person(): BelongsTo
	{
		return $this->belongsTo(Person::class);
	}
}

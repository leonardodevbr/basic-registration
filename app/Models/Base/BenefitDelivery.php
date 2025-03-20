<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Benefit;
use App\Models\Person;
use App\Models\Unit;
use App\Models\User;
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
 * @property string $ticket_code
 * @property Carbon $valid_until
 * @property string $status
 * @property int|null $registered_by_id
 * @property int|null $delivered_by_id
 * @property int|null $unit_id
 * @property Carbon|null $delivered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Benefit $benefit
 * @property User|null $user
 * @property Person $person
 * @property Unit|null $unit
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
		'valid_until' => 'datetime',
		'registered_by_id' => 'int',
		'delivered_by_id' => 'int',
		'unit_id' => 'int',
		'delivered_at' => 'datetime'
	];

	protected $fillable = [
		'benefit_id',
		'person_id',
		'ticket_code',
		'valid_until',
		'status',
		'registered_by_id',
		'delivered_by_id',
		'unit_id',
		'delivered_at'
	];

	public function benefit(): BelongsTo
	{
		return $this->belongsTo(Benefit::class);
	}

	public function registeredBy(): BelongsTo
	{
		return $this->belongsTo(User::class, 'registered_by_id');
	}

	public function deliveredBy(): BelongsTo
	{
		return $this->belongsTo(User::class, 'delivered_by_id');
	}

	public function person(): BelongsTo
	{
		return $this->belongsTo(Person::class);
	}

	public function unit(): BelongsTo
	{
		return $this->belongsTo(Unit::class);
	}
}

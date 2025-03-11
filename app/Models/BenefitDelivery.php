<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BenefitDelivery
 * 
 * @property int $id
 * @property int $person_id
 * @property Carbon $delivered_at
 * @property string $selfie_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Person $person
 *
 * @package App\Models
 */
class BenefitDelivery extends Model
{
	use HasFactory;
	protected $table = 'benefit_deliveries';

	protected $casts = [
		'person_id' => 'int',
		'delivered_at' => 'datetime'
	];

	protected $fillable = [
		'person_id',
		'delivered_at',
		'selfie_path'
	];

	public function person(): BelongsTo
	{
		return $this->belongsTo(Person::class);
	}
}

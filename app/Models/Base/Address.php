<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Address
 * 
 * @property int $id
 * @property int $person_id
 * @property string|null $zipcode
 * @property string|null $street
 * @property string|null $number
 * @property string|null $complement
 * @property string|null $neighborhood
 * @property string|null $city
 * @property string|null $state
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $type
 * @property string|null $reference
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Person $person
 *
 * @package App\Models\Base
 */
class Address extends Model
{
	use HasFactory;
	protected $table = 'addresses';

	protected $casts = [
		'person_id' => 'int',
		'latitude' => 'float',
		'longitude' => 'float'
	];

	protected $fillable = [
		'person_id',
		'zipcode',
		'street',
		'number',
		'complement',
		'neighborhood',
		'city',
		'state',
		'latitude',
		'longitude',
		'type',
		'reference'
	];

	public function person(): BelongsTo
	{
		return $this->belongsTo(Person::class);
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
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
 * @package App\Models
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

    protected $appends = [
        'selfie_url',
        'thumb_url'
    ];

    public function getSelfieUrlAttribute()
    {
        return $this->generateSignedUrl($this->selfie_path);
    }

    public function getThumbUrlAttribute()
    {
        return $this->generateSignedUrl($this->thumb_path);
    }

    private function generateSignedUrl($path)
    {
        $storage = new StorageClient(['keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE_PATH')]);
        $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
        $object = $bucket->object($path);

        return $object->signedUrl(new \DateTime('5 minutes'), ['version' => 'v4']);
    }

	public function benefit_deliveries(): HasMany
	{
		return $this->hasMany(BenefitDelivery::class);
	}
}

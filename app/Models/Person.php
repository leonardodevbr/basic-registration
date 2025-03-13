<?php

namespace App\Models;

use App\Models\Base\Person as BasePerson;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Cache;

class Person extends BasePerson
{
    protected $appends = [
        'selfie_url',
        'thumb_url'
    ];

    public function getSelfieUrlAttribute()
    {
        return $this->getCachedSignedUrl('selfie_url', $this->selfie_path);
    }

    public function getThumbUrlAttribute()
    {
        return $this->getCachedSignedUrl('thumb_url', $this->thumb_path);
    }

    private function getCachedSignedUrl($type, $path)
    {
        if (!$path) {
            return null;
        }

        $cacheKey = "person:{$this->id}:{$type}";

        // Armazena no cache por 4 minutos e 50 segundos (evita gerar novas URLs antes da expiração)
        return Cache::remember($cacheKey, now()->addMinutes(4)->addSeconds(50), function () use ($path) {
            return $this->generateSignedUrl($path);
        });
    }

    private function generateSignedUrl($path)
    {
        $storage = new StorageClient(['keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE_PATH')]);
        $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
        $object = $bucket->object($path);

        return $object->signedUrl(new \DateTime('5 minutes'), ['version' => 'v4']);
    }
}

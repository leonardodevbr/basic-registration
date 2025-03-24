<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InjectPersonAccessors extends Command
{
    protected $signature = 'inject:person-accessors';
    protected $description = 'Injeta os accessors e appends customizados no model Person.php';

    public function handle(): void
    {
        $modelPath = app_path('Models/Person.php');

        if (!File::exists($modelPath)) {
            $this->error('Model Person.php não encontrado.');
            return;
        }

        $content = File::get($modelPath);

        if (str_contains($content, 'getSelfieUrlAttribute')) {
            $this->info('Accessors já estão presentes no model.');
            return;
        }

        $injection = <<<'EOD'

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

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(4)->addSeconds(50), function () use ($path) {
            return $this->generateSignedUrl($path);
        });
    }

    private function generateSignedUrl($path)
    {
        $storage = new \Google\Cloud\Storage\StorageClient(['keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE_PATH')]);
        $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
        $object = $bucket->object($path);

        return $object->signedUrl(new \DateTime('5 minutes'), ['version' => 'v4']);
    }
EOD;

        $updated = preg_replace('/}\s*$/', $injection . "\n}", $content);

        File::put($modelPath, $updated);

        $this->info('Accessors e appends adicionados ao model Person com sucesso.');
    }
}

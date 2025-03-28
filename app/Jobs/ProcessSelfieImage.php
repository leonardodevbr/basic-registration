<?php

namespace App\Jobs;

use App\Events\SelfieUpdated;
use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Google\Cloud\Storage\StorageClient;

class ProcessSelfieImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cacheKey;
    protected $personId;
    protected $oldSelfie;
    protected $oldThumb;

    public function __construct($cacheKey, $personId, $oldSelfie = null, $oldThumb = null)
    {
        $this->cacheKey = $cacheKey;
        $this->personId = $personId;
        $this->oldSelfie = $oldSelfie;
        $this->oldThumb = $oldThumb;
    }

    public function handle()
    {
        Log::info("🔹 Iniciando processamento da selfie da pessoa ID: {$this->personId}");

        // Recupera a imagem do cache
        $base64Image = Cache::pull($this->cacheKey); // `pull` remove a chave automaticamente

        if (!$base64Image) {
            Log::error("❌ Falha ao recuperar a imagem do cache: {$this->cacheKey}");
            return;
        }

        // Decodifica a imagem
        $imageData = base64_decode($base64Image);
        if (!$imageData) {
            Log::error("❌ Erro ao decodificar imagem Base64 para a pessoa ID: {$this->personId}");
            return;
        }

        $randName = uniqid();
        $selfieName = 'selfies/' . $randName . '.png';
        $thumbName = 'selfies/thumbs/' . $randName . '.png';

        $manager = new ImageManager(new Driver());
        $imageFull = $manager->read($imageData)->cover(500, 500)->encode();
        $imageThumb = $manager->read($imageData)->cover(150, 150)->encode();

        $storage = new StorageClient(['keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE_PATH')]);
        $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));

        // 🔹 Removendo imagens antigas do Storage
        try {
            if (!empty($this->oldSelfie)) {
                Log::info("🗑️ Removendo selfie antiga: {$this->oldSelfie}");
                $bucket->object($this->oldSelfie)->delete();
            }
            if (!empty($this->oldThumb)) {
                Log::info("🗑️ Removendo thumbnail antiga: {$this->oldThumb}");
                $bucket->object($this->oldThumb)->delete();
            }
        } catch (\Exception $e) {
            Log::error("❌ Erro ao remover imagens antigas: " . $e->getMessage());
        }

        // 🔹 Upload das novas imagens
        try {
            Log::info("🚀 Fazendo upload da imagem principal: {$selfieName}");
            $bucket->upload($imageFull, ['name' => $selfieName]);

            Log::info("🚀 Fazendo upload da thumbnail: {$thumbName}");
            $bucket->upload($imageThumb, ['name' => $thumbName]);
        } catch (\Exception $e) {
            Log::error("❌ Erro ao fazer upload das imagens: " . $e->getMessage());
            return;
        }

        // 🔹 Atualiza os caminhos no banco de dados
        try {
            $person = Person::find($this->personId);
            if ($person) {
                $person->update([
                    'selfie_path' => $selfieName,
                    'thumb_path' => $thumbName,
                ]);

                // 🔥 Limpa o cache das URLs assinadas para gerar novas
                Cache::forget("person:{$this->personId}:selfie_url");
                Cache::forget("person:{$this->personId}:thumb_url");

                Log::info("✅ Selfie processada com sucesso para a pessoa ID: {$this->personId}");
            }
            event(new SelfieUpdated($person->id, $person->thumb_url));
        } catch (\Exception $e) {
            Log::error("❌ Erro ao atualizar caminhos no banco: " . $e->getMessage());
        }
    }
}

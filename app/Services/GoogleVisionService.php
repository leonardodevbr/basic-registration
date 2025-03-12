<?php
namespace App\Services;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class GoogleVisionService
{
    public static function detectFace($imagePath)
    {
        $imageAnnotator = new ImageAnnotatorClient([
            'keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE_PATH'),
        ]);

        $imageContent = file_get_contents($imagePath);
        $response = $imageAnnotator->faceDetection($imageContent);
        $faces = $response->getFaceAnnotations();

        return count($faces) > 0; // Retorna true se houver um rosto detectado
    }
}

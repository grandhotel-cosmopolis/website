<?php

namespace Database\Factories;

use App\Models\FileUpload;
use App\Traits\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 *@extends Factory<FileUpload>
 */
class FileUploadFactory extends Factory
{
    use Upload;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stub = __DIR__ . '/test.png';
        $file = new UploadedFile($stub, 'test.png', 'image/png', null, true);
        $uploadedFile = $this->UploadFile($file);
        return [
            'file_path' => $uploadedFile,
            'mime_type' => $file->getMimeType(),
            'guid' => uuid_create()
        ];
    }
}

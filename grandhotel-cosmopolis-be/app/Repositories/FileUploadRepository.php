<?php

namespace App\Repositories;

use App\Models\FileUpload;
use App\Models\User;
use App\Repositories\Interfaces\IFileUploadRepository;
use Illuminate\Support\Facades\Auth;

class FileUploadRepository implements IFileUploadRepository
{
    public function create(string $filePath, string $mimeType): FileUpload
    {
        $fileUpload = new FileUpload([
            'file_path' => $filePath,
            'mime_type' => $mimeType,
            'guid' => uuid_create()
        ]);

        /** @var User $user */
        $user = Auth::user();

        $fileUpload->uploadedBy()->associate($user);
        $fileUpload->save();
        return $fileUpload;
    }
}

<?php

namespace App\Repositories\Interfaces;

use App\Models\FileUpload;

interface IFileUploadRepository
{
    public function create(string $filePath, string $mimeType): FileUpload;
}

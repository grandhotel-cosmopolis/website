<?php

namespace App\Http\Dtos\File;

use App\Models\FileUpload;
use OpenApi\Attributes as OA;

#[OA\Schema]
class FileDto
{
    #[OA\Property]
    public string $fileUrl;

    #[OA\Property]
    public string $mimeType;

    public function __construct(string $fileUrl, string $mimeType) {
        $this->fileUrl = $fileUrl;
        $this->mimeType = $mimeType;
    }

    public static function create(FileUpload $fileUpload): FileDto {
        $fileUrl = config('app.url') . '/storage/' . $fileUpload->file_path;
        return new FileDto(
            $fileUrl,
            $fileUpload->mime_type
        );
    }
}

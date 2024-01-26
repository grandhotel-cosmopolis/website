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

    #[OA\Property]
    public string $guid;

    public function __construct(string $fileUrl, string $mimeType, string $guid) {
        $this->fileUrl = $fileUrl;
        $this->mimeType = $mimeType;
        $this->guid = $guid;
    }

    public static function create(FileUpload $fileUpload): FileDto {
        $fileUrl = config('app.url') . '/storage/' . $fileUpload->file_path;
        return new FileDto(
            $fileUrl,
            $fileUpload->mime_type,
            $fileUpload->guid
        );
    }
}

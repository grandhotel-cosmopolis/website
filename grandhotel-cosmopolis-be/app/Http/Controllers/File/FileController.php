<?php

namespace App\Http\Controllers\File;

use App\Http\Dtos\File\FileDto;
use App\Models\FileUpload;
use App\Models\User;
use App\Traits\Upload;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    use Upload;

    /** @noinspection PhpUnused */
    public function uploadImage(Request $request): Response | JsonResponse
    {
        /** @var UploadedFile $test */
        $file = $request->file('test');

        $savedFile = $this->UploadFile($file);
        if (is_null($savedFile)) {
            return response('unable to store file', 500);
        }

        $fileUpload = new FileUpload;
        $fileUpload->file_path = $savedFile;
        $fileUpload->mime_type = $file->getMimeType();

        /** @var User $user */
        $user = Auth::user();
        $user->fileUploads()->save($fileUpload);

        return new JsonResponse(FileDto::create($fileUpload));
    }
}

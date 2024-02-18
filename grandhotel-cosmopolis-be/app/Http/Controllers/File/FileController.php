<?php

namespace App\Http\Controllers\File;

use App\Http\Dtos\File\FileDto;
use App\Models\FileUpload;
use App\Models\User;
use App\Repositories\Interfaces\IFileUploadRepository;
use App\Traits\Upload;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

use OpenApi\Attributes as OA;

class FileController extends Controller
{
    use Upload;

    public function __construct(
        protected IFileUploadRepository $fileUploadRepository
    ) {}

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/file/upload',
        operationId: 'uploadFile',
        description: 'Upload a single file',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary')
                    ]
                )
            )
        ),
        tags: ['File'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'uploaded file successfully',
                content: new OA\JsonContent(ref: FileDto::class)
            ),
            new OA\Response(response: 401, description: 'unauthenticated')
        ]
    )]
    public function uploadImage(Request $request): Response | JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image']
        ]);

        /** @var UploadedFile $test */
        $file = $request->file('file');

        $savedFile = $this->UploadFile($file);
        if (is_null($savedFile)) {
            return response('unable to store file', 500);
        }

        $fileUpload = $this->fileUploadRepository->create($savedFile, $file->getMimeType());

        return new JsonResponse(FileDto::create($fileUpload));
    }
}

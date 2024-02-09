<?php

namespace Tests\Unit\Repositories;

use App\Models\FileUpload;
use App\Models\User;
use App\Repositories\FileUploadRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileUploadRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FileUploadRepository $cut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cut = new FileUploadRepository();
    }

    /** @test */
    public function create_valid_fileUploadIsStoredInDb() {
        // Arrange
        $user = User::factory()->create();
        $this->be($user);
        $filePath = "path" . uuid_create();
        $mimeType = "mime";

        // Act
        $this->cut->create($filePath, $mimeType);

        // Assert
        $fileUploads = FileUpload::query()->where('file_path', $filePath)->get();
        $this->assertCount(1, $fileUploads);

        $this->assertEquals($mimeType, $fileUploads[0]['mime_type']);
    }

}

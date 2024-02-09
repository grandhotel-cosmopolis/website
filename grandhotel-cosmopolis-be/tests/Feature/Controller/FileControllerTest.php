<?php

namespace Tests\Feature\Controller;

use App\Models\Permissions;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class FileControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $basePath = "/api/file";

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /** @test */
    public function uploadImage_notLoggedIn_returnsUnauthenticated() {
        // Act
        $response = $this->put("$this->basePath/upload", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function uploadImage_notAuthorized_returnsUnauthorized() {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->put("$this->basePath/upload", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function uploadImage_valid_fileIsUploaded() {
        // Arrange
        $filePath = __DIR__.'/test.png';
        $file = new UploadedFile($filePath, 'test.pngs', filesize($filePath), null, true);
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);

        // Act
        $response = $this->actingAs($user)->put("$this->basePath/upload", ['file' => $file], ['Accept' => 'application/json']);
        // Assert
        $response->assertStatus(200);
    }

    /** @test */
    public function uploadImage_noImage_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);

        // Act
        $response = $this->actingAs($user)->put("$this->basePath/upload", ['file' => 'test'], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function uploadImage_noUploadData_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);

        // Act
        $response = $this->actingAs($user)->put("$this->basePath/upload", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }
}

<?php

namespace Tests\Feature\Controller;

use App\Models\Permissions;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $basePath = '/api/user';
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(
            Permissions::cases()
        );
    }

    /** @test */
    public function getUser_unauthenticated_return401(): void
    {
        // Act
        $response = $this->get("$this->basePath", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function getUser_authenticated_returnsCorrectUser(): void {
        // Arrange
        $user = User::factory()->make();

        // Act
        $response = $this->actingAs($user)->get("$this->basePath", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($response->json('email'), $user->email);
        $this->assertEquals($response->json('name'), $user->name);
    }

    /** @test */
    public function getUser_authenticated_returnsCorrectPermissions(): void {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo([Permissions::PUBLISH_EVENT, Permissions::CREATE_EVENT]);

        // Act
        $response = $this->actingAs($user)->get("$this->basePath", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($response->json('email'), $user->email);
        $this->assertEquals($response->json('name'), $user->name);
        $this->assertContains(Permissions::PUBLISH_EVENT->value, $response->json('permissions'));
        $this->assertContains(Permissions::CREATE_EVENT->value, $response->json('permissions'));
        $this->assertNotContains(Permissions::EDIT_EVENT->value, $response->json(('permissions')));
        $this->assertNotContains(Permissions::DELETE_EVENT->value, $response->json(('permissions')));
        $this->assertNotContains(Permissions::UNPUBLISH_EVENT->value, $response->json(('permissions')));
    }

    /** @test */
    public function list_unauthenticated_returnsUnauthenticated(): void {
        // Act
        $response = $this->get("$this->basePath/list", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function list_unauthorized_returnsUnauthorized(): void {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get("$this->basePath/list", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function list_allValid_returnsOneUser(): void {
        // Arrange
        $allPermissions  = [];
        foreach(Permissions::cases() as $permission) {
            $allPermissions[] = $permission->value;
        }

        // Act
        $response = $this->actingAs($this->user)->get("$this->basePath/list", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);

        $responseUsers = $response->json()['users'];

        $this->assertCount(1, $responseUsers);
        $this->assertEquals($this->user->name, $responseUsers[0]['name']);
        $this->assertEquals($this->user->email, $responseUsers[0]['email']);
        $this->assertEquals($allPermissions, $responseUsers[0]['permissions']);
    }
}

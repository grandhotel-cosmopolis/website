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

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /** @test */
    public function getUser_unauthenticated_return401(): void
    {
        // Act
        $response = $this->get('/api/user', ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function getUser_authenticated_returnsCorrectUser(): void {
        // Arrange
        $user = User::factory()->make();

        // Act
        $response = $this->actingAs($user)->get('/api/user', ['Accept' => 'application/json']);

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
        $response = $this->actingAs($user)->get('/api/user', ['Accept' => 'application/json']);

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
}

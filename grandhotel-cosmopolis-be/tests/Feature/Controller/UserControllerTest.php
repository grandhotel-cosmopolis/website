<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
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
}

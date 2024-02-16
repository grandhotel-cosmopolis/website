<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $basePath = '/api/login';

    /** @test */
    public function authenticate_noEmailNoPassword_returnsValidationError() {
        // Act
        $response = $this->post("$this->basePath", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertInvalid(['email', 'password']);
    }

    /** @test */
    public function authenticate_noEmail_returnsValidationError() {
        // Act
        $response = $this->post("$this->basePath", ['password' => 'test'], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertInvalid(['email']);
    }

    /** @test */
    public function authenticate_noPassword_returnsValidationError() {
        // Act
        $response = $this->post("$this->basePath", ['email' => 'test@test.de'], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertInvalid(['password']);
    }

    /** @test */
    public function authenticate_invalidEmail_returnsValidationError() {
        // Act
        $response = $this->post("$this->basePath", ['email' => 'test', 'password' => 'test'], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertInvalid(['email']);
    }

    /** @test */
    public function authenticate_notExistingUser_returnsUnauthenticated() {
        // Act
        $response = $this->post("$this->basePath", ['email' => 'test@test.de', 'password' => 'test'], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function rememberMe_previouslyNotLoggedIn_returnsUnauthenticated() {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/rememberMe", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function rememberMe_withoutUser_returnsUnauthenticated() {
        // Act
        $response = $this->post("$this->basePath/rememberMe", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }
}

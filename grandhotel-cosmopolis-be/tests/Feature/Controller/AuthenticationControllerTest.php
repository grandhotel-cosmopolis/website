<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use Tests\TestCase;

class AuthenticationControllerTest extends TestCase
{

    public function test_authenticate_noEmailNoPassword_returnsValidationError() {
        // Act
        $response = $this->post('/api/login', [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertInvalid(['email', 'password']);
    }

    public function test_authenticate_noEmail_returnsValidationError() {
        // Act
        $response = $this->post('/api/login', ['password' => 'test'], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertInvalid(['email']);
    }

    public function test_authenticate_noPassword_returnsValidationError() {
        // Act
        $response = $this->post('/api/login', ['email' => 'test@test.de'], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertInvalid(['password']);
    }

    public function test_authenticate_invalidEmail_returnsValidationError() {
        // Act
        $response = $this->post('/api/login', ['email' => 'test', 'password' => 'test'], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertInvalid(['email']);
    }

    public function test_authenticate_notExistingUser_returnsUnauthorized() {
        // Act
        $response = $this->post('/api/login', ['email' => 'test@test.de', 'password' => 'test'], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }
}

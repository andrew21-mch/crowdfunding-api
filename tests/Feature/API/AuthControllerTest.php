<?php

namespace Tests\Feature;

use App\Models\User;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_user_registration()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
        ];

        $response = $this->post('api/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User registered successfully. Please check your email for verification.',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    public function test_user_login_with_valid_credentials()
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);
    
        $userData = [
            'email' => $user->email,
            'password' => $password,
        ];
    
        $response = $this->post('api/login', $userData);
    
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message' => [
                    'token',
                ],
                'data',
                'code',
            ])
            ->assertJson([
                'code' => 200,
            ])
            ->assertJsonFragment([
                'message' => [
                    'token' => $response->json('message.token'),
                ],
            ]);
    }
    public function test_user_login_with_invalid_credentials()
    {
        $userData = [
            'email' => $this->faker->safeEmail,
            'password' => 'invalidpassword',
        ];

        $response = $this->post('api/login', $userData);


        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_user_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $response = $this->post('/api/logout', [], $headers);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'User logged out successfully',
                'data' => null,
                'code' => Response::HTTP_OK,
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
            'name' => 'test-token',
        ]);
    }
}
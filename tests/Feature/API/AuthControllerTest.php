<?php

namespace Tests\Feature;

use App\Models\User;
use DB;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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

    public function test_get_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $response = $this->get('/api/user', $headers);

        $response->assertStatus(Response::HTTP_OK); 
    }

    public function test_update_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $newName = $this->faker->name;
        $newEmail = $this->faker->safeEmail;

        $requestData = [
            'name' => $newName,
            'email' => $newEmail,
        ];

        $response = $this->put('/api/user', $requestData, $headers);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'User profile updated successfully',
                'data' => null,
                'code' => Response::HTTP_OK,
            ]);

        // Assert that the user's name and email have been updated correctly
        $this->assertEquals($newName, $user->fresh()->name);
        $this->assertEquals($newEmail, $user->fresh()->email);
    }

    public function test_change_password()
    {
        // Create a user
        $user = User::factory()->create([
            'password' => bcrypt('old_password'),
        ]);

        // Simulate the authenticated user
        $this->actingAs($user);

        // Send a request to change the password
        $response = $this->put('api/user/password', [
            'current_password' => 'old_password',
            'new_password' => 'new_password',
            'new_password_confirmation' => 'new_password',
        ]);

        // Assert the response
        $response->assertStatus(Response::HTTP_OK);
        $this->assertTrue(Hash::check('new_password', $user->fresh()->password));
    }

    public function test_send_reset_link_email()
    {
        // Create a user
        $user = User::factory()->create();

        // Send a request to send the password reset link
        $response = $this->post('api/password/email', [
            'email' => $user->email,
        ]);

        // Assert the response
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['data' => ['token']]);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_reset_password()
    {
        // Create a user
        $user = User::factory()->create();

        // Generate a password reset token
        $token = Str::random(8);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Send a request to reset the password
        $response = $this->post('api/password/reset/' . $token, [
            'email' => $user->email,
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ]);

        // Assert the response
        $response->assertStatus(Response::HTTP_OK);
        $this->assertTrue(Hash::check('new_password', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_verify_email()
    {
        // Create a user
        $user = User::factory()->create(['email_verified_at' => null]);

        // Send a request to verify the email
        $response = $this->get('api/email/verify/' . $user->id . '/' . sha1($user->getEmailForVerification()));

        // Assert the response
        $response->assertStatus(Response::HTTP_OK);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_resend_verification_email()
    {
        // Create a user
        $user = User::factory()->create(['email_verified_at' => null]);

        // Simulate the authenticated user
        $this->actingAs($user);

        // Send a request to resend the verification email
        $response = $this->post('api/email/resend');

        // Assert the response
        $response->assertStatus(Response::HTTP_OK);
    }
}
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_a_new_user()
    {
        // Data untuk registrasi
        $profile = Profile::factory()->create();
        
        $userData = [
            'profile_id' => $profile->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Kirim request registrasi
        $response = $this->postJson('/api/register', $userData);

        // Assert response
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'profile_id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);

        // Assert cookie token
        $response->assertCookie('token');

        // Assert user tersimpan di database
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Assert password di-hash
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function it_cannot_register_with_invalid_data()
    {
        // Data registrasi yang tidak valid
        $userData = [
            'profile_id' => 1,
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '1234',
        ];

        // Kirim request registrasi
        $response = $this->postJson('/api/register', $userData);

        // Assert response
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'name',
            'email',
            'password',
        ]);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        // Buat user untuk test
        $profile = Profile::factory()->create();
        $user = User::create([
            'profile_id' => $profile->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Data login
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        // Kirim request login
        $response = $this->postJson('/api/login', $loginData);

        // Assert response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
        ]);

        // Assert cookie token
        $response->assertCookie('token');
    }

    /** @test */
    public function it_cannot_login_with_invalid_credentials()
    {
        // Data login yang tidak valid
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ];

        // Kirim request login
        $response = $this->postJson('/api/login', $loginData);

        // Assert response
        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthorized'
        ]);
    }

    /** @test */
    public function it_can_refresh_token()
    {
        // Buat user untuk test
        $profile = Profile::factory()->create();
        $user = User::create([
            'profile_id' => $profile->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login untuk mendapatkan token
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $loginResponse = $this->postJson('/api/login', $loginData);
        
        // Dapatkan token dari cookie
        $cookies = $loginResponse->headers->getCookies();
        $token = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'token') {
                $token = $cookie->getValue();
                break;
            }
        }

        // Kirim request refresh token
        $response = $this->withCookie('token', $token)
            ->postJson('/api/refresh');

        // Assert response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
        ]);

        // Assert new cookie token
        $response->assertCookie('token');
    }

    /** @test */
    public function it_can_logout_user()
    {
        // Buat user untuk test
        $profile = Profile::factory()->create();
        $user = User::create([
            'profile_id' => $profile->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login untuk mendapatkan token
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $loginResponse = $this->postJson('/api/login', $loginData);
        
        // Dapatkan token dari cookie
        $cookies = $loginResponse->headers->getCookies();
        $token = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'token') {
                $token = $cookie->getValue();
                break;
            }
        }

        // Kirim request logout
        $response = $this->withCookie('token', $token)
            ->postJson('/api/logout');

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'User logged out successfully'
        ]);

        // Assert cookie is removed
        $response->assertCookieExpired('token');
    }

    /** @test */
    public function it_can_get_authenticated_user_data()
    {
        // Buat user untuk test
        $profile = Profile::factory()->create();
        $user = User::create([
            'profile_id' => $profile->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login untuk mendapatkan token
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $loginResponse = $this->postJson('/api/login', $loginData);
        
        // Dapatkan token dari cookie
        $cookies = $loginResponse->headers->getCookies();
        $token = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'token') {
                $token = $cookie->getValue();
                break;
            }
        }

        // Kirim request untuk mendapatkan data user
        $response = $this->withCookie('token', $token)
            ->getJson('/api/me');

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'profile_id' => $profile->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
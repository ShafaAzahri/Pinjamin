<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_registration_requires_ktm_and_sets_pending_status()
    {
        Storage::fake('public');

        // Use create() instead of image() to avoid GD extension dependency
        $ktmFile = UploadedFile::fake()->create('ktm.jpg', 100, 'image/jpeg');

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@student.polines.ac.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nim' => '3.32.22.0.10',
            'prodi' => 'Teknik Informatika',
            'ktm_photo' => $ktmFile,
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', [
            'email' => 'john@student.polines.ac.id',
            'role' => 'user',
            'status' => 'menunggu_verifikasi',
        ]);

        // Assert file exists in storage
        $user = User::where('email', 'john@student.polines.ac.id')->first();
        $this->assertNotNull($user->ktm_photo);
        Storage::disk('public')->assertExists($user->ktm_photo);
    }

    public function test_login_blocks_unverified_ktm_students()
    {
        $user = User::create([
            'name' => 'Unverified Student',
            'email' => 'unverified@student.polines.ac.id',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'menunggu_verifikasi',
        ]);

        $response = $this->post('/login', [
            'email' => 'unverified@student.polines.ac.id',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(auth()->check());
    }

    public function test_login_allows_active_users_and_redirects_appropriately()
    {
        $student = User::create([
            'name' => 'Active Student',
            'email' => 'active@student.polines.ac.id',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'aktif',
        ]);

        $admin = User::create([
            'name' => 'Admin Lab',
            'email' => 'admin_active@pinjamin.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // Test student login redirects to student catalog
        $response = $this->post('/login', [
            'email' => 'active@student.polines.ac.id',
            'password' => 'password',
        ]);
        $response->assertRedirect('/catalog');
        $this->assertTrue(auth()->check());
        $this->assertEquals('user', auth()->user()->role);

        auth()->logout();

        // Test admin login redirects to admin dashboard
        $response = $this->post('/login', [
            'email' => 'admin_active@pinjamin.com',
            'password' => 'password',
        ]);
        $response->assertRedirect('/admin/dashboard');
        $this->assertTrue(auth()->check());
        $this->assertEquals('admin', auth()->user()->role);
    }
}

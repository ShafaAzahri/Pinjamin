<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Loan;
use App\Models\Fine;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@pinjamin.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $this->student = User::create([
            'name' => 'John Student',
            'email' => 'student@student.polines.ac.id',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'aktif',
            'nim' => '3.32.22.0.12',
            'prodi' => 'Teknik Informatika',
        ]);
    }

    public function test_non_admins_cannot_access_admin_dashboard()
    {
        // Guest redirect
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        // Student redirect
        $this->actingAs($this->student);
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_admins_can_access_dashboard_with_statistics()
    {
        $this->actingAs($this->admin);

        // Create some stats
        $category = Category::create(['name' => 'Elektronika']);
        $item = Item::create(['category_id' => $category->id, 'name' => 'Oscilloscope']);
        $unit = ItemUnit::create(['item_id' => $item->id, 'serial_number' => 'OSC-111', 'status' => 'dipinjam']);

        $loan = Loan::create([
            'user_id' => $this->student->id,
            'status' => 'aktif',
        ]);
        
        Fine::create([
            'loan_id' => $loan->id,
            'amount' => 10000,
            'type' => 'keterlambatan',
            'status' => 'belum_dibayar',
        ]);

        // Create an unverified user
        $pendingUser = User::create([
            'name' => 'Pending Student',
            'email' => 'pending@student.polines.ac.id',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'menunggu_verifikasi',
            'nim' => '3.32.22.0.99',
            'prodi' => 'Teknik Elektro',
            'ktm_photo' => 'ktm/fake.jpg',
        ]);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('John Student'); // Active student
        $response->assertSee('10.000'); // Fines display

        // Unverified student should not be visible on Dashboard now, but visible on Verification page
        $response = $this->get('/admin/users/verification');
        $response->assertStatus(200);
        $response->assertSee('Pending Student');
        $response->assertSee('pending@student.polines.ac.id');
    }

    public function test_admin_can_approve_pending_user()
    {
        $pendingUser = User::create([
            'name' => 'Pending Student',
            'email' => 'pending@student.polines.ac.id',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'menunggu_verifikasi',
            'nim' => '3.32.22.0.99',
            'prodi' => 'Teknik Elektro',
            'ktm_photo' => 'ktm/fake.jpg',
        ]);

        $this->actingAs($this->admin);

        $response = $this->from('/admin/users/verification')->post("/admin/users/{$pendingUser->id}/verify", [
            'action' => 'approve',
        ]);

        $response->assertRedirect('/admin/users/verification');
        $this->assertEquals('aktif', $pendingUser->fresh()->status);
    }

    public function test_admin_can_reject_pending_user()
    {
        $pendingUser = User::create([
            'name' => 'Pending Student',
            'email' => 'pending@student.polines.ac.id',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'menunggu_verifikasi',
            'nim' => '3.32.22.0.99',
            'prodi' => 'Teknik Elektro',
            'ktm_photo' => 'ktm/fake.jpg',
        ]);

        $this->actingAs($this->admin);

        $response = $this->from('/admin/users/verification')->post("/admin/users/{$pendingUser->id}/verify", [
            'action' => 'reject',
        ]);

        $response->assertRedirect('/admin/users/verification');
        $this->assertDatabaseMissing('users', ['id' => $pendingUser->id]);
    }
}

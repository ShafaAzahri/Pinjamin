<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Loan;
use App\Models\Fine;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinePaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;
    private Fine $fine;

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
            'email' => 'john@student.polines.ac.id',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'aktif',
            'nim' => '3.32.22.0.12',
            'prodi' => 'Teknik Elektro',
        ]);

        $category = Category::create(['name' => 'Elektronika']);
        $item = Item::create(['name' => 'Oscilloscope', 'category_id' => $category->id]);
        $unit = ItemUnit::create(['item_id' => $item->id, 'serial_number' => 'OSC-001', 'condition' => 'baik', 'status' => 'tersedia']);

        $loan = Loan::create([
            'user_id' => $this->student->id,
            'status' => 'selesai',
            'loan_duration_hours' => 8,
        ]);

        $this->fine = Fine::create([
            'loan_id' => $loan->id,
            'amount' => 10000,
            'type' => 'keterlambatan',
            'status' => 'belum_dibayar',
        ]);

        Setting::create(['key' => 'max_loan_duration', 'value' => '8']);
        Setting::create(['key' => 'fine_per_hour', 'value' => '5000']);
        Setting::create(['key' => 'max_items_borrowed', 'value' => '3']);
    }

    public function test_student_can_upload_payment_proof()
    {
        Storage::fake('public');
        $fakeProof = UploadedFile::fake()->create('receipt.jpg', 100);

        $response = $this->actingAs($this->student)->post("/fines/{$this->fine->id}/pay", [
            'payment_proof' => $fakeProof,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('fines', [
            'id' => $this->fine->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $this->fine->refresh();
        $this->assertNotNull($this->fine->payment_proof_photo);
    }

    public function test_admin_can_approve_fine_payment()
    {
        $this->fine->update([
            'status' => 'menunggu_verifikasi',
            'payment_proof_photo' => 'payment_proofs/fake.jpg',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/fines/{$this->fine->id}/verify", [
            'action' => 'approve',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('fines', [
            'id' => $this->fine->id,
            'status' => 'lunas',
            'verified_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->student->id,
            'title' => 'Pembayaran Denda Diverifikasi',
        ]);
    }

    public function test_admin_can_reject_fine_payment()
    {
        $this->fine->update([
            'status' => 'menunggu_verifikasi',
            'payment_proof_photo' => 'payment_proofs/fake.jpg',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/fines/{$this->fine->id}/verify", [
            'action' => 'reject',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('fines', [
            'id' => $this->fine->id,
            'status' => 'belum_dibayar',
            'payment_proof_photo' => null,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->student->id,
            'title' => 'Bukti Pembayaran Ditolak',
        ]);
    }

    public function test_admin_can_update_settings()
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'max_loan_duration' => 12,
            'fine_per_hour' => 10000,
            'max_items_borrowed' => 5,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'max_loan_duration', 'value' => '12']);
        $this->assertDatabaseHas('settings', ['key' => 'fine_per_hour', 'value' => '10000']);
        $this->assertDatabaseHas('settings', ['key' => 'max_items_borrowed', 'value' => '5']);
    }
}

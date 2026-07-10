<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoanWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;
    private Category $category;
    private Item $item;
    private ItemUnit $unit;

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

        $this->category = Category::create([
            'name' => 'Elektronika',
            'description' => 'Kategori elektronika',
        ]);

        $this->item = Item::create([
            'name' => 'Oscilloscope',
            'category_id' => $this->category->id,
        ]);

        $this->unit = ItemUnit::create([
            'item_id' => $this->item->id,
            'serial_number' => 'OSC-001',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        Setting::updateOrCreate(['key' => 'max_loan_duration'], ['value' => '8']);
        Setting::updateOrCreate(['key' => 'max_loan_duration_type'], ['value' => 'hours']);
        Setting::updateOrCreate(['key' => 'fine_amount'], ['value' => '5000']);
        Setting::updateOrCreate(['key' => 'fine_type'], ['value' => 'per_hour']);
        Setting::updateOrCreate(['key' => 'max_items_borrowed'], ['value' => '3']);
    }

    public function test_admin_can_approve_loan()
    {
        $loan = Loan::create([
            'user_id' => $this->student->id,
            'status' => 'menunggu_persetujuan',
            'loan_duration' => 8,
            'loan_duration_type' => 'hours',
        ]);

        $loanItem = LoanItem::create([
            'loan_id' => $loan->id,
            'item_unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/approve");
        $response->assertRedirect();
        
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'aktif',
            'approved_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('item_units', [
            'id' => $this->unit->id,
            'status' => 'dipinjam',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->student->id,
            'title' => 'Peminjaman Disetujui',
        ]);
    }

    public function test_admin_can_reject_loan()
    {
        $loan = Loan::create([
            'user_id' => $this->student->id,
            'status' => 'menunggu_persetujuan',
            'loan_duration' => 8,
            'loan_duration_type' => 'hours',
        ]);

        $loanItem = LoanItem::create([
            'loan_id' => $loan->id,
            'item_unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/reject", [
            'rejection_reason' => 'Stok tidak cukup',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'ditolak',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->student->id,
            'title' => 'Peminjaman Ditolak',
        ]);
    }

    public function test_student_can_submit_return()
    {
        Storage::fake('public');
        $fakeProof = UploadedFile::fake()->create('proof.jpg', 100);

        $loan = Loan::create([
            'user_id' => $this->student->id,
            'status' => 'aktif',
            'loan_duration' => 8,
            'loan_duration_type' => 'hours',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $loanItem = LoanItem::create([
            'loan_id' => $loan->id,
            'item_unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->student)->post("/loans/{$loan->id}/return", [
            'return_photos' => [
                $loanItem->id => $fakeProof
            ]
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'menunggu_verifikasi_kembali',
        ]);

        $loanItem->refresh();
        $this->assertNotNull($loanItem->return_proof_photo);
    }

    public function test_admin_can_verify_return_and_calculate_lateness_fines()
    {
        // Loan approved 10 hours ago, but limit was 8 hours. Overdue by 2 hours.
        $approvedAt = now()->subHours(10)->addMinutes(1);

        $loan = Loan::create([
            'user_id' => $this->student->id,
            'status' => 'menunggu_verifikasi_kembali',
            'loan_duration' => 8,
            'loan_duration_type' => 'hours',
            'approved_by' => $this->admin->id,
            'approved_at' => $approvedAt,
        ]);

        $loanItem = LoanItem::create([
            'loan_id' => $loan->id,
            'item_unit_id' => $this->unit->id,
            'return_proof_photo' => 'return_proofs/fake.jpg',
        ]);

        // Mock unit is currently dipinjam
        $this->unit->update(['status' => 'dipinjam']);

        $response = $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/verify-return", [
            'unit_conditions' => [
                $loanItem->id => 'baik', // returned in good condition
            ]
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'selesai',
        ]);

        // 2 hours overdue * 5000 fine per hour = 10000 denda
        $this->assertDatabaseHas('fines', [
            'loan_id' => $loan->id,
            'amount' => 10000,
            'type' => 'keterlambatan',
            'status' => 'belum_dibayar',
        ]);

        $this->assertDatabaseHas('item_units', [
            'id' => $this->unit->id,
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);
    }

    public function test_admin_can_verify_return_and_calculate_damage_fines()
    {
        $loan = Loan::create([
            'user_id' => $this->student->id,
            'status' => 'menunggu_verifikasi_kembali',
            'loan_duration' => 8,
            'loan_duration_type' => 'hours',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $loanItem = LoanItem::create([
            'loan_id' => $loan->id,
            'item_unit_id' => $this->unit->id,
            'return_proof_photo' => 'return_proofs/fake.jpg',
        ]);

        $this->unit->update(['status' => 'dipinjam']);

        $response = $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/verify-return", [
            'unit_conditions' => [
                $loanItem->id => 'rusak', // returned damaged
            ]
        ]);

        $response->assertRedirect();

        // Should apply fixed damage fine of 50000
        $this->assertDatabaseHas('fines', [
            'loan_id' => $loan->id,
            'amount' => 50000,
            'type' => 'kerusakan_barang',
            'status' => 'belum_dibayar',
        ]);

        $this->assertDatabaseHas('item_units', [
            'id' => $this->unit->id,
            'status' => 'maintenance',
            'condition' => 'rusak',
        ]);
    }

    public function test_admin_can_view_loan_report_page()
    {
        $loan = Loan::create([
            'user_id' => $this->student->id,
            'status' => 'selesai',
            'loan_duration' => 8,
            'loan_duration_type' => 'hours',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/loans/report');
        $response->assertStatus(200);
        $response->assertSee('Laporan Aktivitas Peminjaman Alat');
    }
}

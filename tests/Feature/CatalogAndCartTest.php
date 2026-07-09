<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAndCartTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Category $category;
    private Item $item;
    private ItemUnit $unit1;
    private ItemUnit $unit2;
    private ItemUnit $unit3;
    private ItemUnit $unit4;

    protected function setUp(): void
    {
        parent::setUp();

        // Create student user
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

        $this->unit1 = ItemUnit::create(['item_id' => $this->item->id, 'serial_number' => 'OSC-001', 'condition' => 'baik', 'status' => 'tersedia']);
        $this->unit2 = ItemUnit::create(['item_id' => $this->item->id, 'serial_number' => 'OSC-002', 'condition' => 'baik', 'status' => 'tersedia']);
        $this->unit3 = ItemUnit::create(['item_id' => $this->item->id, 'serial_number' => 'OSC-003', 'condition' => 'baik', 'status' => 'tersedia']);
        $this->unit4 = ItemUnit::create(['item_id' => $this->item->id, 'serial_number' => 'OSC-004', 'condition' => 'baik', 'status' => 'tersedia']);

        // Default setting
        Setting::create(['key' => 'max_loan_duration', 'value' => '8']);
        Setting::create(['key' => 'fine_per_hour', 'value' => '5000']);
        Setting::create(['key' => 'max_items_borrowed', 'value' => '3']);
    }

    public function test_student_can_browse_catalog()
    {
        $response = $this->actingAs($this->student)->get('/catalog');
        $response->assertStatus(200);
        $response->assertSee('Oscilloscope');
    }

    public function test_student_can_add_and_remove_from_cart()
    {
        // Add to cart
        $response = $this->actingAs($this->student)->post('/catalog/add-to-cart', [
            'item_unit_id' => $this->unit1->id,
        ]);
        $response->assertRedirect();
        $this->assertEquals([$this->unit1->id], session('cart'));

        // View cart
        $response = $this->actingAs($this->student)->get('/cart');
        $response->assertStatus(200);
        $response->assertSee('OSC-001');

        // Remove from cart
        $response = $this->actingAs($this->student)->post('/catalog/remove-from-cart', [
            'item_unit_id' => $this->unit1->id,
        ]);
        $response->assertRedirect();
        $this->assertEquals([], session('cart'));
    }

    public function test_student_cannot_exceed_max_items_limit()
    {
        // Max is 3
        $this->actingAs($this->student)->post('/catalog/add-to-cart', ['item_unit_id' => $this->unit1->id]);
        $this->actingAs($this->student)->post('/catalog/add-to-cart', ['item_unit_id' => $this->unit2->id]);
        $this->actingAs($this->student)->post('/catalog/add-to-cart', ['item_unit_id' => $this->unit3->id]);
        
        $response = $this->actingAs($this->student)->post('/catalog/add-to-cart', ['item_unit_id' => $this->unit4->id]);
        $response->assertSessionHas('error');
        $this->assertCount(3, session('cart'));
    }

    public function test_student_can_checkout_cart()
    {
        $this->actingAs($this->student)->post('/catalog/add-to-cart', ['item_unit_id' => $this->unit1->id]);
        $this->actingAs($this->student)->post('/catalog/add-to-cart', ['item_unit_id' => $this->unit2->id]);

        $response = $this->actingAs($this->student)->post('/cart/checkout', [
            'loan_duration_hours' => 6,
        ]);

        $response->assertRedirect('/loans');
        $this->assertDatabaseHas('loans', [
            'user_id' => $this->student->id,
            'status' => 'menunggu_persetujuan',
            'loan_duration_hours' => 6,
        ]);
        $this->assertNull(session('cart'));
    }
}

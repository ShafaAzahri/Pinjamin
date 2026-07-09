<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@pinjamin.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $this->category = Category::create([
            'name' => 'Alat Elektronik',
            'description' => 'Kategori alat-alat elektronik',
        ]);
    }

    public function test_admin_can_access_inventory_index()
    {
        $response = $this->actingAs($this->admin)->get('/admin/inventory');
        $response->assertStatus(200);
        $response->assertSee('Inventaris Barang');
    }

    public function test_admin_can_create_item_with_units()
    {
        Storage::fake('public');
        $fakeImage = UploadedFile::fake()->create('item.jpg', 100);

        $response = $this->actingAs($this->admin)->post('/admin/inventory', [
            'name' => 'Oscilloscope X',
            'category_id' => $this->category->id,
            'description' => 'Oscilloscope super canggih',
            'image' => $fakeImage,
            'units' => [
                ['serial_number' => 'OSC-100', 'condition' => 'baik'],
                ['serial_number' => 'OSC-200', 'condition' => 'baik'],
            ]
        ]);

        $response->assertRedirect('/admin/inventory');
        $this->assertDatabaseHas('items', ['name' => 'Oscilloscope X']);
        $this->assertDatabaseHas('item_units', ['serial_number' => 'OSC-100', 'condition' => 'baik']);
        $this->assertDatabaseHas('item_units', ['serial_number' => 'OSC-200', 'condition' => 'baik']);
    }

    public function test_admin_can_update_item()
    {
        $item = Item::create([
            'name' => 'Multimeter Y',
            'category_id' => $this->category->id,
            'description' => 'Multimeter digital standar',
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/inventory/{$item->id}", [
            'name' => 'Multimeter Y Updated',
            'category_id' => $this->category->id,
            'description' => 'Updated description',
        ]);

        $response->assertRedirect("/admin/inventory/{$item->id}");
        $this->assertDatabaseHas('items', ['name' => 'Multimeter Y Updated', 'description' => 'Updated description']);
    }

    public function test_admin_can_add_unit_to_existing_item()
    {
        $item = Item::create([
            'name' => 'Function Generator Z',
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/inventory/{$item->id}/units", [
            'serial_number' => 'GEN-999',
            'condition' => 'baik',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('item_units', [
            'item_id' => $item->id,
            'serial_number' => 'GEN-999',
            'condition' => 'baik',
            'status' => 'tersedia'
        ]);
    }

    public function test_admin_can_update_unit_condition_and_status()
    {
        $item = Item::create([
            'name' => 'Function Generator Z',
            'category_id' => $this->category->id,
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'GEN-111',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/units/{$unit->id}", [
            'condition' => 'rusak',
            'status' => 'maintenance',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('item_units', [
            'id' => $unit->id,
            'condition' => 'rusak',
            'status' => 'maintenance',
        ]);
    }

    public function test_admin_can_delete_unit()
    {
        $item = Item::create([
            'name' => 'Tool A',
            'category_id' => $this->category->id,
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'TOOL-123',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/units/{$unit->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('item_units', ['id' => $unit->id]);
    }

    public function test_admin_cannot_delete_borrowed_unit()
    {
        $item = Item::create([
            'name' => 'Tool A',
            'category_id' => $this->category->id,
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'TOOL-555',
            'condition' => 'baik',
            'status' => 'dipinjam',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/units/{$unit->id}");
        $response->assertRedirect();
        $this->assertDatabaseHas('item_units', ['id' => $unit->id]);
    }
}

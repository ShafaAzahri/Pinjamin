<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Fine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_relationships_are_correct()
    {
        $user = User::create(['name' => 'Shafa', 'email' => 'shafa@student.polines.ac.id', 'password' => bcrypt('password'), 'role' => 'user']);
        $category = Category::create(['name' => 'Elektronika']);
        $item = Item::create(['category_id' => $category->id, 'name' => 'Oscilloscope Digital']);
        $unit = ItemUnit::create(['item_id' => $item->id, 'serial_number' => 'OSC-001']);
        $loan = Loan::create(['user_id' => $user->id, 'status' => 'menunggu_persetujuan']);
        $loanItem = LoanItem::create(['loan_id' => $loan->id, 'item_unit_id' => $unit->id]);
        $fine = Fine::create(['loan_id' => $loan->id, 'amount' => 5000, 'type' => 'keterlambatan']);

        $this->assertEquals('Elektronika', $item->category->name);
        $this->assertCount(1, $item->units);
        $this->assertEquals($user->id, $loan->user->id);
        $this->assertCount(1, $loan->loanItems);
        $this->assertEquals('OSC-001', $loan->loanItems->first()->unit->serial_number);
        $this->assertCount(1, $loan->fines);
    }
}

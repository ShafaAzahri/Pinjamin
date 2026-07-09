<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tables_are_created()
    {
        $tables = ['users', 'categories', 'items', 'item_units', 'loans', 'loan_items', 'fines', 'notifications', 'settings'];
        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist.");
        }
    }
}

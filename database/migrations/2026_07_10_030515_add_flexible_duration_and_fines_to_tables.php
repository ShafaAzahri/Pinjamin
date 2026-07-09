<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->renameColumn('loan_duration_hours', 'loan_duration');
            $table->enum('loan_duration_type', ['hours', 'days'])->default('hours')->after('loan_duration');
        });

        // Update settings table records
        DB::table('settings')->where('key', 'fine_per_hour')->update(['key' => 'fine_amount']);
        
        // Insert new setting if not exists
        if (!DB::table('settings')->where('key', 'fine_type')->exists()) {
            DB::table('settings')->insert([
                'key' => 'fine_type',
                'value' => 'per_hour',
                'description' => 'Tipe perhitungan denda (per_hour atau per_day)'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert settings table records
        DB::table('settings')->where('key', 'fine_type')->delete();
        DB::table('settings')->where('key', 'fine_amount')->update(['key' => 'fine_per_hour']);

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('loan_duration_type');
            $table->renameColumn('loan_duration', 'loan_duration_hours');
        });
    }
};

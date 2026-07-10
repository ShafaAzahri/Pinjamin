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
        // Ubah enum status untuk menambahkan 'ditolak'
        // Kita gunakan raw DB statement karena Laravel Schema Builder tidak bisa mengubah enum dengan mudah di beberapa database
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('menunggu_verifikasi', 'aktif', 'ditangguhkan', 'ditolak') DEFAULT 'menunggu_verifikasi'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('rejection_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('menunggu_verifikasi', 'aktif', 'ditangguhkan') DEFAULT 'menunggu_verifikasi'");
        }
    }
};

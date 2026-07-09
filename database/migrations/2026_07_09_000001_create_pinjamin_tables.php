<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->string('nim', 50)->unique()->nullable();
            $table->string('prodi', 100)->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('ktm_photo')->nullable();
            $table->enum('status', ['menunggu_verifikasi', 'aktif', 'ditangguhkan'])->default('menunggu_verifikasi');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('item_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('serial_number', 100)->unique();
            $table->enum('condition', ['baik', 'rusak'])->default('baik');
            $table->enum('status', ['tersedia', 'dipinjam', 'maintenance'])->default('tersedia');
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['menunggu_persetujuan', 'aktif', 'menunggu_verifikasi_kembali', 'selesai', 'ditolak', 'terlambat'])->default('menunggu_persetujuan');
            $table->integer('loan_duration_hours')->default(8);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignId('item_unit_id')->constrained('item_units')->cascadeOnDelete();
            $table->string('return_proof_photo')->nullable();
            $table->enum('return_condition', ['baik', 'rusak'])->nullable();
            $table->timestamps();
        });

        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['keterlambatan', 'kerusakan_barang']);
            $table->enum('status', ['belum_dibayar', 'menunggu_verifikasi', 'lunas'])->default('belum_dibayar');
            $table->string('payment_proof_photo')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('fines');
        Schema::dropIfExists('loan_items');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('item_units');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
    }
};

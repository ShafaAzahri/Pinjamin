<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Default Admin Account
        User::create([
            'name' => 'Admin Lab A',
            'email' => 'admin@pinjamin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // 2. Default Student Account
        User::create([
            'name' => 'Shafa',
            'email' => 'shafa@student.polines.ac.id',
            'password' => Hash::make('password'),
            'role' => 'user',
            'nim' => '3.32.22.0.12',
            'prodi' => 'Teknik Informatika',
            'status' => 'aktif',
        ]);

        // 3. Categories
        $catElectronics = Category::create(['name' => 'Elektronika', 'description' => 'Alat-alat ukur dan komponen listrik/elektronika']);
        $catMicro = Category::create(['name' => 'Microcontroller', 'description' => 'Development board dan modul mikroprosesor']);
        $catTools = Category::create(['name' => 'Tools', 'description' => 'Alat perkakas tangan untuk perakitan elektro']);

        // 4. Default Settings
        Setting::create([
            'key' => 'max_loan_duration',
            'value' => '8',
            'description' => 'Batas waktu peminjaman maksimal (dalam satuan jam)',
        ]);
        Setting::create([
            'key' => 'fine_per_hour',
            'value' => '5000',
            'description' => 'Tarif denda keterlambatan per jam (dalam Rupiah)',
        ]);
        Setting::create([
            'key' => 'max_items_borrowed',
            'value' => '3',
            'description' => 'Batas jumlah barang yang dapat dipinjam sekaligus',
        ]);

        // 5. Items and Units
        // Oscilloscope Digital
        $itemOsc = Item::create([
            'category_id' => $catElectronics->id,
            'name' => 'Oscilloscope Digital',
            'description' => 'Alat ukur grafik sinyal listrik/elektronika',
        ]);
        ItemUnit::create(['item_id' => $itemOsc->id, 'serial_number' => 'OSC-001', 'condition' => 'baik', 'status' => 'tersedia']);
        ItemUnit::create(['item_id' => $itemOsc->id, 'serial_number' => 'OSC-002', 'condition' => 'baik', 'status' => 'tersedia']);
        ItemUnit::create(['item_id' => $itemOsc->id, 'serial_number' => 'OSC-003', 'condition' => 'baik', 'status' => 'tersedia']);
        ItemUnit::create(['item_id' => $itemOsc->id, 'serial_number' => 'OSC-004', 'condition' => 'baik', 'status' => 'dipinjam']); // As in screenshot
        ItemUnit::create(['item_id' => $itemOsc->id, 'serial_number' => 'OSC-005', 'condition' => 'rusak', 'status' => 'maintenance']);

        // Multimeter Digital
        $itemMult = Item::create([
            'category_id' => $catElectronics->id,
            'name' => 'Multimeter Digital',
            'description' => 'Alat ukur tegangan, arus, dan resistansi',
        ]);
        for ($i = 1; $i <= 10; $i++) {
            $num = str_pad($i, 3, '0', STR_PAD_LEFT);
            $status = ($i == 9) ? 'dipinjam' : 'tersedia';
            ItemUnit::create(['item_id' => $itemMult->id, 'serial_number' => "MULT-{$num}", 'condition' => 'baik', 'status' => $status]);
        }

        // Function Generator
        $itemFunc = Item::create([
            'category_id' => $catElectronics->id,
            'name' => 'Function Generator',
            'description' => 'Alat pembangkit gelombang listrik',
        ]);
        ItemUnit::create(['item_id' => $itemFunc->id, 'serial_number' => 'FUNC-001', 'condition' => 'baik', 'status' => 'tersedia']);
        ItemUnit::create(['item_id' => $itemFunc->id, 'serial_number' => 'FUNC-002', 'condition' => 'baik', 'status' => 'tersedia']);
        ItemUnit::create(['item_id' => $itemFunc->id, 'serial_number' => 'FUNC-003', 'condition' => 'baik', 'status' => 'dipinjam']);

        // Arduino Uno
        $itemArdu = Item::create([
            'category_id' => $catMicro->id,
            'name' => 'Arduino Uno',
            'description' => 'Mikrokontroler berbasis ATmega328P',
        ]);
        for ($i = 1; $i <= 15; $i++) {
            $num = str_pad($i, 3, '0', STR_PAD_LEFT);
            ItemUnit::create(['item_id' => $itemArdu->id, 'serial_number' => "ARDU-{$num}", 'condition' => 'baik', 'status' => 'tersedia']);
        }
    }
}

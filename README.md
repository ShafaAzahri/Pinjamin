# 🚀 Pinjamin - Sistem Peminjaman Alat Lab

<p align="center">
  <img src="public/images/pinjamin-logo.png" alt="Pinjamin Logo" width="200">
</p>

**Pinjamin** adalah sebuah platform aplikasi web modern yang dibangun untuk memudahkan mahasiswa dan admin dalam mengelola proses peminjaman peralatan praktikum di Laboratorium (khususnya untuk Politeknik Negeri Semarang / Polines). 

Aplikasi ini dibangun menggunakan **Laravel 11**, dilengkapi dengan desain UI modern (Tailwind CSS + Alpine.js), dan memiliki pengalaman navigasi secepat kilat (*Single Page Application*) berkat integrasi **Hotwire Turbo**.

---

## ✨ Fitur Unggulan

### 👨‍🎓 Untuk Mahasiswa:
1. **Google Single Sign-On (SSO):** Login instan tanpa ribet mengingat password menggunakan akun Google (dikhususkan untuk domain `@mhs.polines.ac.id`).
2. **Katalog Interaktif:** Jelajahi barang, lihat stok *real-time*, dan tambahkan barang ke Keranjang (Cart) layaknya *e-commerce*.
3. **Navigasi Turbo (SPA):** Perpindahan antar halaman terjadi secara *seamless* tanpa *loading* ulang seluruh halaman (layar putih).
4. **Pembayaran Denda Otomatis (Midtrans):** Jika terlambat mengembalikan atau barang rusak, denda bisa dibayar langsung menggunakan QRIS/Gopay/Transfer Bank melalui integrasi *Payment Gateway* Midtrans.
5. **Notifikasi & Status:** Pantau status persetujuan peminjaman, barang aktif, hingga notifikasi pengembalian.

### 👨‍💻 Untuk Admin:
1. **Dashboard Statistik:** Ringkasan jumlah peminjaman aktif, keterlambatan, dan daftar verifikasi mahasiswa baru (KTM).
2. **Manajemen Inventaris:** Sistem pendataan barang dan unit (*serial number*) yang mendetail. Status barang otomatis berubah saat dipinjam.
3. **Persetujuan (Approval):** Proses setujui (Approve), tolak (Reject), dan verifikasi pengembalian barang dalam 1 kali klik.
4. **Sistem Denda Cerdas:** Hitung otomatis durasi keterlambatan berdasarkan jam/hari dan buat tagihan denda otomatis ke mahasiswa.
5. **Cetak Laporan (PDF):** Cetak laporan riwayat peminjaman dengan filter status yang rapi.
6. **Pengaturan Sistem:** Atur nilai denda, maksimal hari pinjam, dan maksimal jumlah barang secara dinamis.

---

## 📸 Tampilan Layar (Screenshots)

| Halaman Login & SSO | Dashboard Admin |
| :---: | :---: |
| <img src="public/docs/login.png" width="400" alt="Login"> | <img src="public/docs/admin-dashboard.png" width="400" alt="Admin Dashboard"> |

| Katalog Mahasiswa | Keranjang Peminjaman |
| :---: | :---: |
| <img src="public/docs/catalog.png" width="400" alt="Katalog"> | <img src="public/docs/cart.png" width="400" alt="Cart"> |

| Integrasi Pembayaran Midtrans | Manajemen Peminjaman (Admin) |
| :---: | :---: |
| <img src="public/docs/midtrans.png" width="400" alt="Midtrans"> | <img src="public/docs/admin-loans.png" width="400" alt="Admin Loans"> |

---

## 💻 Panduan Instalasi (Development)

Berikut adalah panduan untuk menjalankan Pinjamin di komputer lokal Anda (menggunakan **Laragon** atau XAMPP).

### 1. Persyaratan Sistem
- PHP >= 8.3
- Composer
- Node.js & NPM (untuk Tailwind & Vite)
- Database MySQL atau SQLite

### 2. Kloning Repositori
Buka terminal dan jalankan:
```bash
git clone https://github.com/ShafaAzahri/Pinjamin.git
cd Pinjamin
```

### 3. Instalasi Dependensi (Backend & Frontend)
```bash
composer install
npm install
```

### 4. Pengaturan `.env`
Salin file konfigurasi:
```bash
cp .env.example .env
```
Lalu *generate* kunci aplikasi:
```bash
php artisan key:generate
```

Ubah pengaturan database dan URL Anda di dalam file `.env`:
```env
APP_URL=http://pinjamin.test

# Jika menggunakan database MySQL:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pinjamin
DB_USERNAME=root
DB_PASSWORD=

# Atau jika ingin praktis pakai SQLite:
DB_CONNECTION=sqlite
```

### 5. Konfigurasi Google SSO (Wajib untuk Login)
Agar fitur *Login with Google* berfungsi, tambahkan API kredensial dari **Google Cloud Console** Anda ke dalam `.env`:
```env
GOOGLE_CLIENT_ID=masukkan_client_id_anda
GOOGLE_CLIENT_SECRET=masukkan_secret_anda
GOOGLE_REDIRECT_URI=http://pinjamin.test/auth/google/callback
```

### 6. Migrasi & Seeder Database
Siapkan tabel dan masukkan akun bawaan (Admin & User):
```bash
php artisan migrate:fresh --seed
```
*Catatan: Ini akan membuat akun Admin (admin@pinjamin.com / password) dan Student.*

### 7. Jalankan Server Vite (Untuk CSS & JS)
Buka tab terminal baru dan jalankan:
```bash
npm run dev
```

Selesai! Sekarang Anda dapat mengakses aplikasinya melalui web browser pada alamat:
`http://pinjamin.test/`

---

## 🛠 Teknologi yang Digunakan
- **Framework Utama:** Laravel 11
- **UI & Styling:** Tailwind CSS 3
- **Interaktivitas:** Alpine.js
- **SPA Navigation:** Hotwire Turbo 8
- **PDF Generator:** Barryvdh/DomPDF
- **Payment Gateway:** Midtrans Snap
- **Authentication:** Laravel Sanctum (Core Auth) & Laravel Socialite (Google SSO)

---
*Dibuat untuk Tugas / Skripsi Politeknik Negeri Semarang.*

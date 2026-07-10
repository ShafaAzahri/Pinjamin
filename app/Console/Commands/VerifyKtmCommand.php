<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\OcrService;

class VerifyKtmCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pinjamin:verify-ktm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifikasi massal KTM pengguna yang masih berstatus menunggu_verifikasi menggunakan AI 9Router';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mencari pengguna yang menunggu verifikasi KTM...');

        $users = User::where('status', 'menunggu_verifikasi')
            ->whereNotNull('ktm_photo')
            ->get();

        if ($users->isEmpty()) {
            $this->info('Tidak ada pengguna yang perlu diverifikasi.');
            return;
        }

        $this->info("Ditemukan {$users->count()} pengguna. Memulai verifikasi AI...");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $successCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            $result = OcrService::verifyKtm($user->ktm_photo, $user->nim, $user->name);

            if ($result['is_match'] && $result['is_valid_ktm']) {
                $user->update([
                    'status' => 'aktif',
                    'rejection_reason' => null
                ]);
                
                $user->notifications()->create([
                    'title' => 'Verifikasi KTM Berhasil',
                    'message' => 'Selamat, Kartu Tanda Mahasiswa (KTM) Anda berhasil diverifikasi secara otomatis oleh AI. Anda sekarang dapat melakukan peminjaman alat.'
                ]);

                $successCount++;
            } else {
                $reason = $result['reason'] ?? 'Data tidak sesuai atau bukan foto KTM.';

                if (str_contains($reason, 'Gagal memproses respon') || str_contains($reason, 'Terjadi kesalahan')) {
                    // Masalah teknis/timeout: pertahankan status menunggu_verifikasi
                    $user->update([
                        'status' => 'menunggu_verifikasi'
                    ]);
                    $this->warn("\n⚠️ Sistem Error/Timeout untuk {$user->name} (NIM: {$user->nim}). Alasan: {$reason}");
                } else {
                    // Penolakan valid oleh AI
                    $user->update([
                        'status' => 'ditolak',
                        'rejection_reason' => $reason
                    ]);

                    $user->notifications()->create([
                        'title' => 'Verifikasi KTM Ditolak',
                        'message' => "Maaf, verifikasi KTM Anda ditolak secara otomatis oleh AI. Alasan: {$reason}. Silakan unggah kembali KTM Anda dengan benar."
                    ]);

                    if ($user->phone) {
                        $message = "Halo *{$user->name}*,\n\n"
                                 . "Mohon maaf, pendaftaran akun Pinjamin Anda *DITOLAK* secara otomatis oleh AI.\n\n"
                                 . "Alasan: _{$reason}_\n\n"
                                 . "Silakan unggah ulang KTM Anda melalui halaman profil dengan benar. Terima kasih!";
                        \App\Services\WhatsAppService::send($user->phone, $message);
                    }

                    $this->error("\n❌ Gagal verifikasi untuk {$user->name} (NIM: {$user->nim}). Alasan: {$reason}");
                }
                $failedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\n✅ Proses selesai!");
        $this->info("Berhasil mengaktifkan: {$successCount} pengguna");
        $this->info("Gagal/Buram (Tetap Pending): {$failedCount} pengguna");
    }
}

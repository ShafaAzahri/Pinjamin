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
                $user->update(['status' => 'aktif']);
                $successCount++;
            } else {
                // Biarkan status tetap menunggu_verifikasi
                $failedCount++;
                $this->error("\n❌ Gagal verifikasi untuk {$user->name} (NIM: {$user->nim}). Alasan: {$result['reason']}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\n✅ Proses selesai!");
        $this->info("Berhasil mengaktifkan: {$successCount} pengguna");
        $this->info("Gagal/Buram (Tetap Pending): {$failedCount} pengguna");
    }
}

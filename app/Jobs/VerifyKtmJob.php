<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\OcrService;

class VerifyKtmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $photoPath;
    public $nim;
    public $name;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $photoPath, $nim = null, $name = null)
    {
        $this->user = $user;
        $this->photoPath = $photoPath;
        $this->nim = $nim ?? $user->nim;
        $this->name = $name ?? $user->name;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Jalankan verifikasi AI OCR secara asinkron di background
        $result = OcrService::verifyKtm($this->photoPath, $this->nim, $this->name);

        if ($result['is_match'] && $result['is_valid_ktm']) {
            $this->user->update([
                'status' => 'aktif',
                'rejection_reason' => null
            ]);

            // Tambahkan Notifikasi Database
            $this->user->notifications()->create([
                'title' => 'Verifikasi KTM Berhasil',
                'message' => 'Selamat, Kartu Tanda Mahasiswa (KTM) Anda berhasil diverifikasi secara otomatis oleh AI. Anda sekarang dapat melakukan peminjaman alat.'
            ]);
        } else {
            // Jika gagal verifikasi AI, biarkan masuk antrean manual admin (status: menunggu_verifikasi)
            $this->user->update([
                'status' => 'menunggu_verifikasi'
            ]);
        }
    }
}

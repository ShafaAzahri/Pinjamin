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
        \Illuminate\Support\Facades\Log::info("VerifyKtmJob: Memulai verifikasi AI untuk pengguna: {$this->user->name} (ID: {$this->user->id})");
        
        // Jika file KTM di database sudah berubah (user sudah upload yang baru), batalkan job ini
        if ($this->user->ktm_photo !== $this->photoPath) {
            \Illuminate\Support\Facades\Log::info("VerifyKtmJob: Berkas KTM sudah diperbarui oleh pengguna (Database: {$this->user->ktm_photo}, Job: {$this->photoPath}). Membatalkan job lama ini.");
            return;
        }

        // Jalankan verifikasi AI OCR secara asinkron di background
        $result = OcrService::verifyKtm($this->photoPath, $this->nim, $this->name);

        \Illuminate\Support\Facades\Log::info("VerifyKtmJob: Hasil analisis OCR. Match: " . ($result['is_match'] ? 'YA' : 'TIDAK') . ", Valid KTM: " . ($result['is_valid_ktm'] ? 'YA' : 'TIDAK') . ", Alasan: " . $result['reason']);

        if ($result['is_match'] && $result['is_valid_ktm']) {
            \Illuminate\Support\Facades\Log::info("VerifyKtmJob: Pengguna disetujui otomatis oleh AI. Mengubah status ke aktif.");
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
            $reason = $result['reason'] ?? 'Data tidak sesuai atau bukan foto KTM.';

            // Jika error sistem atau timeout, pertahankan status menunggu_verifikasi untuk manual admin
            if (str_contains($reason, 'Gagal memproses respon') || str_contains($reason, 'Terjadi kesalahan')) {
                \Illuminate\Support\Facades\Log::warning("VerifyKtmJob: Terjadi error teknis/timeout pada AI. Mempertahankan status menunggu_verifikasi untuk pemeriksaan manual.");
                $this->user->update([
                    'status' => 'menunggu_verifikasi'
                ]);
            } else {
                \Illuminate\Support\Facades\Log::info("VerifyKtmJob: Pengguna DITOLAK otomatis oleh AI. Mengubah status ke ditolak. Alasan: {$reason}");
                // Tolak langsung oleh AI karena gambar memang bukan KTM atau salah data
                $this->user->update([
                    'status' => 'ditolak',
                    'rejection_reason' => $reason
                ]);

                // Tambahkan Notifikasi Database
                $this->user->notifications()->create([
                    'title' => 'Verifikasi KTM Ditolak',
                    'message' => "Maaf, verifikasi KTM Anda ditolak secara otomatis oleh AI. Alasan: {$reason}. Silakan unggah kembali KTM Anda dengan benar."
                ]);

                // Kirim WhatsApp (jika ada nomor HP)
                if ($this->user->phone) {
                    \Illuminate\Support\Facades\Log::info("VerifyKtmJob: Mengirim notifikasi penolakan via WhatsApp ke {$this->user->phone}");
                    $message = "Halo *{$this->user->name}*,\n\n"
                             . "Mohon maaf, pendaftaran akun Pinjamin Anda *DITOLAK* secara otomatis oleh AI.\n\n"
                             . "Alasan: _{$reason}_\n\n"
                             . "Silakan unggah ulang KTM Anda melalui halaman profil dengan benar. Terima kasih!";
                    \App\Services\WhatsAppService::send($this->user->phone, $message);
                }
            }
        }
    }
}

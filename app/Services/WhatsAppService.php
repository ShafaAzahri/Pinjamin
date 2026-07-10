<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Kirim pesan WhatsApp
     *
     * @param string $target Nomor HP/WhatsApp tujuan (e.g. 0812xxx atau 62812xxx)
     * @param string $message Isi pesan yang akan dikirim
     * @return bool True jika berhasil dikirim (atau ter-log dengan sukses)
     */
    public static function send(string $target, string $message): bool
    {
        $url = env('WHATSAPP_SERVER_URL', 'http://pinjamin.test/send');

        // Format nomor agar diawali kode negara 62
        $formattedTarget = self::formatNumber($target);

        // Jika URL diset 'null' atau kosong, simulasikan pengiriman (untuk testing)
        if (empty($url) || $url === 'null') {
            Log::info("WhatsApp Notification (Simulated LOG):\nTo: {$formattedTarget}\nMessage: {$message}");
            return true;
        }

        try {
            $response = Http::withoutVerifying()->post($url, [
                'target' => $formattedTarget,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp Notification sent successfully to {$formattedTarget} via Local Gateway");
                return true;
            }

            Log::error("Failed to send WhatsApp to {$formattedTarget}. Gateway Response: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Exception occurred while sending WhatsApp to {$formattedTarget}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format nomor telepon ke standar internasional (62xxx)
     */
    private static function formatNumber(string $number): string
    {
        // Hilangkan karakter non-numerik
        $clean = preg_replace('/[^0-9]/', '', $number);

        // Jika diawali '0', ubah jadi '62'
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }
}

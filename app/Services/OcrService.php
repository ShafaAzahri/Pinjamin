<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OcrService
{
    /**
     * Verify KTM photo against expected NIM and Name using local AI proxy.
     *
     * @param string $imagePath Storage path (e.g., 'ktm/filename.jpg')
     * @param string $expectedNim
     * @param string $expectedName
     * @return array ['is_match' => bool, 'is_valid_ktm' => bool, 'reason' => string]
     */
    public static function verifyKtm(string $imagePath, string $expectedNim, string $expectedName): array
    {
        try {
            // Read image and encode to base64
            $fullPath = storage_path('app/public/' . $imagePath);
            if (!file_exists($fullPath)) {
                return self::defaultFalse('File foto KTM tidak ditemukan.');
            }

            $imageData = base64_encode(file_get_contents($fullPath));
            $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';

            $base64Image = "data:{$mimeType};base64,{$imageData}";

            $prompt = "Tolong analisis foto Kartu Tanda Mahasiswa (KTM) ini. Ekstrak NIM dan Nama yang tertera di kartu. "
                . "Bandingkan dengan data berikut:\n"
                . "- Expected NIM: {$expectedNim}\n"
                . "- Expected Name: {$expectedName}\n\n"
                . "Perhatikan bahwa nama mungkin disingkat di kartu. "
                . "Apakah ini foto KTM yang valid (bukan foto acak)? "
                . "Apakah NIM dan Nama di foto sesuai dengan data di atas? "
                . "Kembalikan respon hanya dalam format JSON murni (tanpa markdown atau teks tambahan) dengan struktur berikut: "
                . '{"is_valid_ktm": true/false, "is_match": true/false, "reason": "penjelasan singkat"}';

            // Endpoint 9Router Lokal (berdasarkan screenshot)
            $endpoint = 'http://localhost:20128/v1/chat/completions';

            // Kita menggunakan model Gemini yang tersedia di 9Router
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('AI_API_KEY', 'dummy-key')
            ])->post($endpoint, [
                        'model' => 'ag/gemini-3-flash', // Model Gemini dari 9Router
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => $prompt
                                    ],
                                    [
                                        'type' => 'image_url',
                                        'image_url' => [
                                            'url' => $base64Image
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'temperature' => 0.1, // Agar AI tidak berhalusinasi
                        'max_tokens' => 300,
                    ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                // Bersihkan respon jika AI masih mengembalikan markdown block ```json ... ```
                $content = preg_replace('/```json/i', '', $content);
                $content = preg_replace('/```/i', '', $content);
                $content = trim($content);

                $result = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'is_valid_ktm' => (bool) ($result['is_valid_ktm'] ?? false),
                        'is_match' => (bool) ($result['is_match'] ?? false),
                        'reason' => $result['reason'] ?? 'Berhasil dianalisis oleh AI.',
                    ];
                }
            }

            Log::error('OCR KTM Error: Response invalid', ['response' => $response->body()]);
            return self::defaultFalse('Gagal memproses respon dari AI server.');

        } catch (\Exception $e) {
            Log::error('OCR KTM Exception: ' . $e->getMessage());
            return self::defaultFalse('Terjadi kesalahan saat memproses gambar dengan AI.');
        }
    }

    private static function defaultFalse(string $reason): array
    {
        return [
            'is_valid_ktm' => false,
            'is_match' => false,
            'reason' => $reason,
        ];
    }
}

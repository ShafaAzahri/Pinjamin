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
            // Read image and encode to base64 using Laravel Storage
            if (!Storage::disk('public')->exists($imagePath)) {
                return self::defaultFalse('File foto KTM tidak ditemukan.');
            }

            $imageData = base64_encode(Storage::disk('public')->get($imagePath));
            $fullPath = Storage::disk('public')->path($imagePath);
            $mimeType = @mime_content_type($fullPath) ?: 'image/jpeg';

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

            // Endpoint Google AI Studio Gemini API
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key=' . env('AI_API_KEY');

            $response = Http::withoutVerifying()->timeout(90)->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ],
                            [
                                'inlineData' => [
                                    'mimeType' => $mimeType,
                                    'data' => $imageData
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text');

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

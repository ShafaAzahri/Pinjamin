<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebApiController;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends WebApiController
{
    public function index()
    {
        $response = $this->callApi('GET', '/api/admin/settings');

        $settings = Setting::orderBy('key')->get()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'max_loan_duration' => 'required|integer|min:1',
            'max_loan_duration_type' => 'required|in:hours,days',
            'fine_amount' => 'required|integer|min:0|max:1000000',
            'fine_type' => 'required|in:per_hour,per_day',
            'max_items_borrowed' => 'required|integer|min:1|max:10',
        ]);

        $response = $this->callApi('PUT', '/api/admin/settings', $request->only([
            'max_loan_duration',
            'max_loan_duration_type',
            'fine_amount',
            'fine_type',
            'max_items_borrowed'
        ]));

        if (isset($response['message'])) {
            return back()->with('success', $response['message']);
        }

        return back()->with('error', $response['message'] ?? 'Gagal memperbarui pengaturan.');
    }

    public function whatsapp()
    {
        $whatsappUrl = env('WHATSAPP_SERVER_URL', 'http://pinjamin.test/send');
        $whatsappBaseUrl = str_replace('/send', '', $whatsappUrl);

        return view('admin.settings.whatsapp', compact('whatsappBaseUrl'));
    }

    public function startWhatsapp()
    {
        \Log::info('Mencoba menyalakan server WhatsApp...');

        $whatsappUrl = env('WHATSAPP_SERVER_URL', 'http://pinjamin.test/send');
        $parsedUrl = parse_url($whatsappUrl);
        $host = $parsedUrl['host'] ?? '127.0.0.1';
        if ($host === 'pinjamin.test' || $host === 'localhost') {
            $host = '127.0.0.1';
        }
        $port = $parsedUrl['port'] ?? 3000;

        // Cek apakah port server WhatsApp sudah terpakai
        $connection = @fsockopen($host, $port, $errno, $errstr, 2);
        if (is_resource($connection)) {
            fclose($connection);
            \Log::warning("Gagal menyalakan server WA: Port {$port} pada {$host} sudah terpakai.");
            return back()->with('success', 'Server WhatsApp sudah berjalan.');
        }

        $path = base_path('whatsapp-server');
        \Log::info("Directory target WhatsApp: {$path}");

        // Diagnostik: Cek apakah node terinstal dan bisa diakses dari Apache
        exec("node -v 2>&1", $nodeVerOut, $nodeVerErr);
        \Log::info("Diagnostik Node -v dari web:", [
            'output' => $nodeVerOut,
            'exit_code' => $nodeVerErr
        ]);

        // Ganti working directory PHP sementara ke folder whatsapp-server
        $oldPath = getcwd();
        chdir($path);

        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows: Jalankan node index.js di background dan redirect output ke log file
                // Kita gunakan cmd /c agar output redirection (>) bekerja
                $cmd = 'start /B cmd /c "node index.js > whatsapp.log 2>&1"';
                \Log::info("Menjalankan perintah Windows: {$cmd}");
                $handle = popen($cmd, "r");
                if ($handle === false) {
                    \Log::error('Gagal menjalankan popen pada Windows.');
                } else {
                    pclose($handle);
                    \Log::info('Berhasil memicu popen pada Windows.');
                }
            } else {
                // Linux/macOS
                $cmd = "node index.js > whatsapp.log 2>&1 &";
                \Log::info("Menjalankan perintah Linux: {$cmd}");
                exec($cmd);
            }
        } catch (\Exception $e) {
            \Log::error('Exception saat menyalakan server WA: ' . $e->getMessage());
        }

        // Kembalikan working directory PHP ke semula
        chdir($oldPath);

        return back()->with('success', 'Server WhatsApp sedang dinyalakan di latar belakang.');
    }
}

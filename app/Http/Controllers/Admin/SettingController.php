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
            'max_loan_duration'      => 'required|integer|min:1',
            'max_loan_duration_type' => 'required|in:hours,days',
            'fine_amount'            => 'required|integer|min:0|max:1000000',
            'fine_type'              => 'required|in:per_hour,per_day',
            'max_items_borrowed'     => 'required|integer|min:1|max:10',
        ]);

        $response = $this->callApi('PUT', '/api/admin/settings', $request->only([
            'max_loan_duration', 'max_loan_duration_type', 'fine_amount', 'fine_type', 'max_items_borrowed'
        ]));

        if (isset($response['message'])) {
            return back()->with('success', $response['message']);
        }

        return back()->with('error', $response['message'] ?? 'Gagal memperbarui pengaturan.');
    }

    public function whatsapp()
    {
        $whatsappUrl = env('WHATSAPP_SERVER_URL', 'http://localhost:3000/send');
        $whatsappBaseUrl = str_replace('/send', '', $whatsappUrl);
        
        return view('admin.settings.whatsapp', compact('whatsappBaseUrl'));
    }

    public function startWhatsapp()
    {
        // Cek apakah port 3000 sudah terpakai
        $connection = @fsockopen('127.0.0.1', 3000);
        if (is_resource($connection)) {
            fclose($connection);
            return back()->with('success', 'Server WhatsApp sudah berjalan.');
        }

        $path = base_path('whatsapp-server');
        $batPath = base_path('start-whatsapp.bat');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: Jalankan bat file di background dengan CMD untuk memastikan working directory benar
            // Kita gunakan popen dengan cmd /c agar berjalan terpisah
            pclose(popen("start /B cmd /c \"cd /d \"" . base_path() . "\" && start-whatsapp.bat\"", "r"));
        } else {
            // Linux/macOS
            exec("cd \"{$path}\" && node index.js > /dev/null 2>&1 &");
        }

        return back()->with('success', 'Server WhatsApp berhasil dinyalakan di latar belakang.');
    }
}

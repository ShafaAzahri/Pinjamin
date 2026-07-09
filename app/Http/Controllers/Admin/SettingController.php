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
            'max_loan_duration'  => 'required|integer|min:1|max:72',
            'fine_amount'        => 'required|integer|min:0|max:1000000',
            'fine_type'          => 'required|in:per_hour,per_day',
            'max_items_borrowed' => 'required|integer|min:1|max:10',
        ]);

        $response = $this->callApi('PUT', '/api/admin/settings', $request->only([
            'max_loan_duration', 'fine_amount', 'fine_type', 'max_items_borrowed'
        ]));

        if (isset($response['message'])) {
            return back()->with('success', $response['message']);
        }

        return back()->with('error', $response['message'] ?? 'Gagal memperbarui pengaturan.');
    }
}

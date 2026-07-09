<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Show settings page.
     */
    public function index()
    {
        $settings = Setting::orderBy('key')->get()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update all settings at once.
     */
    public function update(Request $request)
    {
        $request->validate([
            'max_loan_duration'  => 'required|integer|min:1|max:72',
            'fine_per_hour'      => 'required|integer|min:0|max:100000',
            'max_items_borrowed' => 'required|integer|min:1|max:10',
        ], [
            'max_loan_duration.required'  => 'Durasi pinjam wajib diisi.',
            'max_loan_duration.min'       => 'Durasi pinjam minimal 1 jam.',
            'fine_per_hour.required'      => 'Tarif denda wajib diisi.',
            'max_items_borrowed.required' => 'Batas barang wajib diisi.',
        ]);

        $settings = [
            'max_loan_duration'  => $request->input('max_loan_duration'),
            'fine_per_hour'      => $request->input('fine_per_hour'),
            'max_items_borrowed' => $request->input('max_items_borrowed'),
        ];

        foreach ($settings as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }
}

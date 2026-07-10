<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function showCompleteProfileForm()
    {
        $user = Auth::user();
        
        // If profile is already complete and NOT rejected, redirect to catalog
        if (!empty($user->nim) && !empty($user->ktm_photo) && $user->status !== 'ditolak') {
            return redirect('/catalog');
        }

        return view('student.complete_profile', compact('user'));
    }

    public function processCompleteProfile(Request $request)
    {
        $user = Auth::user();

        // If profile is already complete and NOT rejected, redirect
        if (!empty($user->nim) && !empty($user->ktm_photo) && $user->status !== 'ditolak') {
            return redirect('/catalog');
        }

        $request->validate([
            'nim' => 'required|string|max:50|unique:users,nim,' . $user->id,
            'prodi' => 'required|string|max:100',
            'ktm_photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('ktm_photo')->store('ktm', 'public');

        // OCR Verification via 9Router
        $ocrResult = \App\Services\OcrService::verifyKtm($path, $request->nim, $user->name);
        $status = ($ocrResult['is_match'] && $ocrResult['is_valid_ktm']) ? 'aktif' : 'menunggu_verifikasi';

        $user->update([
            'nim' => $request->nim,
            'prodi' => $request->prodi,
            'ktm_photo' => $path,
            'status' => $status,
        ]);

        $msg = $status === 'aktif' 
            ? 'Profil berhasil dilengkapi. KTM Anda terverifikasi oleh AI dan akun Anda sudah aktif.' 
            : 'Profil berhasil dilengkapi. Namun KTM tidak terverifikasi otomatis oleh AI (' . $ocrResult['reason'] . '). Akun menunggu verifikasi admin.';

        return redirect('/catalog')->with('success', $msg);
    }
}

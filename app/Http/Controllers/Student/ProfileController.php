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

        // Update status ke 'menunggu_verifikasi' terlebih dahulu
        $user->update([
            'nim' => $request->nim,
            'prodi' => $request->prodi,
            'ktm_photo' => $path,
            'status' => 'menunggu_verifikasi',
        ]);

        // Picu verifikasi AI di latar belakang (asinkron)
        \App\Jobs\VerifyKtmJob::dispatch($user, $path, $request->nim, $user->name);

        // Jalankan queue worker 1x secara otomatis di background (non-blocking)
        $php = (new \Symfony\Component\Process\PhpExecutableFinder())->find(false) ?: 'php';
        $artisan = base_path('artisan');
        \Illuminate\Support\Facades\Process::start("\"{$php}\" \"{$artisan}\" queue:work --once");

        $msg = 'Profil berhasil dilengkapi. KTM Anda sedang diverifikasi oleh AI di latar belakang. Silakan tunggu beberapa saat.';

        return redirect('/catalog')->with('success', $msg);
    }
}

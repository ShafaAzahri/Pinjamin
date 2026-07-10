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
        
        // If profile is already complete, redirect to catalog
        if (!empty($user->nim) && !empty($user->ktm_photo)) {
            return redirect('/catalog');
        }

        return view('student.complete_profile', compact('user'));
    }

    public function processCompleteProfile(Request $request)
    {
        $user = Auth::user();

        // If profile is already complete, redirect
        if (!empty($user->nim) && !empty($user->ktm_photo)) {
            return redirect('/catalog');
        }

        $request->validate([
            'nim' => 'required|string|max:50|unique:users,nim,' . $user->id,
            'prodi' => 'required|string|max:100',
            'ktm_photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('ktm_photo')->store('ktm', 'public');

        $user->update([
            'nim' => $request->nim,
            'prodi' => $request->prodi,
            'ktm_photo' => $path,
            'status' => 'menunggu_verifikasi',
        ]);

        return redirect('/catalog')->with('success', 'Profil berhasil dilengkapi. Akun Anda sekarang menunggu verifikasi Admin.');
    }
}

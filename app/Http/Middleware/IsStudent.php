<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'user' && Auth::user()->status !== 'ditangguhkan') {
            
            // Periksa apakah profil sudah lengkap (terutama untuk login via SSO)
            $user = Auth::user();
            if (empty($user->nim) || empty($user->ktm_photo)) {
                // Hindari redirect loop jika sudah berada di halaman complete-profile
                if (!$request->is('complete-profile') && !$request->is('complete-profile/*')) {
                    return redirect()->route('student.complete_profile')->with('info', 'Harap perbarui KTM dan data profil Anda.');
                }
            }

            return $next($request);
        }

        Auth::logout();
        return redirect('/login')->withErrors(['email' => 'Akses ditolak. Silakan login menggunakan akun mahasiswa aktif.']);
    }
}

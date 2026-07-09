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
            return $next($request);
        }

        Auth::logout();
        return redirect('/login')->withErrors(['email' => 'Akses ditolak. Silakan login menggunakan akun mahasiswa aktif.']);
    }
}

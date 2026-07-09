<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'admin' ? redirect('/admin/dashboard') : redirect('/catalog');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->status === 'menunggu_verifikasi') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda sedang menunggu verifikasi KTM oleh Admin. Silakan periksa berkala.',
                ]);
            }

            if ($user->status === 'ditangguhkan') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda ditangguhkan karena pelanggaran peraturan lab.',
                ]);
            }

            $request->session()->regenerate();

            return $user->role === 'admin' 
                ? redirect()->intended('/admin/dashboard') 
                : redirect()->intended('/catalog');
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'admin' ? redirect('/admin/dashboard') : redirect('/catalog');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nim' => 'required|string|max:50|unique:users',
            'prodi' => 'required|string|max:100',
            'ktm_photo' => 'required|image|max:2048', // Max 2MB
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique' => 'NIM ini sudah terdaftar.',
            'prodi.required' => 'Program studi wajib diisi.',
            'ktm_photo.required' => 'Foto KTM wajib diunggah.',
            'ktm_photo.image' => 'Berkas KTM harus berupa gambar.',
        ]);

        $path = $request->file('ktm_photo')->store('ktm', 'public');

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'nim' => $request->nim,
            'prodi' => $request->prodi,
            'ktm_photo' => $path,
            'status' => 'menunggu_verifikasi',
        ]);

        return redirect('/login')->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu verifikasi KTM oleh Admin.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

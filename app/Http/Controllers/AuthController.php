<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends WebApiController
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $response = $this->callApi('POST', '/api/auth/login', $request->all());

        if (isset($response['token'])) {
            // Login sukses di API, mari kita buat session login di web
            $user = \App\Models\User::where('email', $request->email)->first();
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return $user->role === 'admin' 
                ? redirect()->intended('/admin/dashboard') 
                : redirect()->intended('/catalog');
        }

        return back()->withErrors([
            'email' => $response['message'] ?? 'Email atau password salah.',
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
        // Karena upload file harus diproses, kita kirim data pendaftaran ke API
        // Catatan: request internal menangani file dengan baik jika dikirim dalam array request.
        $data = $request->all();
        if ($request->hasFile('ktm_photo')) {
            $data['ktm_photo'] = $request->file('ktm_photo');
        }

        $response = $this->callApi('POST', '/api/auth/register', $data);

        if (isset($response['user'])) {
            return redirect('/login')->with('success', $response['message']);
        }

        return back()->withErrors($response['errors'] ?? ['email' => $response['message'] ?? 'Pendaftaran gagal.'])->withInput();
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

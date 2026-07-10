<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SsoController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Filter hanya mengizinkan email kampus (@mhs.polines.ac.id)
            if (!str_ends_with($googleUser->getEmail(), '@mhs.polines.ac.id')) {
                return redirect('/login')->withErrors(['email' => 'Gagal login. Anda harus menggunakan email kampus (@mhs.polines.ac.id).']);
            }

            // Check if user exists by provider_id or email
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Register new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'role' => 'user',
                    'status' => 'menunggu_verifikasi',
                ]);
            } else {
                // Update existing user with provider details if empty
                if (!$user->provider_id) {
                    $user->update([
                        'provider' => 'google',
                        'provider_id' => $googleUser->getId(),
                    ]);
                }
            }

            Auth::login($user, true);

            return redirect()->intended('/catalog');

        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Gagal login menggunakan Google. Silakan coba lagi.']);
        }
    }
}

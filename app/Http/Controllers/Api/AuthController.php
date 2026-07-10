<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * Returns a Sanctum token if credentials are valid.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password yang Anda masukkan salah.'], 401);
        }

        if ($user->status === 'ditangguhkan') {
            return response()->json(['message' => 'Akun Anda ditangguhkan karena pelanggaran peraturan lab.'], 403);
        }

        // Delete old tokens if you want single device login
        // $user->tokens()->delete();

        // Create a new token for the device
        $deviceName = $request->input('device_name', 'mobile-app');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'nim'   => $user->nim,
                'prodi' => $user->prodi,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * POST /api/register
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:8|confirmed',
            'nim'       => 'nullable|string|max:50|unique:users',
            'prodi'     => 'nullable|string|max:100',
            'ktm_photo' => 'nullable|image|max:2048',
        ]);

        $path = null;

        if ($request->hasFile('ktm_photo')) {
            $path = $request->file('ktm_photo')->store('ktm', 'public');
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'user',
            'nim'       => $request->nim,
            'prodi'     => $request->prodi,
            'ktm_photo' => $path,
            'status'    => 'menunggu_verifikasi',
        ]);

        if ($path) {
            // Picu verifikasi AI di latar belakang (asinkron)
            \App\Jobs\VerifyKtmJob::dispatch($user, $path, $request->nim, $request->name);

            // Jalankan queue worker 1x secara otomatis di background (non-blocking & detached)
            $php = (new \Symfony\Component\Process\PhpExecutableFinder())->find(false) ?: 'php';
            $artisan = base_path('artisan');
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B \"\" \"{$php}\" \"{$artisan}\" queue:work --once", "r"));
            } else {
                exec("\"{$php}\" \"{$artisan}\" queue:work --once > /dev/null 2>&1 &");
            }
        }

        $msg = $path 
            ? 'Pendaftaran berhasil! KTM Anda sedang diverifikasi oleh AI di latar belakang.'
            : 'Pendaftaran berhasil! Silakan masuk ke akun Anda dan lengkapi profil.';

        return response()->json([
            'message' => $msg,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'nim'   => $user->nim,
                'prodi' => $user->prodi,
            ],
        ], 201);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'nim'           => $user->nim,
            'prodi'         => $user->prodi,
            'role'          => $user->role,
            'status'        => $user->status,
            'profile_photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
            'ktm_photo'     => $user->ktm_photo ? asset('storage/' . $user->ktm_photo) : null,
        ]);
    }

    /**
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if ($user->status === 'aktif' && $user->ktm_photo && $request->hasFile('ktm_photo')) {
            return response()->json(['message' => 'KTM Anda sudah disetujui dan tidak dapat diubah lagi.'], 422);
        }

        $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'email'         => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|max:20',
            'password'      => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:2048',
            'ktm_photo'     => 'nullable|image|max:2048',
        ]);

        $data = [];
        
        if ($request->has('name')) {
            $data['name'] = $request->name;
        }
        if ($request->has('email')) {
            $data['email'] = $request->email;
        }
        if ($request->has('phone')) {
            $data['phone'] = $request->phone;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $data['profile_photo'] = $path;
        }

        if ($request->hasFile('ktm_photo')) {
            if ($user->ktm_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->ktm_photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->ktm_photo);
            }
            $path = $request->file('ktm_photo')->store('ktm', 'public');
            $data['ktm_photo'] = $path;

            // Set ke 'menunggu_verifikasi' agar dicek ulang oleh AI dan admin
            $data['status'] = 'menunggu_verifikasi';

            // Parameter verifikasi AI
            $nim = $user->nim;
            $name = $request->input('name', $user->name);
        }

        $user->update($data);

        if ($request->hasFile('ktm_photo')) {
            // Picu verifikasi AI di latar belakang (asinkron)
            \App\Jobs\VerifyKtmJob::dispatch($user, $path, $nim, $name);

            // Jalankan queue worker 1x secara otomatis di background (non-blocking & detached)
            $php = (new \Symfony\Component\Process\PhpExecutableFinder())->find(false) ?: 'php';
            $artisan = base_path('artisan');
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B \"\" \"{$php}\" \"{$artisan}\" queue:work --once", "r"));
            } else {
                exec("\"{$php}\" \"{$artisan}\" queue:work --once > /dev/null 2>&1 &");
            }
        }

        $msg = 'Profil Anda berhasil diperbarui!';
        if ($request->hasFile('ktm_photo')) {
            $msg = 'Profil diperbarui. KTM Anda sedang diverifikasi oleh AI di latar belakang.';
        }

        return response()->json([
            'message' => $msg,
            'user'    => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'nim'           => $user->nim,
                'prodi'         => $user->prodi,
                'status'        => $user->status,
                'profile_photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
                'ktm_photo'     => $user->ktm_photo ? asset('storage/' . $user->ktm_photo) : null,
            ],
        ]);
    }
}

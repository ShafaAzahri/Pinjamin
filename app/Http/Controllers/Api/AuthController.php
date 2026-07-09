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

        if ($user->status === 'menunggu_verifikasi') {
            return response()->json(['message' => 'Akun Anda sedang menunggu verifikasi KTM oleh Admin.'], 403);
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
            'nim'       => 'required|string|max:50|unique:users',
            'prodi'     => 'required|string|max:100',
            'ktm_photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('ktm_photo')->store('ktm', 'public');

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

        return response()->json([
            'message' => 'Pendaftaran berhasil! Akun Anda sedang menunggu verifikasi KTM oleh Admin.',
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
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'nim'    => $user->nim,
            'prodi'  => $user->prodi,
            'role'   => $user->role,
            'status' => $user->status,
        ]);
    }
}

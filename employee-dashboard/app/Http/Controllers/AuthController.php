<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User2;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string|alpha_num:ascii|max:50',
            'password' => 'required|string',
        ]);

        $user = User2::with('role')->where('username', $credentials['username'])->first();

        // Cek user ada dan password cocok
        $passwordMatches = false;

        if ($user) {
            $passwordInfo = password_get_info($user->password);
            $passwordMatches = $passwordInfo['algo'] !== 0
                ? Hash::check($credentials['password'], $user->password)
                : hash_equals($user->password, $credentials['password']);

            if ($passwordMatches && $passwordInfo['algo'] === 0) {
                $user->forceFill(['password' => Hash::make($credentials['password'])])->save();
            }
        }

        if (!$passwordMatches) {
            return response()->json(['message' => 'Username atau password salah'], 401);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role?->nama_role,
            'user' => [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'karyawan_id_karyawan' => $user->karyawan_id_karyawan,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}
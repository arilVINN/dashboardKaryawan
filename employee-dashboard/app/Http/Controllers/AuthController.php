<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User2;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required'
        ]);

        $user = User2::with('role')->where('username', $request->username)->first();

        // Cek user ada dan password cocok
        $passwordMatches = false;

        if ($user) {
            $passwordMatches = ! empty($user->password) && Hash::check($request->password, $user->password);
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

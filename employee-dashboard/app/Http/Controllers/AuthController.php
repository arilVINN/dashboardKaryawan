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
            $passwordInfo = password_get_info($user->password);
            $passwordMatches = $passwordInfo['algo'] !== 0
                ? Hash::check($request->password, $user->password)
                : hash_equals($user->password, $request->password);

            if ($passwordMatches && $passwordInfo['algo'] === 0) {
                $user->forceFill(['password' => Hash::make($request->password)])->save();
            }
        }

        if (!$passwordMatches) {
            return response()->json(['message' => 'Username atau password salah'], 401);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role->nama_role ?? 'Tidak ada role'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}
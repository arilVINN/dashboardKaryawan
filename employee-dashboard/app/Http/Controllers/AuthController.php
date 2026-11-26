<?php

namespace App\Http\Controllers;

use App\Models\User2;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/'],
            'password' => 'required|string',
        ]);

        $isApiRequest = $request->is('api/*');

        $user = User2::with('role')->where('username', $credentials['username'])->first();

        $passwordMatches = false;

        if ($user) {
            $passwordInfo = password_get_info($user->password);

            // Only real password hashes may authenticate. Plaintext (or any
            // non-hashed) values are treated as invalid: never compared with
            // hash_equals and never silently auto-upgraded, so a legacy or
            // leaked plaintext column cannot be used to log in.
            $storedIsHashed = ! empty($user->password)
                && ! in_array($passwordInfo['algo'], [null, 0], true);

            if ($storedIsHashed) {
                $passwordMatches = Hash::check($credentials['password'], $user->password);
            }
        }

        if (! $passwordMatches) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Username atau password salah'], 401);
            }

            return back()->withErrors(['username' => 'Username atau password salah']);
        }

        // Checked after the password so a wrong password on an inactive
        // account still reads as bad credentials; a correct one is told
        // plainly the account is deactivated (admin-created accounts only).
        if (($user->karyawan?->status_aktif ?? 'Aktif') === 'Tidak Aktif') {
            $message = 'Akun Anda dinonaktifkan. Hubungi HRD untuk informasi lebih lanjut.';
            if ($request->expectsJson() || $isApiRequest) {
                return response()->json(['message' => $message], 403);
            }

            return back()->withErrors(['username' => $message]);
        }

        try {
            $user->update(['last_login_at' => now()]);
        } catch (QueryException $exception) {
            report($exception);
        }

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        if ($request->expectsJson() || $isApiRequest) {
            // Sertakan User-Agent agar token bisa diaudit per device/browser.
            $deviceName = substr($request->userAgent() ?? 'api-client', 0, 80);
            $token = $user->createToken($deviceName)->plainTextToken;

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

        $roleName = strtolower($user->role?->nama_role ?? '');

        if ($roleName === 'direktur' || $roleName === 'direksi' || $roleName === 'superadmin') {
            return redirect()->route('direktur.dashboard');
        } elseif ($roleName === 'hrd') {
            return redirect()->intended('/hrd/dashboard');
        } elseif ($roleName === 'kadiv') {
            return redirect()->intended('/kadiv/dashboard');
        } else {
            return redirect()->intended('/');
        }
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        if ($request->is('api/*')) {
            return response()->json(['message' => 'Logout berhasil']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logout berhasil']);
        }

        return redirect('/login');
    }
}

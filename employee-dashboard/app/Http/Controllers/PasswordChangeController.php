<?php

namespace App\Http\Controllers;

use App\Services\EmployeeWriteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordChangeController extends Controller
{
    public function show()
    {
        return view('gantiPassword');
    }

    public function store(Request $request, EmployeeWriteService $service)
    {
        $validated = $request->validate([
            'password_lama' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_lama.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = $request->user();
        if (! $user || ! Hash::check($validated['password_lama'], $user->password)) {
            throw ValidationException::withMessages([
                'password_lama' => ['Password saat ini tidak benar.'],
            ]);
        }

        $service->changeOwnPassword($user, $validated['password']);
        $request->session()->put('session_version', $user->fresh()->session_version);

        return redirect('/')->with('success', 'Password berhasil diubah.');
    }
}

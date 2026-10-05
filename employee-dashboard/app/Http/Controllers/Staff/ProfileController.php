<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('karyawan.divisi');

        return response()->json([
            'message' => 'Data profil',
            'data' => [
                'nama' => $user->karyawan->nama ?? $user->username,
                'email' => $user->karyawan->email ?? '-',
                'alamat' => $user->karyawan->alamat ?? '-',
                'jabatan' => $user->karyawan->jabatan ?? '-',
                'divisi' => $user->karyawan->divisi->nama_divisi ?? '-',
                'no_telepon' => $user->karyawan->no_telepon ?? '-',
                'tanggal_rekrut' => $user->karyawan->tanggal_rekrut ?? '-',
                'jenis_kelamin' => $user->karyawan->jenis_kelamin ?? '-',
                'tanggal_dibuat' => $user->created_at ? $user->created_at->format('d F Y') : '-'
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'no_telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
        ]);

        if ($user->karyawan) {
            $user->karyawan->update($validated);
        }

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password saat ini salah.'], 400);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}

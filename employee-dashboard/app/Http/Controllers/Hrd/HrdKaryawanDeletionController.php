<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Karyawan;
use App\Models\Tugas;
use App\Services\HrdProtectionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HrdKaryawanDeletionController extends Controller
{
    /**
     * Delete a karyawan if no accepted tasks exist.
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Load karyawan with related user and tasks
        $karyawan = Karyawan::with(['user.role', 'user.tugas'])->findOrFail($id);

        // Guard keamanan akun HRD
        $authUser = auth()->user();
        if ($errorMsg = HrdProtectionService::validateDeletion($authUser, $karyawan)) {
            return response()->json(['message' => $errorMsg], 403);
        }

        // If there is no related user, we can safely delete the karyawan record
        if (!$karyawan->user) {
            $nama = $karyawan->nama;
            $karyawan->delete();

            AuditLog::catat(
                $authUser?->id_user,
                'hapus_karyawan',
                'karyawan',
                $id,
                ['nama' => $nama, 'keterangan' => 'Karyawan tanpa akun dihapus']
            );

            return response()->json(['message' => 'Karyawan berhasil dihapus.'], 200);
        }

        // Check if any task of the user has status "sudah di-acc"
        $hasAcceptedTask = $karyawan->user->tugas->contains(function ($t) {
            return $t->status === Tugas::STATUS_SUDAH_ACC;
        });

        if ($hasAcceptedTask) {
            return response()->json([
                'message' => 'Tidak dapat menghapus karyawan karena ada tugas yang sudah di‑acc.'
            ], 403);
        }

        $nama = $karyawan->nama;
        $username = $karyawan->user->username;
        $roleName = $karyawan->user->role?->nama_role;

        // No accepted tasks – delete user then karyawan
        $karyawan->user->delete();
        $karyawan->delete();

        AuditLog::catat(
            $authUser?->id_user,
            'hapus_karyawan',
            'karyawan',
            $id,
            [
                'nama'     => $nama,
                'username' => $username,
                'role'     => $roleName,
            ]
        );

        return response()->json(['message' => 'Karyawan berhasil dihapus.'], 200);
    }
}


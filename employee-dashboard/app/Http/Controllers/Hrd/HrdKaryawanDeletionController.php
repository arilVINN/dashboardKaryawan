<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HrdKaryawanDeletionController extends Controller
{
    /**
     * Delete a karyawan if no accepted tasks exist.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Load karyawan with related user and tasks
        $karyawan = Karyawan::with('user.tugas')->findOrFail($id);

        // If there is no related user, we can safely delete the karyawan record
        if (!$karyawan->user) {
            $karyawan->delete();
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

        // No accepted tasks – delete user then karyawan
        $karyawan->user->delete();
        $karyawan->delete();

        return response()->json(['message' => 'Karyawan berhasil dihapus.'], 200);
    }
}

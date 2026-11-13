<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Karyawan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StrukturOrganisasiController extends Controller
{
    /**
     * Tampilkan halaman Bagan Struktur Organisasi.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user?->role?->nama_role ?? 'staff');
        $karyawan = $user?->karyawan;

        $daftarDivisi = Divisi::select('id_divisi', 'kode_divisi', 'nama_divisi')
            ->orderBy('nama_divisi')
            ->get();

        $daftarKaryawanAktif = Karyawan::with('divisi')
            ->where(function ($q) {
                $q->where('status_aktif', 'Aktif')
                    ->orWhereNull('status_aktif');
            })
            ->where(function ($q) {
                $q->where('aktif', true)
                    ->orWhereNull('aktif');
            })
            ->whereNull('tanggal_keluar')
            ->orderBy('nama')
            ->get();

        return view('organisasi.bagan', [
            'role' => $role,
            'isHrd' => ($role === 'hrd'),
            'currentKaryawan' => $karyawan,
            'daftarDivisi' => $daftarDivisi,
            'daftarKaryawanAktif' => $daftarKaryawanAktif,
        ]);
    }

    /**
     * Mengambil struktur pohon organisasi dalam bentuk JSON.
     */
    public function getTreeData(Request $request): JsonResponse
    {
        $divisiId = $request->query('divisi_id');

        // Ambil semua karyawan aktif
        $allActive = Karyawan::with(['divisi', 'user', 'atasan'])
            ->where(function ($q) {
                $q->where('status_aktif', 'Aktif')
                    ->orWhereNull('status_aktif');
            })
            ->where(function ($q) {
                $q->where('aktif', true)
                    ->orWhereNull('aktif');
            })
            ->whereNull('tanggal_keluar')
            ->get();

        // Cari karyawan yang non-aktif/keluar tapi masih dirujuk sebagai atasan_id oleh karyawan aktif
        $activeAtasanIds = $allActive->pluck('atasan_id')->filter()->unique();
        $activeIds = $allActive->pluck('id_karyawan')->all();
        $missingAtasanIds = $activeAtasanIds->diff($activeIds)->values();

        // Data pejabat yang keluar/non-aktif untuk dibuatkan node "Posisi Kosong"
        $vacantBosses = collect();
        if ($missingAtasanIds->isNotEmpty()) {
            $vacantBosses = Karyawan::with('divisi')
                ->whereIn('id_karyawan', $missingAtasanIds)
                ->get();
        }

        // Petakan tiap karyawan menjadi node standar
        $nodesById = [];
        foreach ($allActive as $k) {
            $level = $this->determineJobLevel($k);
            $nodesById[$k->id_karyawan] = [
                'id' => $k->id_karyawan,
                'nama' => $k->nama,
                'jabatan' => $k->jabatan ?? 'Staff',
                'divisi' => $k->divisi->nama_divisi ?? 'Umum',
                'divisi_id' => $k->divisi_id_divisi,
                'foto' => $k->foto ? asset('storage/' . $k->foto) : null,
                'email' => $k->email ?? '-',
                'no_telepon' => $k->no_telepon ?? '-',
                'alamat' => $k->alamat ?? '-',
                'status_karyawan' => $k->status_karyawan ?? 'Aktif',
                'atasan_id' => $k->atasan_id,
                'atasan_nama' => $k->atasan?->nama ?? null,
                'user_id' => $k->user?->id_user ?? null,
                'level' => $level,
                'is_vacant' => false,
                'children' => [],
            ];
        }

        // Tambahkan node "Posisi Kosong" jika atasannya keluar/nonaktif
        foreach ($vacantBosses as $vb) {
            $nodesById[$vb->id_karyawan] = [
                'id' => $vb->id_karyawan,
                'nama' => '[ Posisi Kosong ]',
                'jabatan' => $vb->jabatan ?? 'Kepala Posisi',
                'divisi' => $vb->divisi->nama_divisi ?? 'Umum',
                'divisi_id' => $vb->divisi_id_divisi,
                'foto' => null,
                'email' => '-',
                'no_telepon' => '-',
                'alamat' => '-',
                'status_karyawan' => 'Menunggu Penunjukan',
                'atasan_id' => $vb->atasan_id,
                'atasan_nama' => null,
                'user_id' => null,
                'level' => $this->determineJobLevel($vb),
                'is_vacant' => true,
                'children' => [],
            ];
        }

        // Hubungkan children ke parent
        $roots = [];
        foreach ($nodesById as $id => &$node) {
            $parentId = $node['atasan_id'];
            if (!empty($parentId) && isset($nodesById[$parentId]) && $parentId !== $id) {
                $nodesById[$parentId]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }
        unset($node);

        // Jika filter divisi dipilih
        if (!empty($divisiId) && $divisiId !== 'all') {
            $filteredRoots = [];
            foreach ($nodesById as $id => $node) {
                if ($node['divisi_id'] === $divisiId) {
                    $parentIsSameDiv = isset($nodesById[$node['atasan_id']]) && $nodesById[$node['atasan_id']]['divisi_id'] === $divisiId;
                    if (!$parentIsSameDiv) {
                        $filteredRoots[] = $node;
                    }
                }
            }
            $roots = $filteredRoots;
        }

        return response()->json([
            'success' => true,
            'roots' => $roots,
            'total_nodes' => count($nodesById),
        ]);
    }

    /**
     * Update Atasan Karyawan (Khusus HRD).
     */
    public function updateAtasan(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (strtolower($user?->role?->nama_role ?? '') !== 'hrd') {
            return response()->json(['success' => false, 'message' => 'Hanya HRD yang diizinkan mengubah bagan struktur.'], 403);
        }

        $validated = $request->validate([
            'id_karyawan' => 'required|exists:karyawans,id_karyawan',
            'atasan_id' => 'nullable|exists:karyawans,id_karyawan',
        ]);

        $karyawanId = $validated['id_karyawan'];
        $atasanId = $validated['atasan_id'] ?: null;

        // Cegah atasan diri sendiri
        if ($karyawanId === $atasanId) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak bisa menjadi atasan untuk dirinya sendiri.'], 422);
        }

        // Cegah siklus/circular dependency (misal A bawahan B, B tidak boleh jadi bawahan A)
        if ($atasanId && $this->isDescendantOf($atasanId, $karyawanId)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menetapkan bawahan sebagai atasan (terjadi hubungan sirkular).',
            ], 422);
        }

        $karyawan = Karyawan::findOrFail($karyawanId);
        $karyawan->atasan_id = $atasanId;
        $karyawan->save();

        return response()->json([
            'success' => true,
            'message' => 'Posisi bagan untuk ' . $karyawan->nama . ' berhasil diperbarui.',
        ]);
    }

    /**
     * Tunjuk pejabat baru untuk mengisi posisi kosong (Khusus HRD).
     */
    public function assignVacant(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (strtolower($user?->role?->nama_role ?? '') !== 'hrd') {
            return response()->json(['success' => false, 'message' => 'Hanya HRD yang diizinkan.'], 403);
        }

        $validated = $request->validate([
            'vacant_id' => 'required',
            'new_karyawan_id' => 'required|exists:karyawans,id_karyawan',
        ]);

        $vacantId = $validated['vacant_id'];
        $newKaryawanId = $validated['new_karyawan_id'];

        // Karyawan baru menggantikan posisi:
        // 1. Ambil bawahan dari vacant_id, alihkan atasan_id mereka ke $newKaryawanId
        Karyawan::where('atasan_id', $vacantId)->update(['atasan_id' => $newKaryawanId]);

        // 2. Ambil atasan dari mantan vacant, jadikan atasan bagi $newKaryawanId
        $formerBoss = Karyawan::find($vacantId);
        if ($formerBoss && $formerBoss->atasan_id && $formerBoss->atasan_id !== $newKaryawanId) {
            $newKaryawan = Karyawan::find($newKaryawanId);
            if ($newKaryawan) {
                $newKaryawan->atasan_id = $formerBoss->atasan_id;
                $newKaryawan->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pejabat baru berhasil ditugaskan pada posisi tersebut.',
        ]);
    }

    /**
     * Menentukan tingkatan warna / level hierarki.
     */
    private function determineJobLevel(Karyawan $karyawan): int
    {
        $jabatan = strtolower($karyawan->jabatan ?? '');
        $jobLevel = strtolower($karyawan->job_level ?? '');

        // Level 1: CEO, Direktur, HRD Manager, General Manager, Pucuk Pimpinan
        if (
            str_contains($jabatan, 'direktur') ||
            str_contains($jabatan, 'ceo') ||
            str_contains($jabatan, 'hrd manager') ||
            str_contains($jabatan, 'general manager') ||
            str_contains($jobLevel, 'c-level') ||
            str_contains($jobLevel, 'direktur')
        ) {
            return 1;
        }

        // Level 2: Kepala Divisi, Manager, Kadiv
        if (
            str_contains($jabatan, 'kepala divisi') ||
            str_contains($jabatan, 'kadiv') ||
            str_contains($jabatan, 'manager') ||
            $karyawan->hasRole('kadiv')
        ) {
            return 2;
        }

        // Level 3: Wakil Kepala Divisi, Supervisor, Foreman, Koordinator
        if (
            str_contains($jabatan, 'wakil') ||
            str_contains($jabatan, 'supervisor') ||
            str_contains($jabatan, 'foreman') ||
            str_contains($jabatan, 'lead') ||
            str_contains($jabatan, 'koordinator')
        ) {
            return 3;
        }

        // Level 4: Staff, Officer, Programmer, Writer, Workers
        return 4;
    }

    /**
     * Cek apakah $possibleDescendantId adalah bawahan dari $ancestorId.
     */
    private function isDescendantOf(string $possibleDescendantId, string $ancestorId): bool
    {
        $current = Karyawan::find($possibleDescendantId);
        $visited = [];

        while ($current && !empty($current->atasan_id)) {
            if ($current->atasan_id === $ancestorId) {
                return true;
            }
            if (in_array($current->atasan_id, $visited, true)) {
                break; // loop guard
            }
            $visited[] = $current->atasan_id;
            $current = Karyawan::find($current->atasan_id);
        }

        return false;
    }

    /**
     * Filter pohon rekursif berdasarkan divisi.
     */
    private function filterTreeByDivisi(array $node, string $divisiId): ?array
    {
        $filteredChildren = [];
        foreach ($node['children'] as $child) {
            $filteredChild = $this->filterTreeByDivisi($child, $divisiId);
            if ($filteredChild !== null) {
                $filteredChildren[] = $filteredChild;
            }
        }

        if ($node['divisi_id'] === $divisiId || !empty($filteredChildren)) {
            $node['children'] = $filteredChildren;
            return $node;
        }

        return null;
    }
}

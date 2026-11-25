<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\StrukturOrganisasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StrukturOrganisasiController extends Controller
{
    /**
     * Tampilkan halaman Bagan Struktur Organisasi dengan tata letak asli PT Silindo.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user?->role?->nama_role ?? 'staff');
        // Read-only monitors (direktur/direksi) may view the chart but the
        // edit controls stay hidden for them.
        $canManage = in_array($role, ['hrd', 'superadmin'], true);

        $sidebar = match ($role) {
            'kadiv' => 'component_kadiv.sidebar',
            'staff' => 'component.sidebar',
            'direktur', 'direksi', 'superadmin' => 'component_direktur.sidebar',
            default => 'component_hrd.sidebar',
        };

        $dashboardUrl = match ($role) {
            'kadiv' => route('kadiv.dashboard'),
            'staff' => url('/'),
            'direktur', 'direksi', 'superadmin' => route('direktur.dashboard'),
            default => route('hrd.dashboard'),
        };

        // Ambil semua node terurut
        $allNodes = StrukturOrganisasi::with(['karyawan', 'parent'])
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 1. Pimpinan Utama
        $pengawas = $allNodes->first(fn($n) => str_contains(strtolower($n->jabatan), 'pengawas'))
            ?? $allNodes->where('level', 1)->first()
            ?? $allNodes->first();

        $dirUtama = $allNodes->first(fn($n) => str_contains(strtolower($n->jabatan), 'direktur utama'))
            ?? $allNodes->where('level', 2)->first();

        $dirOps = $allNodes->first(fn($n) => str_contains(strtolower($n->jabatan), 'direktur operasional'));

        $execIds = array_filter([$pengawas?->id, $dirUtama?->id, $dirOps?->id]);

        // 2. Jajaran Pendukung (Support Level: Legal, PA, Writer, Finance, HR & GA)
        $legalMembers = $allNodes->filter(fn($n) => in_array($n->divisi, ['Legal & Compliance', 'Legal']) && !in_array($n->id, $execIds))->values();
        $paMembers = $allNodes->filter(fn($n) => (str_contains(strtolower($n->jabatan), 'personal assistant') || str_contains(strtolower($n->jabatan), 'asisten pribadi')) && !in_array($n->id, $execIds))->values();
        $writerMembers = $allNodes->filter(fn($n) => ($n->divisi === 'Writer' || str_contains(strtolower($n->jabatan), 'writer')) && !in_array($n->id, $execIds))->values();
        $financeMembers = $allNodes->filter(fn($n) => in_array($n->divisi, ['Finance', 'Finance & Accounting', 'Keuangan']) && !in_array($n->id, $execIds))->values();
        $hrMembers = $allNodes->filter(fn($n) => in_array($n->divisi, ['HR & GA', 'HR', 'Human Resources']) && !in_array($n->id, $execIds))->values();

        $supportIds = array_merge(
            $legalMembers->pluck('id')->all(),
            $paMembers->pluck('id')->all(),
            $writerMembers->pluck('id')->all(),
            $financeMembers->pluck('id')->all(),
            $hrMembers->pluck('id')->all()
        );

        // 3. Departemen Operasional (Level 4: 6 Kolom Standar + Divisi Kustom Lainnya)
        $standardDeptNames = [
            'Kelas Kerjasama',
            'Publikasi Buku',
            'Publikasi Jurnal',
            'Academic Event',
            'Kreatif Digital',
            'IT',
        ];

        $assignedIds = array_merge($execIds, $supportIds);
        $otherMembers = $allNodes->filter(fn($n) => !in_array($n->id, $assignedIds));

        $allDeptNames = collect($standardDeptNames)
            ->merge($otherMembers->pluck('divisi')->filter())
            ->unique()
            ->values();

        $deptGroups = [];
        foreach ($allDeptNames as $deptName) {
            $members = $allNodes->filter(function ($n) use ($deptName, $assignedIds) {
                return !in_array($n->id, $assignedIds)
                    && strcasecmp((string)$n->divisi, $deptName) === 0;
            })->values();

            $deptGroups[$deptName] = $members;
        }

        // Daftar divisi untuk dropdown form dan filter
        $daftarDivisi = Divisi::select('nama_divisi')
            ->orderBy('nama_divisi')
            ->pluck('nama_divisi')
            ->merge($allNodes->pluck('divisi')->filter())
            ->merge($standardDeptNames)
            ->merge(['Legal & Compliance', 'Writer', 'Finance & Accounting', 'HR & GA'])
            ->unique()
            ->filter()
            ->sort()
            ->values();

        // Daftar karyawan aktif untuk autofill
        $daftarKaryawan = Karyawan::select('id_karyawan', 'nama', 'jabatan', 'divisi_id_divisi', 'email', 'foto')
            ->with('divisi:id_divisi,nama_divisi')
            ->where(function ($q) {
                $q->where('status_aktif', 'Aktif')->orWhereNull('status_aktif');
            })
            ->whereNull('tanggal_keluar')
            ->orderBy('nama')
            ->get();

        return view('hrd.strukturOrganisasi', compact(
            'role',
            'canManage',
            'sidebar',
            'dashboardUrl',
            'pengawas',
            'dirUtama',
            'dirOps',
            'legalMembers',
            'paMembers',
            'writerMembers',
            'financeMembers',
            'hrMembers',
            'deptGroups',
            'daftarDivisi',
            'daftarKaryawan',
            'allNodes'
        ));
    }

    /**
     * Mengambil data pohon hierarki dalam format JSON.
     */
    public function getTreeData(Request $request): JsonResponse
    {
        $search = trim($request->query('search', ''));
        $filterDivisi = trim($request->query('divisi', ''));

        $nodes = StrukturOrganisasi::with(['karyawan', 'parent'])
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $nodesById = [];
        foreach ($nodes as $n) {
            $fotoUrl = null;
            if ($n->foto) {
                $fotoUrl = asset('storage/' . $n->foto);
            } elseif ($n->karyawan?->foto) {
                $fotoUrl = asset('storage/' . $n->karyawan->foto);
            }

            $matchesSearch = true;
            if ($search !== '') {
                $searchLower = mb_strtolower($search);
                $matchesSearch = str_contains(mb_strtolower($n->nama), $searchLower)
                    || str_contains(mb_strtolower($n->jabatan), $searchLower)
                    || str_contains(mb_strtolower((string)$n->divisi), $searchLower);
            }

            $matchesDivisi = true;
            if ($filterDivisi !== '' && strtolower($filterDivisi) !== 'all') {
                $matchesDivisi = strcasecmp((string)$n->divisi, $filterDivisi) === 0;
            }

            $nodesById[$n->id] = [
                'id'             => $n->id,
                'nama'           => $n->nama,
                'jabatan'        => $n->jabatan,
                'tipe_node'      => $n->tipe_node ?? 'orang',
                'status_kerja'   => $n->status_kerja ?? 'tetap',
                'garis_putus'    => (bool)($n->garis_putus || in_array($n->status_kerja, ['magang', 'probation'])),
                'divisi'         => $n->divisi ?? '-',
                'parent_id'      => $n->parent_id,
                'parent_nama'    => $n->parent?->nama,
                'urutan'         => $n->urutan,
                'level'          => $n->level,
                'foto'           => $fotoUrl,
                'initial'        => mb_strtoupper(mb_substr($n->nama, 0, 1)),
                'email'          => $n->email ?? '-',
                'no_telepon'     => $n->no_telepon ?? '-',
                'keterangan'     => $n->keterangan ?? '-',
                'id_karyawan'    => $n->id_karyawan,
                'matches_search' => $matchesSearch,
                'matches_divisi' => $matchesDivisi,
            ];
        }

        return response()->json([
            'success'     => true,
            'nodes'       => array_values($nodesById),
            'total_nodes' => count($nodesById),
        ]);
    }

    /**
     * Ambil data detail 1 posisi karyawan.
     */
    public function show($id): JsonResponse
    {
        $node = StrukturOrganisasi::with(['parent', 'children', 'karyawan'])->findOrFail($id);

        $fotoUrl = null;
        if ($node->foto) {
            $fotoUrl = asset('storage/' . $node->foto);
        } elseif ($node->karyawan?->foto) {
            $fotoUrl = asset('storage/' . $node->karyawan->foto);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $node->id,
                'nama'         => $node->nama,
                'jabatan'      => $node->jabatan,
                'tipe_node'    => $node->tipe_node ?? 'orang',
                'status_kerja' => $node->status_kerja ?? 'tetap',
                'garis_putus'  => (bool)($node->garis_putus || in_array($node->status_kerja, ['magang', 'probation'])),
                'divisi'       => $node->divisi,
                'parent_id'    => $node->parent_id,
                'parent_nama'  => $node->parent?->nama,
                'parent_jab'   => $node->parent?->jabatan,
                'urutan'       => $node->urutan,
                'level'        => $node->level,
                'foto'         => $fotoUrl,
                'initial'      => mb_strtoupper(mb_substr($node->nama, 0, 1)),
                'email'        => $node->email,
                'no_telepon'   => $node->no_telepon,
                'keterangan'   => $node->keterangan,
                'id_karyawan'  => $node->id_karyawan,
            ],
        ]);
    }

    /**
     * Simpan posisi karyawan baru (CRUD: Create).
     */
    public function store(Request $request)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'nama'         => 'required|string|max:255',
            'jabatan'      => 'required|string|max:255',
            'divisi'       => 'required|string|max:255',
            'status_kerja' => 'nullable|string|in:tetap,probation,magang,kontrak',
            'garis_putus'  => 'nullable|boolean',
            'parent_id'    => 'nullable|exists:struktur_organisasis,id',
            'urutan'       => 'nullable|integer|min:1',
            'email'        => 'nullable|email|max:255',
            'no_telepon'   => 'nullable|string|max:50',
            'keterangan'   => 'nullable|string|max:1000',
            'id_karyawan'  => 'nullable|exists:karyawans,id_karyawan',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $parentId = !empty($validated['parent_id']) ? (int)$validated['parent_id'] : null;

        // Jika parent tidak diisi, otomatis hubungkan ke atasan divisi terkait atau Direktur Operasional
        if (!$parentId) {
            $divisiAtasan = StrukturOrganisasi::where('divisi', $validated['divisi'])
                ->orderBy('level', 'asc')
                ->orderBy('urutan', 'asc')
                ->first();

            if ($divisiAtasan) {
                $parentId = $divisiAtasan->id;
            } else {
                $dirOps = StrukturOrganisasi::where('jabatan', 'LIKE', '%Direktur Operasional%')->first();
                $dirUtama = StrukturOrganisasi::where('jabatan', 'LIKE', '%Direktur Utama%')->first();
                $parentId = $dirOps?->id ?? $dirUtama?->id;
            }
        }

        // Hitung level
        $level = 1;
        if ($parentId) {
            $parent = StrukturOrganisasi::find($parentId);
            $level = $parent ? $parent->level + 1 : 1;
        }

        // Tentukan urutan di dalam divisi
        if (empty($validated['urutan'])) {
            $maxUrutan = StrukturOrganisasi::where('divisi', $validated['divisi'])->max('urutan');
            $urutan = $maxUrutan ? $maxUrutan + 1 : 1;
        } else {
            $urutan = (int)$validated['urutan'];
        }

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('struktur-organisasi', 'public');
        }

        $statusKerja = $validated['status_kerja'] ?? 'tetap';
        $garisPutus = $request->has('garis_putus')
            ? $request->boolean('garis_putus')
            : in_array($statusKerja, ['magang', 'probation']);

        $node = StrukturOrganisasi::create([
            'nama'         => $validated['nama'],
            'jabatan'      => $validated['jabatan'],
            'divisi'       => $validated['divisi'],
            'tipe_node'    => 'orang',
            'status_kerja' => $statusKerja,
            'garis_putus'  => $garisPutus,
            'parent_id'    => $parentId,
            'urutan'       => $urutan,
            'level'        => $level,
            'foto'         => $fotoPath,
            'email'        => $validated['email'] ?? null,
            'no_telepon'   => $validated['no_telepon'] ?? null,
            'keterangan'   => $validated['keterangan'] ?? null,
            'id_karyawan'  => $validated['id_karyawan'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Karyawan '{$node->nama}' ({$node->jabatan}) berhasil ditambahkan ke Divisi {$node->divisi}.",
                'data'    => $node,
            ], 201);
        }

        return back()->with('success', "Karyawan '{$node->nama}' ({$node->jabatan}) berhasil ditambahkan ke Divisi {$node->divisi}.");
    }

    /**
     * Perbarui data posisi karyawan (CRUD: Update).
     */
    public function update(Request $request, $id)
    {
        $this->authorizeManagement();

        $node = StrukturOrganisasi::findOrFail($id);

        $validated = $request->validate([
            'nama'         => 'required|string|max:255',
            'jabatan'      => 'required|string|max:255',
            'divisi'       => 'required|string|max:255',
            'status_kerja' => 'nullable|string|in:tetap,probation,magang,kontrak',
            'garis_putus'  => 'nullable|boolean',
            'parent_id'    => 'nullable|exists:struktur_organisasis,id',
            'urutan'       => 'nullable|integer|min:1',
            'email'        => 'nullable|email|max:255',
            'no_telepon'   => 'nullable|string|max:50',
            'keterangan'   => 'nullable|string|max:1000',
            'id_karyawan'  => 'nullable|exists:karyawans,id_karyawan',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'hapus_foto'   => 'nullable|boolean',
        ]);

        $newParentId = !empty($validated['parent_id']) ? (int)$validated['parent_id'] : null;

        // Cegah circular loop jika memilih parent
        if ($newParentId && $newParentId !== $node->parent_id) {
            if ($newParentId === (int)$node->id) {
                return $this->errorResponse($request, 'Posisi tidak dapat dijadikan atasan bagi dirinya sendiri.', 422);
            }
            if ($node->isDescendantOf($newParentId)) {
                return $this->errorResponse($request, 'Tidak dapat memindahkan posisi ke bawah bawahannya sendiri (loop hirarki terdeteksi).', 422);
            }
        }

        // Hapus foto lama jika diminta
        if ($request->boolean('hapus_foto') && $node->foto) {
            Storage::disk('public')->delete($node->foto);
            $node->foto = null;
        }

        // Upload foto baru
        if ($request->hasFile('foto')) {
            if ($node->foto) {
                Storage::disk('public')->delete($node->foto);
            }
            $node->foto = $request->file('foto')->store('struktur-organisasi', 'public');
        }

        $statusKerja = $validated['status_kerja'] ?? $node->status_kerja ?? 'tetap';
        $garisPutus = $request->has('garis_putus')
            ? $request->boolean('garis_putus')
            : in_array($statusKerja, ['magang', 'probation']);

        $node->nama         = $validated['nama'];
        $node->jabatan      = $validated['jabatan'];
        $node->divisi       = $validated['divisi'];
        $node->status_kerja = $statusKerja;
        $node->garis_putus  = $garisPutus;
        $node->parent_id    = $newParentId;
        $node->email        = $validated['email'] ?? null;
        $node->no_telepon   = $validated['no_telepon'] ?? null;
        $node->keterangan   = $validated['keterangan'] ?? null;

        if (array_key_exists('urutan', $validated) && !empty($validated['urutan'])) {
            $node->urutan = (int)$validated['urutan'];
        }
        if (array_key_exists('id_karyawan', $validated)) {
            $node->id_karyawan = $validated['id_karyawan'];
        }

        $node->calculateLevel();
        $node->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data posisi '{$node->nama}' berhasil diperbarui.",
                'data'    => $node,
            ]);
        }

        return back()->with('success', "Data posisi '{$node->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus posisi/karyawan dari struktur organisasi (CRUD: Delete).
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeManagement();

        $node = StrukturOrganisasi::with('children')->findOrFail($id);
        $namaNode = $node->nama;

        DB::beginTransaction();
        try {
            // Pindahkan anak/bawahan ke parent di atasnya agar tidak terputus
            if ($node->children->count() > 0) {
                foreach ($node->children as $child) {
                    $child->parent_id = $node->parent_id;
                    $child->calculateLevel();
                    $child->save();
                }
            }

            if ($node->foto) {
                Storage::disk('public')->delete($node->foto);
            }

            $node->delete();
            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Posisi '{$namaNode}' berhasil dihapus dari struktur.",
                ]);
            }

            return back()->with('success', "Posisi '{$namaNode}' berhasil dihapus dari struktur.");
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->errorResponse($request, 'Gagal menghapus data posisi: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Helper untuk otorisasi akses edit struktur organisasi.
     */
    protected function authorizeManagement(): void
    {
        $role = strtolower(Auth::user()?->role?->nama_role ?? '');

        // Direktur/Direksi are read-only monitors, so they cannot manage the
        // organization chart even though they can view it.
        if (!in_array($role, ['hrd', 'superadmin'], true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola Struktur Organisasi.');
        }
    }

    /**
     * Helper format response error JSON vs HTTP Redirect.
     */
    protected function errorResponse(Request $request, string $message, int $status = 400)
    {
        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()->withErrors(['error' => $message])->withInput();
    }
}

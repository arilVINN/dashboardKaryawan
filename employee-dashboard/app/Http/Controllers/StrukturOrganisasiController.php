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
     * Tampilkan halaman Bagan Struktur Organisasi yang terintegrasi penuh dengan Divisi & Karyawan.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user?->role?->nama_role ?? 'staff');
        $canManage = in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true);

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

        // 1. Ambil seluruh divisi aktif dan karyawan aktif dari master database
        $allDivisi = Divisi::aktif()
            ->orderBy('urutan', 'asc')
            ->orderBy('nama_divisi', 'asc')
            ->get();

        $allKaryawan = Karyawan::with(['divisi', 'jabatanRef', 'user.role', 'atasan'])
            ->whereNull('tanggal_keluar')
            ->where(function ($q) {
                $q->where('status_aktif', 'Aktif')->orWhereNull('status_aktif');
            })
            ->orderBy('nama', 'asc')
            ->get();

        // Fallback node pimpinan non-karyawan (jika Pengawas / Dir Ops belum terdaftar di tabel karyawans)
        $strukturNodes = StrukturOrganisasi::orderBy('level', 'asc')
            ->orderBy('urutan', 'asc')
            ->get();

        // 2. Tentukan Pimpinan Utama (Pengawas, Direktur Utama, Direktur Operasional)
        $pengawasKaryawan = $allKaryawan->first(fn(Karyawan $k) => str_contains(strtolower((string)$k->jabatan), 'pengawas'));
        $pengawasStruktur = $strukturNodes->first(fn($n) => str_contains(strtolower((string)$n->jabatan), 'pengawas'));
        $pengawas = $pengawasKaryawan
            ? $this->formatKaryawanNode($pengawasKaryawan)
            : ($pengawasStruktur ? $this->formatStrukturNode($pengawasStruktur) : null);

        $dirUtamaKaryawan = $allKaryawan->first(fn(Karyawan $k) => $k->isDirekturUtama() || $k->divisi?->isDirekturUtama());
        $dirUtamaStruktur = $strukturNodes->first(fn($n) => str_contains(strtolower((string)$n->jabatan), 'direktur utama'));
        $dirUtama = $dirUtamaKaryawan
            ? $this->formatKaryawanNode($dirUtamaKaryawan)
            : ($dirUtamaStruktur ? $this->formatStrukturNode($dirUtamaStruktur) : null);

        $dirOpsKaryawan = $allKaryawan->first(fn(Karyawan $k) => str_contains(strtolower((string)$k->jabatan), 'direktur operasional'));
        $dirOpsStruktur = $strukturNodes->first(fn($n) => str_contains(strtolower((string)$n->jabatan), 'direktur operasional'));
        $dirOps = $dirOpsKaryawan
            ? $this->formatKaryawanNode($dirOpsKaryawan)
            : ($dirOpsStruktur ? $this->formatStrukturNode($dirOpsStruktur) : null);

        $execKaryawanIds = array_filter([
            $pengawasKaryawan?->id_karyawan,
            $dirUtamaKaryawan?->id_karyawan,
            $dirOpsKaryawan?->id_karyawan,
        ]);

        // 3. Kelompokkan Divisi Aktif (tipe = divisi) & isi anggotanya dari tabel karyawans
        $unitDivisions = $allDivisi->where('tipe', Divisi::TIPE_DIVISI)->values();

        $dirutDivisions = collect();
        $diropsDivisions = collect();
        $operasionalDivisions = collect();

        foreach ($unitDivisions as $divisi) {
            $members = $allKaryawan
                ->filter(function (Karyawan $k) use ($divisi, $execKaryawanIds) {
                    if (in_array($k->id_karyawan, $execKaryawanIds, true)) {
                        return false;
                    }
                    // Abaikan akun sistem Superadmin agar tidak mengotori bagan
                    if (strtolower((string)$k->job_level) === 'superadmin' || strtolower((string)$k->jabatan) === 'superadmin') {
                        return false;
                    }
                    return $k->divisi_id_divisi === $divisi->id_divisi;
                })
                ->sortBy(function (Karyawan $k) {
                    $jab = strtolower((string)$k->jabatanLabel());
                    $isHead = $k->isKadiv()
                        || str_contains($jab, 'kepala')
                        || str_contains($jab, 'koord')
                        || str_contains($jab, 'manager');
                    return ($isHead ? '0_' : '1_') . strtolower($k->nama);
                })
                ->map(fn(Karyawan $k) => $this->formatKaryawanNode($k, $divisi))
                ->values();

            $divBox = (object) [
                'id_divisi'    => $divisi->id_divisi,
                'kode_divisi'  => $divisi->kode_divisi,
                'nama_divisi'  => $divisi->nama_divisi,
                'posisi_bagan' => $divisi->posisi_bagan ?: Divisi::POSISI_OPERASIONAL,
                'urutan'       => (int) $divisi->urutan,
                'members'      => $members,
            ];

            if ($divBox->posisi_bagan === Divisi::POSISI_DIRUT_PENDUKUNG) {
                $dirutDivisions->push($divBox);
            } elseif ($divBox->posisi_bagan === Divisi::POSISI_DIROPS_PENDUKUNG) {
                $diropsDivisions->push($divBox);
            } else {
                $operasionalDivisions->push($divBox);
            }
        }

        $daftarDivisiObj = $unitDivisions;
        $daftarDivisi = $unitDivisions->pluck('nama_divisi')->values();
        $daftarKaryawan = $allKaryawan->filter(
            fn(Karyawan $k) => strtolower((string)$k->job_level) !== 'superadmin'
        )->values();

        return view('hrd.strukturOrganisasi', compact(
            'role',
            'canManage',
            'sidebar',
            'dashboardUrl',
            'pengawas',
            'dirUtama',
            'dirOps',
            'dirutDivisions',
            'diropsDivisions',
            'operasionalDivisions',
            'daftarDivisiObj',
            'daftarDivisi',
            'daftarKaryawan'
        ));
    }

    /**
     * Format data Karyawan menjadi objek seragam untuk kartu bagan.
     */
    protected function formatKaryawanNode(Karyawan $k, ?Divisi $divisi = null): object
    {
        $divModel = $divisi ?? $k->divisi;
        $statusRaw = strtolower(trim((string) ($k->status_karyawan ?? 'tetap')));
        $jobLevelRaw = strtolower(trim((string) ($k->job_level ?? '')));

        $statusKerja = match (true) {
            str_contains($statusRaw, 'magang') || $jobLevelRaw === 'magang' => 'magang',
            str_contains($statusRaw, 'probation') || str_contains($statusRaw, 'percobaan') => 'probation',
            str_contains($statusRaw, 'kontrak') => 'kontrak',
            default => 'tetap',
        };

        $jabatan = $k->jabatanLabel();
        $jabLower = strtolower($jabatan);
        $isKadiv = $k->isKadiv()
            || str_contains($jabLower, 'kepala')
            || str_contains($jabLower, 'koord')
            || str_contains($jabLower, 'manager');

        return (object) [
            'id'           => $k->id_karyawan,
            'node_key'     => 'kar_' . $k->id_karyawan,
            'source_type'  => 'karyawan',
            'id_karyawan'  => $k->id_karyawan,
            'nama'         => $k->nama,
            'jabatan'      => $jabatan,
            'divisi_id'    => $divModel?->id_divisi ?? $k->divisi_id_divisi,
            'divisi'       => $divModel?->nama_divisi ?? '-',
            'status_kerja' => $statusKerja,
            'garis_putus'  => in_array($statusKerja, ['magang', 'probation'], true),
            'is_kadiv'     => $isKadiv,
            'email'        => filled(trim((string) $k->email)) ? $k->email : '-',
            'no_telepon'   => filled(trim((string) $k->no_telepon)) ? $k->no_telepon : '-',
            'keterangan'   => $k->atasan ? 'Atasan: ' . $k->atasan->nama : ($divModel?->nama_divisi ?? '-'),
            'foto'         => $k->foto ? asset('storage/' . $k->foto) : '',
            'atasan_id'    => $k->atasan_id,
        ];
    }

    /**
     * Format node fallback dari tabel struktur_organisasis (misal Pengawas / Dir. Operasional).
     */
    protected function formatStrukturNode(StrukturOrganisasi $n): object
    {
        $statusKerja = strtolower((string) ($n->status_kerja ?? 'tetap'));
        $fotoUrl = $n->foto
            ? asset('storage/' . $n->foto)
            : ($n->karyawan?->foto ? asset('storage/' . $n->karyawan->foto) : '');

        return (object) [
            'id'           => $n->id,
            'node_key'     => 'str_' . $n->id,
            'source_type'  => 'struktur',
            'id_karyawan'  => $n->id_karyawan,
            'nama'         => $n->nama,
            'jabatan'      => $n->jabatan,
            'divisi_id'    => null,
            'divisi'       => $n->divisi ?? '-',
            'status_kerja' => $statusKerja,
            'garis_putus'  => (bool) ($n->garis_putus || in_array($statusKerja, ['magang', 'probation'], true)),
            'is_kadiv'     => false,
            'email'        => $n->email ?? '-',
            'no_telepon'   => $n->no_telepon ?? '-',
            'keterangan'   => $n->keterangan ?? '-',
            'foto'         => $fotoUrl,
            'atasan_id'    => $n->parent_id,
        ];
    }

    /**
     * Atur posisi divisi di bagan (di bawah Dirut, di bawah Dir Ops, atau Operasional) & urutannya.
     */
    public function updateDivisiLayout(Request $request): JsonResponse
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'id_divisi'    => 'required|string|exists:divisis,id_divisi',
            'posisi_bagan' => 'required|string|in:dirut_pendukung,dirops_pendukung,operasional',
            'urutan'       => 'nullable|integer|min:1|max:999',
        ]);

        $divisi = Divisi::where('id_divisi', $validated['id_divisi'])->firstOrFail();
        $divisi->posisi_bagan = $validated['posisi_bagan'];

        if (! empty($validated['urutan'])) {
            $divisi->urutan = (int) $validated['urutan'];
        }

        // Sinkronkan parent_id agar tetap konsisten dengan hierarki Divisi
        if ($validated['posisi_bagan'] === Divisi::POSISI_DIRUT_PENDUKUNG) {
            $dirutId = Divisi::where('tipe', Divisi::TIPE_DIREKTUR)->value('id_divisi');
            if ($dirutId) {
                $divisi->parent_id = $dirutId;
            }
        } else {
            $mgtId = Divisi::where('tipe', Divisi::TIPE_MANAJEMEN)->value('id_divisi');
            if ($mgtId) {
                $divisi->parent_id = $mgtId;
            }
        }

        $divisi->save();

        return response()->json([
            'success' => true,
            'message' => "Posisi divisi '{$divisi->nama_divisi}' berhasil diperbarui.",
            'data'    => $divisi,
        ]);
    }

    /**
     * Perbarui data karyawan langsung ke tabel karyawans dari halaman Struktur Organisasi.
     */
    public function updateKaryawan(Request $request, string $id): JsonResponse
    {
        $this->authorizeManagement();

        $karyawan = Karyawan::findOrFail($id);

        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'jabatan'          => 'required|string|max:255',
            'divisi_id_divisi' => 'required|string|exists:divisis,id_divisi',
            'status_karyawan'  => 'nullable|string|in:tetap,kontrak,probation,magang',
            'atasan_id'        => 'nullable|string|exists:karyawans,id_karyawan',
            'email'            => 'nullable|email|max:255',
            'no_telepon'       => 'nullable|string|max:50',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        if (! empty($validated['atasan_id']) && $validated['atasan_id'] === $karyawan->id_karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak dapat menjadi atasan bagi dirinya sendiri.',
            ], 422);
        }

        if ($request->hasFile('foto')) {
            if ($karyawan->foto) {
                Storage::disk('public')->delete($karyawan->foto);
            }
            $karyawan->foto = $request->file('foto')->store('karyawan', 'public');
        }

        $statusKerja = $validated['status_karyawan'] ?? $karyawan->status_karyawan ?? 'tetap';
        $jabatanNama = \App\Models\Jabatan::normaliseNama($validated['jabatan']);
        $jabatanModel = \App\Models\Jabatan::firstOrCreate(
            ['division_id' => $validated['divisi_id_divisi'], 'nama' => $jabatanNama],
            ['aktif' => true],
        );

        $karyawan->nama             = $validated['nama'];
        $karyawan->jabatan          = $jabatanModel->nama;
        $karyawan->jabatan_id       = $jabatanModel->id;
        $karyawan->divisi_id_divisi = $validated['divisi_id_divisi'];
        $karyawan->status_karyawan  = $statusKerja;
        $karyawan->atasan_id        = $validated['atasan_id'] ?: null;
        $karyawan->email            = $validated['email'] ?? $karyawan->email;
        $karyawan->no_telepon       = $validated['no_telepon'] ?? $karyawan->no_telepon;

        if ($statusKerja === 'magang') {
            $karyawan->job_level = 'Magang';
        } elseif (strtolower((string) $karyawan->job_level) === 'magang') {
            $karyawan->job_level = 'Staff';
        }

        $karyawan->save();

        return response()->json([
            'success' => true,
            'message' => "Data '{$karyawan->nama}' berhasil diperbarui dan disinkronkan.",
            'data'    => $karyawan,
        ]);
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
        if (!in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true)) {
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

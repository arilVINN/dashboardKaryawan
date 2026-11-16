<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\KeuanganAldiv;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Manajemen Alokasi Pagu Anggaran per Divisi — hanya untuk Kadiv Finance.
 */
class AlokasiController extends Controller
{
    /**
     * Daftar alokasi semua divisi, dikelompokkan.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $periode = $request->input('periode', now()->format('Y-m'));

        $divisiList = Divisi::where('kode_divisi', '!=', 'FIN')
            ->where('status_aktif', 'Aktif')
            ->get();

        $alokasi = KeuanganAldiv::with('divisi', 'createdBy')
            ->where('periode', $periode)
            ->latest()
            ->paginate(15);

        $totalAlokasi = KeuanganAldiv::where('periode', $periode)->sum('nominal_alokasi');

        return view('finance.alokasi.index', compact('divisiList', 'periode', 'alokasi', 'totalAlokasi'));
    }

    /**
     * Simpan alokasi pagu baru untuk suatu divisi & periode.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'periode'          => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'nominal_alokasi'  => ['required', 'numeric', 'min:1'],
            'keterangan'       => ['nullable', 'string', 'max:250'],
            'divisi_id_divisi' => [
                'required',
                'string',
                'exists:divisis,id_divisi',
                Rule::unique('keuangan_aldiv')->where(fn ($q) =>
                    $q->where('divisi_id_divisi', $request->divisi_id_divisi)
                      ->where('periode', $request->periode)
                ),
            ],
        ], [
            'divisi_id_divisi.unique' => 'Alokasi untuk divisi dan periode ini sudah ada. Gunakan tombol Edit untuk memperbaruinya.',
        ]);

        $count          = KeuanganAldiv::count();
        $data['id_kad'] = 'KAD' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        $data['user_id_user'] = Auth::id();

        KeuanganAldiv::create($data);

        return back()->with('success', 'Alokasi pagu anggaran berhasil disimpan.');
    }

    /**
     * Perbarui nominal alokasi yang sudah ada.
     */
    public function update(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $alokasi = KeuanganAldiv::findOrFail($id);

        $data = $request->validate([
            'nominal_alokasi' => ['required', 'numeric', 'min:1'],
            'keterangan'      => ['nullable', 'string', 'max:250'],
        ]);

        $alokasi->update($data);

        return back()->with('success', 'Alokasi pagu berhasil diperbarui.');
    }
}

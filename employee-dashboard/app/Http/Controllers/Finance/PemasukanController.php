<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\KPemasukan;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manajemen Pemasukan Kas — hanya untuk Kadiv Finance.
 */
class PemasukanController extends Controller
{
    /**
     * Daftar pemasukan, dapat difilter berdasarkan periode.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $periode  = $request->input('periode', now()->format('Y-m'));
        $kategori = $request->input('kategori', '');
        [$tahun, $bulan] = explode('-', $periode);

        $query = KPemasukan::with('divisi', 'createdBy')
            ->whereYear('tanggal_masuk', $tahun)
            ->whereMonth('tanggal_masuk', $bulan);

        if ($kategori) {
            $query->where('kategori_pemasukan', $kategori);
        }

        $pemasukan    = $query->latest('tanggal_masuk')->paginate(15);
        $totalPeriode = $query->sum('nominal');

        $divisiList   = Divisi::where('kode_divisi', '!=', 'FIN')
            ->where('status_aktif', 'Aktif')
            ->get();
        $kategoriList = KPemasukan::KATEGORI_OPTIONS;

        return view('finance.pemasukan.index', compact(
            'pemasukan',
            'periode',
            'kategori',
            'totalPeriode',
            'divisiList',
            'kategoriList',
        ));
    }

    /**
     * Simpan data pemasukan baru.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'kategori_pemasukan' => ['required', 'string', 'in:' . implode(',', KPemasukan::KATEGORI_OPTIONS)],
            'nominal'            => ['required', 'numeric', 'min:1'],
            'tanggal_masuk'      => ['required', 'date'],
            'keterangan'         => ['nullable', 'string', 'max:500'],
            'bukti_dokumen'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'divisi_id_divisi'   => ['nullable', 'string', 'exists:divisis,id_divisi'],
        ]);

        if ($request->hasFile('bukti_dokumen')) {
            $data['bukti_dokumen'] = $request->file('bukti_dokumen')
                ->store('keuangan/pemasukan', 'public');
        }

        $count = KPemasukan::count();
        $data['id_kp']           = 'KP' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
        $data['kode_transaksi']  = 'IN' . now()->format('ym') . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $data['user_id_user']    = Auth::id();

        KPemasukan::create($data);

        return back()->with('success', 'Pemasukan kas berhasil dicatat.');
    }
}

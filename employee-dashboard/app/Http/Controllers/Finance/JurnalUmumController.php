<?php

namespace App\Http\Controllers\Finance;

use App\Exports\JurnalUmumExport;
use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JurnalUmumController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', JurnalEntry::class);

        return view('finance.jurnalUmum.index');
    }

    public function export(Request $request): BinaryFileResponse
    {
        Gate::authorize('viewAny', JurnalEntry::class);

        $validated = $request->validate([
            'periode' => ['sometimes', 'required', 'date_format:Y-m'],
            'divisi_id' => ['sometimes', 'nullable', 'integer', 'exists:jurnal_divisis,id'],
        ]);

        $periode = $validated['periode'] ?? now()->format('Y-m');
        $divisiId = isset($validated['divisi_id']) ? (int) $validated['divisi_id'] : null;

        return Excel::download(
            new JurnalUmumExport($periode, $divisiId),
            'Jurnal_Umum_' . $periode . '.xlsx',
        );
    }
}

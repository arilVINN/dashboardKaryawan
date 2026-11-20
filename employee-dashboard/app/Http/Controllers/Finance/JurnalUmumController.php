<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JurnalUmumController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', JurnalEntry::class);

        return view('finance.jurnalUmum.index');
    }

    /** Tautan "Daftar akun (COA)" di sidebar: buka panel sekali, URL tetap bersih. */
    public function coaPanel(): RedirectResponse
    {
        Gate::authorize('viewAny', JurnalEntry::class);
        session()->flash('open_coa_panel', true);

        return redirect()->route('kadiv.keuangan.jurnalUmum');
    }
}

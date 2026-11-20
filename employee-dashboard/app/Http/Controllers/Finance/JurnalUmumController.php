<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JurnalUmumController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', JurnalEntry::class);

        return view('finance.jurnalUmum.index');
    }
}

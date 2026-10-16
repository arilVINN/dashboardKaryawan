<?php

namespace App\Http\Controllers\Direksi;

use App\Http\Controllers\Controller;
use App\Models\Tugas;

class DireksiTaskController extends Controller
{
    /**
     * Company-wide task monitor (read-only).
     */
    public function index()
    {
        return view('direksi.tugas');
    }

    /**
     * Read-only task detail for the monitor. Unlike the Kadiv detail page this
     * is rendered server-side and exposes no mutation controls.
     */
    public function show(string $id)
    {
        $task = Tugas::with([
            'karyawan.divisi',
            'submitTugas' => fn ($query) => $query->orderByDesc('created_at'),
        ])->whereKey($id)->firstOrFail();

        $submissions = $task->submitTugas;
        $latestSubmission = $submissions->first();

        return view('direksi.detailTugas', compact('task', 'submissions', 'latestSubmission'));
    }
}

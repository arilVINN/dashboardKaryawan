<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\User2;
use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrdSignatureController extends Controller
{
    public function __construct(private SignatureService $signatures)
    {
    }

    public function upload(Request $request)
    {
        $employee = $this->hrdEmployee();

        $request->validate([
            'tanda_tangan' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg',
                'max:5120',
                'required_without:tanda_tangan_data',
            ],
            'tanda_tangan_data' => [
                'nullable',
                'string',
                'max:8000000',
                'required_without:tanda_tangan',
                'regex:/^data:image\/png;base64,[A-Za-z0-9+\/=\r\n]+$/',
            ],
        ]);

        try {
            if ($request->hasFile('tanda_tangan')) {
                $this->signatures->store($request->file('tanda_tangan'), $employee);
            } else {
                $this->signatures->storeDataUri(
                    (string) $request->input('tanda_tangan_data'),
                    $employee
                );
            }
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'tanda_tangan' => 'Tanda tangan tidak valid. Gunakan PNG/JPG yang jelas atau gambar ulang tanda tangan.',
            ]);
        }

        return back()->with('success', 'Tanda tangan HR berhasil diunggah.');
    }

    public function show(): StreamedResponse
    {
        $employee = $this->hrdEmployee();
        $path = $this->signatures->pathFor($employee);

        abort_unless($path !== null, 404);

        return Storage::disk('private')->response($path, 'tanda-tangan-hr.png', [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    private function hrdEmployee()
    {
        $user = auth()->user();

        abort_unless(
            $user instanceof User2 && $user->hasRole('hrd') && $user->karyawan,
            403
        );

        return $user->karyawan;
    }
}

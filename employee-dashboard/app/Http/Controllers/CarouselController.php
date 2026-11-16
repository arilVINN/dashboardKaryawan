<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarouselController extends Controller
{
    /**
     * Simpan konten carousel baru.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $user?->loadMissing(['role', 'karyawan']);
        $role = strtolower($user?->role?->nama_role ?? '');

        abort_unless(in_array($role, ['hrd', 'kadiv'], true), 403);

        $divisiId = $user->karyawan?->divisi_id_divisi;
        abort_if($role === 'kadiv' && ! $divisiId, 403);

        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'title'    => 'required|string|max:255',
            'date'     => 'required|date',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('carousels', 'public');
        }

        Carousel::create([
            'title'            => $validated['title'],
            'category'         => $validated['category'],
            'date'             => $validated['date'],
            'image'            => $path,
            'user_id_user'     => $user?->id_user,
            'divisi_id_divisi' => $divisiId,
        ]);

        return back()->with('success', 'Konten kegiatan/informasi berhasil ditambahkan.');
    }

    /**
     * Hapus konten carousel.
     */
    public function destroy($id)
    {
        $carousel = Carousel::findOrFail($id);
        $user = request()->user();
        $user?->loadMissing(['role', 'karyawan']);
        $role = strtolower($user?->role?->nama_role ?? '');

        abort_unless(in_array($role, ['hrd', 'kadiv'], true), 403);
        if (
            $role === 'kadiv'
            && (
                $carousel->divisi_id_divisi !== $user->karyawan?->divisi_id_divisi
                || $carousel->user_id_user !== $user->id_user
            )
        ) {
            return back()->with('error', 'Anda tidak dapat menghapus konten yang dibuat oleh orang lain.');
        }

        if ($carousel->image && Storage::disk('public')->exists($carousel->image)) {
            Storage::disk('public')->delete($carousel->image);
        }

        $carousel->delete();

        return back()->with('success', 'Konten berhasil dihapus.');
    }
}

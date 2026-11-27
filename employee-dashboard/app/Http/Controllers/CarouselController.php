<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use App\Models\CarouselLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarouselController extends Controller
{
    /**
     * Tampilkan halaman detail konten kegiatan / informasi carousel.
     */
    public function show($id)
    {
        $carousel = Carousel::with(['user.karyawan.divisi', 'divisi'])->findOrFail($id);

        $user = auth()->user() ?? request()->user();
        $userRole = strtolower($user?->role?->nama_role ?? 'staff');

        $dashboardUrl = match ($userRole) {
            'hrd' => route('hrd.dashboard'),
            'kadiv' => route('kadiv.dashboard'),
            'direktur', 'direksi', 'superadmin' => route('direktur.dashboard'),
            default => route('dashboard'),
        };

        $isLiked = $carousel->isLikedBy($user);
        $totalLikes = $carousel->total_likes;
        $creatorName = $carousel->user?->karyawan?->nama ?? 'Pengguna tidak diketahui';
        $divisionName = $carousel->divisi?->nama_divisi ?? $carousel->user?->karyawan?->divisi?->nama_divisi ?? 'Finance & Accounting';

        $subtitle = $carousel->subtitle ?: 'Bersama membangun kolaborasi yang lebih baik';
        $description = $carousel->description ?: "Kegiatan {$carousel->title} menjadi ruang bagi tim PT Sinergi Ilmiah Indonesia untuk berbagi informasi, menyelaraskan prioritas, dan memperkuat kerja sama antar divisi.\n\nTerima kasih atas partisipasi seluruh tim. Mari terus menjaga semangat kolaborasi dan bersama-sama mendukung kemajuan perusahaan.";

        return view('carousel.detailKonten', compact(
            'carousel',
            'isLiked',
            'totalLikes',
            'creatorName',
            'divisionName',
            'dashboardUrl',
            'userRole',
            'subtitle',
            'description'
        ));
    }

    /**
     * Toggle status like (suka / batal suka) pada konten carousel.
     */
    public function toggleLike(Request $request, $id)
    {
        $carousel = Carousel::findOrFail($id);
        $user = $request->user();

        if (! $user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
            }

            return redirect()->route('login');
        }

        $existingLike = CarouselLike::where('carousel_id', $carousel->id)
            ->where('user_id_user', $user->id_user)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
            $message = 'Batal menyukai konten ini.';
        } else {
            CarouselLike::create([
                'carousel_id' => $carousel->id,
                'user_id_user' => $user->id_user,
            ]);
            $liked = true;
            $message = 'Terima kasih telah menyukai konten ini!';
        }

        $carousel->refresh();
        $totalLikes = $carousel->total_likes;

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'total_likes' => $totalLikes,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Simpan konten carousel baru.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $user?->loadMissing(['role', 'karyawan']);
        $role = strtolower($user?->role?->nama_role ?? '');

        abort_unless(in_array($role, ['hrd', 'kadiv', 'superadmin'], true), 403);

        $divisiId = $user->karyawan?->divisi_id_divisi;
        abort_if($role === 'kadiv' && ! $divisiId, 403);

        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('carousels', 'public');
        }

        Carousel::create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'date' => $validated['date'],
            'image' => $path,
            'user_id_user' => $user?->id_user,
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

        abort_unless(in_array($role, ['hrd', 'kadiv', 'superadmin'], true), 403);
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

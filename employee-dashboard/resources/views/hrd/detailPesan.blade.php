@php
    $viewer = auth()->user();
    $viewerKaryawan = $viewer?->karyawan;
    $tipe = strtolower((string) ($pesan->tipe ?? 'pesan'));
    $typeLabel = $tipe === 'surat' ? 'Surat izin' : 'Pesan';

    $nama = fn ($pesanItem) => $pesanItem->pengirim?->karyawan?->nama
        ?? $pesanItem->pengirim?->username
        ?? 'Pengguna tidak tersedia';
    $foto = fn ($pesanItem) => $pesanItem->pengirim?->karyawan?->foto
        ? '/storage/'.$pesanItem->pengirim->karyawan->foto
        : null;
    $waktu = fn ($pesanItem) => \Carbon\Carbon::parse($pesanItem->created_at ?? $pesanItem->tanggal_pesan)
        ->translatedFormat('j M Y').', '.(\Carbon\Carbon::parse($pesanItem->created_at ?? $pesanItem->tanggal_pesan)->format('H:i'));

    $threadItems = $thread->map(fn ($item) => [
        'nama' => $nama($item),
        'foto_url' => $foto($item),
        'initials' => mb_strtoupper(mb_substr($nama($item), 0, 1)),
        'time_label' => $waktu($item),
        'body' => $item->deskripsi,
        'is_mine' => $item->pengirim_id_user === $viewer?->id_user,
    ])->values()->all();

    $meta = $nama($pesan).' → '.($pesan->penerima?->karyawan?->nama ?? $pesan->penerima?->username ?? 'Pengguna tidak tersedia')
        .' · '.\Carbon\Carbon::parse($pesan->tanggal_pesan ?? $pesan->created_at)->translatedFormat('j M Y');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pesan->judul_pesan }} - Pesan HRD</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto px-6 pb-8 pt-6 sm:px-8">
            @include('component.breadcrumbs', [
                'parentText' => 'Daftar Pesan',
                'parentUrl' => url('/hrd/pesan'),
                'currentPage' => $pesan->judul_pesan,
                'inline' => true,
            ])

            <x-message-conversation
                :topic="$pesan->judul_pesan"
                :type-label="$typeLabel"
                :meta="$meta"
                :thread="$threadItems"
                :can-reply="$canReply"
                :reply-endpoint="route('hrd.detailPesan.balas', $pesan->id_pesan)"
                :message-id="$pesan->id_pesan"
                :list-url="url('/hrd/pesan')"
                :csrf="csrf_token()"
                :current-user-id="$viewer?->id_user"
                :current-name="$viewerKaryawan?->nama ?? $viewer?->username"
                :current-foto="$viewerKaryawan?->foto ? '/storage/'.$viewerKaryawan->foto : null"
            />
        </main>
    </div>
</body>
</html>

@php
    $viewer = auth()->user();
    $viewerKaryawan = $viewer?->karyawan;
    $data = $data ?? null;
    $rootData = $data['pesan'] ?? $data ?? [];
    $tipe = strtolower((string) ($rootData['tipe'] ?? 'pesan'));
    $typeLabel = $tipe === 'surat' ? 'Surat izin' : 'Pesan';

    $threadRaw = array_merge([$rootData], $data['balasan'] ?? []);
    $threadItems = array_map(function ($item) use ($viewer) {
        $person = $item['pengirim'] ?? [];
        $nama = $person['nama'] ?? $person['username'] ?? 'Pengguna tidak tersedia';
        $timestamp = $item['created_at'] ?? ($item['tanggal_pesan'] ?? null);
        $date = $timestamp ? \Carbon\Carbon::parse($timestamp) : now();

        return [
            'nama' => $nama,
            'foto_url' => $person['foto_url'] ?? null,
            'initials' => mb_strtoupper(mb_substr($nama, 0, 1)),
            'time_label' => $date->translatedFormat('j M Y').', '.$date->format('H:i'),
            'body' => $item['deskripsi'] ?? '',
            'is_mine' => ($person['id_user'] ?? null) === $viewer?->id_user,
        ];
    }, $threadRaw);

    $pengirimNama = $rootData['pengirim']['nama'] ?? $rootData['pengirim']['username'] ?? 'Pengguna tidak tersedia';
    $penerimaNama = $rootData['penerima']['nama'] ?? $rootData['penerima']['username'] ?? 'Pengguna tidak tersedia';
    $tanggal = $rootData['tanggal_pesan'] ?? ($rootData['created_at'] ?? null);
    $meta = $pengirimNama.' → '.$penerimaNama.' · '.($tanggal ? \Carbon\Carbon::parse($tanggal)->translatedFormat('j M Y') : '');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto">
            <x-message-conversation
                :topic="$rootData['judul_pesan'] ?? 'Pesan'"
                :type-label="$typeLabel"
                :meta="$meta"
                :thread="$threadItems"
                :can-reply="(bool) ($data['can_reply'] ?? false)"
                :reply-endpoint="'/api/staff/pesan/'.($id ?? '').'/balas'"
                :message-id="$id ?? ''"
                :list-url="url('/pesan')"
                :token="$token ?? null"
                :current-user-id="$viewer?->id_user"
                :current-name="$viewerKaryawan?->nama ?? $viewer?->username"
                :current-foto="$viewerKaryawan?->foto ? '/storage/'.$viewerKaryawan->foto : null"
            />
        </main>
    </div>
</body>
</html>

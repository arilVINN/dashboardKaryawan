<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Booking - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            @include('component.breadcrumbs', [
                'parentText' => 'Pesan',
                'parentUrl' => route('hrd.pesan'),
                'currentPage' => 'Request Booking',
                'inline' => true,
            ])
            <x-page-heading title="Request Booking" icon="mail" class="mb-6" />

            @if (session('success'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-[#EAF4F8] text-xs text-[#334155]">
                            <tr>
                                <th class="px-5 py-4 font-medium">Pemohon</th>
                                <th class="px-5 py-4 font-medium">Kategori</th>
                                <th class="px-5 py-4 font-medium">Jadwal</th>
                                <th class="px-5 py-4 font-medium">Keperluan</th>
                                <th class="px-5 py-4 font-medium">Status</th>
                                <th class="px-5 py-4 text-center font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($bookings as $booking)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-800">
                                        {{ $booking->pemohon?->karyawan?->nama ?? $booking->pemohon?->username ?? 'Pemohon tidak tersedia' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        {{ $booking->kategori }}: {{ $booking->jenis }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        {{ $booking->tanggal->format('d M Y') }},
                                        {{ substr($booking->jam_mulai, 0, 5) }}–{{ substr($booking->jam_selesai, 0, 5) }}
                                    </td>
                                    <td class="max-w-xs px-5 py-4">{{ $booking->keperluan }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ $booking->status === 'ACC' ? 'bg-emerald-100 text-emerald-700' : ($booking->status === 'Tolak' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ $booking->status }}
                                        </span>
                                        @if ($booking->pemroses)
                                            <div class="mt-1 text-xs text-slate-400">
                                                {{ $booking->pemroses->karyawan?->nama ?? $booking->pemroses->username }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-center">
                                        @if ($booking->status === 'Pending')
                                            <div class="inline-flex items-center gap-2">
                                                <form method="POST" action="{{ route('hrd.suratBooking.acc', $booking) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">ACC</button>
                                                </form>
                                                <form method="POST" action="{{ route('hrd.suratBooking.tolak', $booking) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Tolak</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs italic text-slate-400">Sudah diproses</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center italic text-slate-400">Belum ada request booking.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $bookings->links('vendor.pagination.custom-links') }}
                </div>
            </div>
        </main>
    </div>
</body>

</html>

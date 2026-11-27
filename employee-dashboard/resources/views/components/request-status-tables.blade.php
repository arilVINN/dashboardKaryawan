<div class="mt-8 space-y-6">
    <section aria-labelledby="booking-request-status-heading"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 id="booking-request-status-heading" class="text-base font-semibold text-slate-800">Status Booking Kendaraan/Ruangan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-[#EAF4F8] text-xs text-[#334155]">
                    <tr>
                        <th class="px-5 py-3 font-medium">Kategori</th>
                        <th class="px-5 py-3 font-medium">Jadwal</th>
                        <th class="px-5 py-3 font-medium">Keperluan</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Diajukan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($bookingStatus as $booking)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-800">
                                {{ $booking->kategori }}: {{ $booking->jenis }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs">
                                {{ $booking->tanggal?->format('d M Y') ?? '-' }},
                                {{ substr($booking->jam_mulai, 0, 5) }}–{{ substr($booking->jam_selesai, 0, 5) }}
                            </td>
                            <td class="max-w-xs px-5 py-4">{{ $booking->keperluan }}</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <x-status-badge :status="$booking->status" :label="$booking->status" />
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs">
                                {{ $booking->created_at?->format('d M Y') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada booking.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

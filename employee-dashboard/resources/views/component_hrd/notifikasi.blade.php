@php
    $pesan = collect($pesan ?? []);
    $notifikasi = collect($notifikasi ?? []);
    $birthdayNotifications = $notifikasi->filter(
        fn ($notification) => str_contains(strtolower($notification->judul_notifikasi), 'ulang tahun')
    );
@endphp

<div class="flex flex-col gap-2">
    <div class="flex items-center justify-between px-1">
        <h2 class="text-lg font-semibold text-[#172B36]">Notifikasi</h2>
        <a href="{{ route('hrd.pesan') }}" class="text-xs text-[#146C94] hover:underline">
            Lihat semua &rsaquo;
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="divide-y divide-[#EEF2F5]">
            @if ($pesan->isEmpty() && $birthdayNotifications->isEmpty())
                <p class="px-4 py-3 text-[13px] text-[#4B5563]">Belum ada notifikasi</p>
            @endif
            @foreach ($birthdayNotifications as $notification)
                    @php
                        $timeLabel = $notification->created_at
                            ? $notification->created_at->locale('id')->translatedFormat('j M, H:i')
                            : '-';
                    @endphp
                    <a href="{{ route('hrd.dashboard') }}"
                        data-notification-id="{{ $notification->id_notifikasi }}"
                        class="flex items-start gap-3 px-4 py-3 transition hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 12h16v5.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V12Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 14.5c1.3 0 1.3 1.2 2.6 1.2s1.3-1.2 2.6-1.2 1.3 1.2 2.6 1.2 1.3-1.2 2.6-1.2 1.3 1.2 2.6 1.2 1.3-1.2 2.6-1.2 1.3 1.2 2.6 1.2M8 9V5m4 4V3m4 6V5" />
                                <circle cx="8" cy="3.5" r=".8" fill="currentColor" stroke="none" />
                                <circle cx="12" cy="1.8" r=".8" fill="currentColor" stroke="none" />
                                <circle cx="16" cy="3.5" r=".8" fill="currentColor" stroke="none" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-2">
                                <span class="truncate text-[13px] font-medium text-[#172B36]">Ulang tahun karyawan</span>
                                <span class="shrink-0 whitespace-nowrap text-[11px] text-[#4B5563]">{{ $timeLabel }}</span>
                            </span>
                            <span class="mt-0.5 block text-xs leading-[1.45] text-[#4B5563]">{{ $notification->isi_notif }}</span>
                        </span>
                    </a>
            @endforeach
            @foreach ($pesan as $message)
            @php
                $timeValue = $message->created_at ?? $message->tanggal_pesan;
                $timeLabel = $timeValue
                    ? \Illuminate\Support\Carbon::parse($timeValue)->locale('id')->translatedFormat('j M, H:i')
                    : '-';
            @endphp
            <a href="{{ route('hrd.detailPesan', $message->id_pesan) }}"
                data-notification-id="{{ $message->id_pesan }}"
                class="flex items-start gap-3 px-4 py-3 transition hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </span>

                <span class="min-w-0 flex-1">
                    <span class="flex items-center justify-between gap-2">
                        <span class="flex min-w-0 items-center gap-1.5">
                            <span class="truncate text-[13px] font-medium text-[#172B36]">Pesan baru</span>
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#146C94]" data-unread-dot></span>
                        </span>
                        <span class="shrink-0 whitespace-nowrap text-[11px] text-[#4B5563]">{{ $timeLabel }}</span>
                    </span>
                    <span class="mt-0.5 block text-xs leading-[1.45] text-[#4B5563]">
                        {{ $message->judul_pesan ?: ($message->deskripsi ?: '-') }}
                    </span>
                </span>
            </a>
            @endforeach
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let readMessages = new Set();

        try {
            readMessages = new Set(JSON.parse(localStorage.getItem('hrd_read_pesan') || '[]'));
        } catch (error) {
            readMessages = new Set();
        }

        document.querySelectorAll('[data-notification-id]').forEach((row) => {
            const id = row.dataset.notificationId;

            if (readMessages.has(id)) {
                row.querySelector('[data-unread-dot]')?.remove();
            }

            row.addEventListener('click', () => {
                readMessages.add(id);
                localStorage.setItem('hrd_read_pesan', JSON.stringify([...readMessages]));
                row.querySelector('[data-unread-dot]')?.remove();
            });
        });
    });
</script>

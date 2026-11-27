<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesan - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pt-6 pb-6">
            @include('component.breadcrumbs', [
                'parentText' => 'Dashboard',
                'parentUrl' => url('/'),
                'currentPage' => 'Daftar Pesan',
                'inline' => true,
            ])
            @if (session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        window.appToast(@json(session('success')), 'success');
                    });
                </script>
            @endif

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <x-page-heading title="Daftar Pesan" icon="message" />

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="Livewire.dispatch('open-compose-message')"
                        class="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg bg-[#0097B2] px-4 text-sm font-medium text-white transition hover:bg-[#008199]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Buat Pesan
                    </button>

                    <button type="button" data-modal-open="modalBooking"
                        class="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg bg-[#149463] px-4 text-sm font-medium text-white transition hover:bg-[#0f5878]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        Booking
                    </button>
                </div>
            </div>

            <div class="w-full">
                <livewire:staff.pesan-table />
                @include('components.request-status-tables')
                <livewire:staff.compose-message />
                @include('components.modalBooking')
            </div>
        </main>

    </div>

</body>

</html>

@php
    $role = strtolower(auth()->user()?->role?->nama_role ?? 'staff');
    $sidebar = match ($role) {
        'hrd' => 'component_hrd.sidebar',
        'kadiv' => 'component_kadiv.sidebar',
        default => 'component.sidebar',
    };
    $dashboardUrl = match ($role) {
        'hrd' => url('/hrd/dashboard'),
        'kadiv' => url('/kadiv/dashboard'),
        default => url('/'),
    };

    $activeAccount = \App\Models\ContentAccount::resolve(request()->query('account'));
    $pageTitle = $activeAccount ? 'Kalender Konten - '.$activeAccount->name : 'Kalender Konten';
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include($sidebar)

    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component.topbar')

        {{-- MAIN CONTENT --}}
        <main class="flex-1 overflow-y-auto p-8 pt-6 pb-6">
            @include('component.breadcrumbs', [
                'parentText' => 'Dashboard',
                'parentUrl' => $dashboardUrl,
                'currentPage' => $pageTitle,
                'inline' => true,
            ])

            <div class="mb-6 flex items-start gap-3">
                <span class="shrink-0 rounded-lg bg-[#E3F1F7] p-2 text-[#146C94]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"></path>
                        <path d="M12 11.2l.85 1.95 1.95.85-1.95.85L12 16.8l-.85-1.95-1.95-.85 1.95-.85L12 11.2z" fill="currentColor" stroke="none"></path>
                    </svg>
                </span>
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-[#172B36]" data-page-title>{{ $pageTitle }}</h1>
                </div>
            </div>

            <livewire:content-calendar />
        </main>

    </div>

    <script>
        document.addEventListener('content-account-changed', function (event) {
            const name = event.detail && event.detail.name ? event.detail.name : '';
            const title = name ? 'Kalender Konten - ' + name : 'Kalender Konten';

            const heading = document.querySelector('[data-page-title]');
            if (heading) {
                heading.textContent = title;
            }

            const crumb = document.querySelector('[data-breadcrumb-current]');
            if (crumb) {
                crumb.textContent = title;
                crumb.setAttribute('data-breadcrumb-current', title);
            }

            document.title = title + ' - PT Silindo';
        });
    </script>

</body>

</html>

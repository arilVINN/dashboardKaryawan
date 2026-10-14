<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HRD</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        
        @include('component.topbar')
        
        <div class="px-8">
            @include('component_hrd.statusbar')
        </div>
        
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            @include('component_hrd.tabelDivisi')
            <!-- Konten -->
            
        </main>
        
    </div>

</body>

</html>
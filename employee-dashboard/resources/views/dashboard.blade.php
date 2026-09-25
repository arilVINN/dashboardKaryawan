<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 flex h-screen">

    @include('component.sidebar')

    <main class="flex-1 p-8 overflow-y-auto">
        <h1 class="text-2xl font-bold text-gray-800">Selamat Datang di Dashboard</h1>
    </main>

</body>
</html>
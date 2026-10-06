<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="relative min-h-screen bg-gradient-to-b from-[#044564] via-[#0D7C9E] to-[#19A7CE] overflow-hidden flex items-center justify-center">

    <div class="absolute inset-x-0 bottom-0 h-[446px] w-full flex pointer-events-none z-0 overflow-hidden">
        <img src="{{ asset('gambar/bglogin.png') }}" alt="bg1"
            class="w-1/2 h-full object-cover object-bottom shrink-0">

        <img src="{{ asset('gambar/bglogin.png') }}" alt="bg2"
            class="w-1/2 h-full object-cover object-bottom shrink-0 -scale-x-100">
    </div>

    <div
        class="absolute inset-x-0 -bottom-50 h-[446px] w-full bg-gradient-to-t from-white via-white/50 to-transparent pointer-events-none z-[1]">
    </div>

    @include('component.loginForm', [
        'action' => route('login', absolute: false),
        'logo' => asset('gambar/silindo.png'),
        'bgCity' => asset('gambar/bglogin.png'),
    ])

</body>

</html>

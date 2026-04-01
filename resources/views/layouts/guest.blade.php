<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'تسجيل الدخول') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gradient-to-br from-zinc-950 via-black to-zinc-900 text-zinc-100">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="mb-8 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#d4af37]/90">Bavly KYC</p>
            <h1 class="mt-2 text-2xl font-bold text-white">بوابة الامتثال الداخلي</h1>
        </div>

        @include('partials.flash')

        <div class="w-full max-w-md rounded-2xl border border-zinc-800/80 bg-zinc-900/70 p-8 shadow-2xl backdrop-blur">
            @yield('content')
        </div>
    </div>
</body>
</html>

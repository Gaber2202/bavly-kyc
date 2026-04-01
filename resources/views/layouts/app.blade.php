<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام KYC') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-zinc-950 text-zinc-100">
    <div class="flex min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">
        @include('partials.sidebar')

        <div class="flex min-h-screen flex-1 flex-col">
            @include('partials.topbar')

            <main class="flex-1 p-4 md:p-8">
                @include('partials.flash')

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-500/40 bg-red-950/40 p-4 text-sm text-red-200" role="alert">
                        <p class="mb-2 font-semibold">تحقق من الحقول التالية:</p>
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

@php
    $user = auth()->user();
@endphp

<header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/90 px-4 py-3 backdrop-blur">
    <div class="flex items-center gap-3">
        <button type="button" class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-800 md:hidden" @click="sidebarOpen = true" aria-label="فتح القائمة">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-[#d4af37]/80">{{ config('app.name') }}</p>
            <h2 class="text-lg font-semibold text-white">@yield('heading', 'نظام إدارة KYC')</h2>
        </div>
    </div>

    <div class="flex items-center gap-3">
        @if ($user?->isAdmin())
            <a href="{{ route('kyc.create') }}" class="btn-gold text-xs md:text-sm">إضافة KYC</a>
        @else
            <a href="{{ route('kyc.create') }}" class="btn-outline-gold text-xs md:text-sm">إضافة KYC</a>
        @endif

        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-outline-gold text-xs md:text-sm">خروج</button>
        </form>
    </div>
</header>

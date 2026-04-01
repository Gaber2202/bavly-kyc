@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
@endphp

<aside
    class="fixed inset-y-0 right-0 z-40 w-64 transform border-l border-zinc-800 bg-black/95 px-3 py-6 backdrop-blur transition md:static md:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'"
>
    <div class="mb-8 flex items-center justify-between px-2">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-[#d4af37]">KYC</p>
            <p class="text-lg font-bold text-white">لوحة التحكم</p>
        </div>
        <button type="button" class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-800 md:hidden" @click="sidebarOpen = false" aria-label="إغلاق القائمة">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="space-y-1">
        <a href="{{ route('kyc.index') }}" class="sidebar-link {{ request()->routeIs('kyc.*') ? 'sidebar-link-active' : '' }}">
            <span class="h-1.5 w-1.5 rounded-full bg-[#d4af37]/80"></span>
            سجلات KYC
        </a>

        @if ($user?->isAdmin() || $user?->can_view_reports)
            <a href="{{ route('reports.dashboard') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'sidebar-link-active' : '' }}">
                <span class="h-1.5 w-1.5 rounded-full bg-[#d4af37]/80"></span>
                التقارير والتحليلات
            </a>
        @endif

        @if ($user?->isAdmin())
            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : '' }}">
                <span class="h-1.5 w-1.5 rounded-full bg-[#d4af37]/80"></span>
                إدارة المستخدمين
            </a>
        @endif
    </nav>

    <div class="mt-auto border-t border-zinc-800/80 pt-6 text-xs text-zinc-500">
        <p>{{ $user?->name }}</p>
        <p class="mt-1">{{ $user?->role?->label() }}</p>
    </div>
</aside>

<div
    class="fixed inset-0 z-30 bg-black/60 md:hidden"
    x-show="sidebarOpen"
    x-transition.opacity
    x-cloak
    @click="sidebarOpen = false"
></div>

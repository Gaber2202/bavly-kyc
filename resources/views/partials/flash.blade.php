@if (session('toast'))
    <div
        class="mb-6 rounded-xl border px-4 py-3 text-sm shadow-lg"
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 4500)"
        role="status"
        @class([
            'border-emerald-500/40 bg-emerald-950/50 text-emerald-100' => session('toast.type') === 'success',
            'border-red-500/40 bg-red-950/50 text-red-100' => session('toast.type') === 'error',
            'border-amber-500/40 bg-amber-950/50 text-amber-100' => session('toast.type') === 'warning',
            'border-[#d4af37]/40 bg-zinc-900 text-zinc-100' => ! in_array(session('toast.type'), ['success', 'error', 'warning'], true),
        ])
    >
        {{ session('toast.message') }}
    </div>
@endif

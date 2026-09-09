<div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 shadow-sm">
    <div class="flex items-center gap-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
            {{ $icon ?? '◌' }}
        </div>
        <div class="min-w-0">
            <h3 class="text-base font-semibold text-slate-800">{{ $title }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        </div>
    </div>
</div>
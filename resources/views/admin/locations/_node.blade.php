@php
    $canManage = auth()->user()->can('lokasi.kemaskini');
    $canDelete = auth()->user()->can('lokasi.padam');
    $hasChildren = $node->descendants->isNotEmpty();
    $levelBadge = match ($node->level) {
        \App\Enums\LocationLevel::Kampus => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
        \App\Enums\LocationLevel::Bangunan => 'bg-sky-50 text-sky-700 ring-sky-100',
        \App\Enums\LocationLevel::Tingkat => 'bg-violet-50 text-violet-700 ring-violet-100',
        \App\Enums\LocationLevel::Ruang => 'bg-teal-50 text-teal-700 ring-teal-100',
    };
@endphp

<li x-data="{ open: true }"
    @locations-expand.window="open = true"
    @locations-collapse.window="open = false"
    class="relative">
    <div class="group flex flex-wrap items-center gap-x-2 gap-y-1 rounded-lg px-2 py-2 transition hover:bg-slate-50 {{ $node->is_active ? '' : 'opacity-70' }}">
        @if ($hasChildren)
            <button type="button" @click="open = !open"
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                    :aria-expanded="open ? 'true' : 'false'"
                    aria-label="Buka atau tutup cabang {{ $node->name }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"
                     class="h-3.5 w-3.5 transition-transform duration-150" :class="open ? 'rotate-90' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                </svg>
            </button>
        @else
            <span class="h-6 w-6 shrink-0" aria-hidden="true"></span>
        @endif

        <span class="truncate text-sm font-semibold text-slate-900">{{ $node->name }}</span>

        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-slate-600">{{ $node->code }}</span>

        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $levelBadge }}">
            {{ $node->level->label() }}
        </span>

        @unless ($node->is_active)
            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-100">
                Tidak aktif
            </span>
        @endunless

        @if ($hasChildren)
            <span class="text-[11px] text-slate-400" x-show="!open" x-cloak>
                {{ $node->descendants->count() }} sublokasi
            </span>
        @endif

        @if ($canManage)
            <span class="ml-auto flex shrink-0 items-center gap-1 opacity-70 transition group-hover:opacity-100 focus-within:opacity-100">
                <a href="{{ route('admin.locations.edit', $node) }}"
                   class="rounded-md px-2 py-1 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-indigo-600 hover:shadow-sm">
                    Edit
                </a>

                <form method="POST" action="{{ route('admin.locations.toggle', $node) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="rounded-md px-2 py-1 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-slate-900 hover:shadow-sm">
                        {{ $node->is_active ? 'Nyahaktif' : 'Aktifkan' }}
                    </button>
                </form>

                @if ($canDelete)
                    <form method="POST" action="{{ route('admin.locations.destroy', $node) }}"
                          onsubmit="return confirm('Padam {{ $node->name }} secara kekal?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="rounded-md px-2 py-1 text-xs font-semibold text-slate-500 transition hover:bg-rose-50 hover:text-rose-600">
                            Padam
                        </button>
                    </form>
                @endif
            </span>
        @endif
    </div>

    @if ($hasChildren)
        <ul x-show="open" x-cloak class="ml-5 border-l border-slate-200 pl-3">
            @foreach ($node->descendants->sortBy('name') as $child)
                @include('admin.locations._node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>

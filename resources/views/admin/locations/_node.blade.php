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

    // Each action carries the colour of its consequence: indigo edits,
    // amber withdraws a location from use, emerald returns it, rose destroys.
    $actionBase = 'inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-xs font-semibold ring-1 ring-inset transition';
    $toggleTone = $node->is_active
        ? 'text-amber-700 ring-amber-200 hover:bg-amber-50 focus-visible:outline-amber-600'
        : 'text-emerald-700 ring-emerald-200 hover:bg-emerald-50 focus-visible:outline-emerald-600';
    $focusRing = 'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-1';
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
                   class="{{ $actionBase }} {{ $focusRing }} text-indigo-700 ring-indigo-200 hover:bg-indigo-50 focus-visible:outline-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                    </svg>
                    Edit
                </a>

                <form method="POST" action="{{ route('admin.locations.toggle', $node) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="{{ $actionBase }} {{ $focusRing }} {{ $toggleTone }}">
                        @if ($node->is_active)
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        @endif
                        {{ $node->is_active ? 'Nyahaktif' : 'Aktifkan' }}
                    </button>
                </form>

                @if ($canDelete)
                    <form method="POST" action="{{ route('admin.locations.destroy', $node) }}"
                          onsubmit="return confirm('Padam {{ $node->name }} secara kekal?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="{{ $actionBase }} {{ $focusRing }} text-rose-700 ring-rose-200 hover:bg-rose-50 focus-visible:outline-rose-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                            </svg>
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

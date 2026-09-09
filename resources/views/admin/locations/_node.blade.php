@php($canManage = auth()->user()->can('lokasi.kemaskini'))
@php($canDelete = auth()->user()->can('lokasi.padam'))

<li x-data="{ open: true }" class="border-l border-slate-200 pl-4">
    <div class="flex flex-wrap items-center gap-2 py-2">
        @if ($node->descendants->isNotEmpty())
            <button type="button" @click="open = !open"
                    class="flex h-5 w-5 items-center justify-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    :aria-expanded="open ? 'true' : 'false'"
                    aria-label="Buka atau tutup cabang">
                <span x-show="open">&minus;</span>
                <span x-show="!open" x-cloak>+</span>
            </button>
        @else
            <span class="inline-block h-5 w-5"></span>
        @endif

        <span class="font-medium text-slate-900">{{ $node->name }}</span>
        <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">{{ $node->code }}</span>
        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $node->level->label() }}</span>

        @unless ($node->is_active)
            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Tidak aktif</span>
        @endunless

        @if ($canManage)
            <span class="ml-auto flex items-center gap-3 text-sm">
                <a href="{{ route('admin.locations.edit', $node) }}" class="font-medium text-indigo-600 hover:underline">Edit</a>

                <form method="POST" action="{{ route('admin.locations.toggle', $node) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="font-medium text-slate-600 hover:underline">
                        {{ $node->is_active ? 'Nyahaktif' : 'Aktifkan' }}
                    </button>
                </form>

                @if ($canDelete)
                    <form method="POST" action="{{ route('admin.locations.destroy', $node) }}"
                          onsubmit="return confirm('Padam {{ $node->name }} secara kekal?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="font-medium text-rose-600 hover:underline">Padam</button>
                    </form>
                @endif
            </span>
        @endif
    </div>

    @if ($node->descendants->isNotEmpty())
        <ul x-show="open" x-cloak>
            @foreach ($node->descendants->sortBy('name') as $child)
                @include('admin.locations._node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>

@props([
    'name',
    'selected' => null,
    'label' => 'Lokasi',
    'required' => false,
])

@php
    $locations = \App\Models\Location::query()
        ->active()
        ->orderBy('name')
        ->get(['id', 'code', 'name', 'level', 'parent_id'])
        ->map(fn ($location) => [
            'id' => $location->id,
            'code' => $location->code,
            'name' => $location->name,
            'level' => $location->level->value,
            'parent_id' => $location->parent_id,
        ])
        ->values();

    $selectedId = old($name, $selected);
    $levels = \App\Enums\LocationLevel::cases();
@endphp

<div x-data="locationPicker(@js($locations), @js($selectedId))" class="space-y-3">
    <span class="block text-sm font-medium text-slate-700">{{ $label }}</span>

    @foreach ($levels as $level)
        <div>
            <label for="{{ $name }}_{{ $level->value }}" class="block text-xs font-medium text-slate-500">
                {{ $level->label() }}
            </label>
            <select id="{{ $name }}_{{ $level->value }}"
                    x-model="chosen['{{ $level->value }}']"
                    @change="clearBelow('{{ $level->value }}')"
                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">— Pilih {{ $level->label() }} —</option>
                <template x-for="option in optionsFor('{{ $level->value }}')" :key="option.id">
                    <option :value="option.id" x-text="option.name + ' (' + option.code + ')'"></option>
                </template>
            </select>
        </div>
    @endforeach

    <input type="hidden" name="{{ $name }}" :value="value()" @required($required)>

    {{-- Server-rendered fallback so the options are visible without JavaScript. --}}
    <noscript>
        <select name="{{ $name }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
            <option value="">— Pilih lokasi —</option>
            @foreach ($locations as $location)
                <option value="{{ $location['id'] }}" @selected((string) $selectedId === (string) $location['id'])>
                    {{ $location['name'] }} ({{ $location['code'] }})
                </option>
            @endforeach
        </select>
    </noscript>
</div>

@once
    @push('scripts')
        <script>
            function locationPicker(locations, selectedId) {
                const levels = ['kampus', 'bangunan', 'tingkat', 'ruang'];

                return {
                    locations,
                    chosen: { kampus: '', bangunan: '', tingkat: '', ruang: '' },

                    init() {
                        if (! selectedId) {
                            return;
                        }

                        let node = this.locations.find((item) => String(item.id) === String(selectedId));

                        while (node) {
                            this.chosen[node.level] = String(node.id);
                            node = this.locations.find((item) => String(item.id) === String(node.parent_id));
                        }
                    },

                    optionsFor(level) {
                        const index = levels.indexOf(level);

                        if (index === 0) {
                            return this.locations.filter((item) => item.parent_id === null);
                        }

                        const parentId = this.chosen[levels[index - 1]];

                        if (! parentId) {
                            return [];
                        }

                        return this.locations.filter((item) => String(item.parent_id) === String(parentId));
                    },

                    clearBelow(level) {
                        const index = levels.indexOf(level);

                        levels.slice(index + 1).forEach((lower) => {
                            this.chosen[lower] = '';
                        });
                    },

                    value() {
                        for (const level of [...levels].reverse()) {
                            if (this.chosen[level]) {
                                return this.chosen[level];
                            }
                        }

                        return '';
                    },
                };
            }
        </script>
    @endpush
@endonce

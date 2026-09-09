@csrf

@php
    // The validator demands a parent exactly one level up, so the select only
    // ever offers that level. Derived from the enum rather than passed in, so
    // the rule and the picker cannot drift apart.
    $parentLevels = collect($levels)->mapWithKeys(
        fn ($level) => [$level->value => $level->parentLevel()?->value],
    );
@endphp

<div x-data="{
        level: @js(old('level', $location->level?->value ?? \App\Enums\LocationLevel::Kampus->value)),
        parentLevels: @js($parentLevels),
        get requiredParentLevel() {
            return this.parentLevels[this.level] ?? null;
        },
        allows(optionLevel) {
            return this.requiredParentLevel === optionLevel;
        },
    }"
     x-effect="if (! requiredParentLevel) { $refs.parent.value = '' }
               else if ($refs.parent.selectedOptions[0] && ! allows($refs.parent.selectedOptions[0].dataset.level)) { $refs.parent.value = '' }">

    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
        <div>
            <label for="code" class="block text-sm font-medium text-slate-700">
                Kod <span class="text-rose-500" aria-hidden="true">*</span>
            </label>
            <input type="text" id="code" name="code" value="{{ old('code', $location->code) }}"
                   required maxlength="30" autocomplete="off" placeholder="cth. KMP-01"
                   @error('code') aria-invalid="true" aria-describedby="code-error" @enderror
                   class="mt-1.5 block w-full font-mono uppercase shadow-sm placeholder:normal-case @error('code') border-rose-400 @enderror">
            @error('code')
                <p id="code-error" class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-slate-500">Kod unik, maksimum 30 aksara. Disimpan dalam huruf besar.</p>
            @enderror
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">
                Nama <span class="text-rose-500" aria-hidden="true">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $location->name) }}"
                   required maxlength="150" placeholder="cth. Kampus Induk"
                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                   class="mt-1.5 block w-full shadow-sm @error('name') border-rose-400 @enderror">
            @error('name')
                <p id="name-error" class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-slate-500">Nama penuh seperti yang dipaparkan dalam direktori.</p>
            @enderror
        </div>

        <div>
            <label for="level" class="block text-sm font-medium text-slate-700">
                Aras <span class="text-rose-500" aria-hidden="true">*</span>
            </label>
            <select id="level" name="level" required x-model="level"
                    @error('level') aria-invalid="true" aria-describedby="level-error" @enderror
                    class="mt-1.5 block w-full shadow-sm @error('level') border-rose-400 @enderror">
                @foreach ($levels as $level)
                    <option value="{{ $level->value }}" @selected(old('level', $location->level?->value) === $level->value)>
                        {{ $level->label() }}
                    </option>
                @endforeach
            </select>
            @error('level')
                <p id="level-error" class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-slate-500">Kampus &rsaquo; Bangunan &rsaquo; Tingkat &rsaquo; Ruang.</p>
            @enderror
        </div>

        <div>
            <label for="parent_id" class="block text-sm font-medium text-slate-700">Lokasi induk</label>
            <select id="parent_id" name="parent_id" x-ref="parent"
                    :disabled="! requiredParentLevel"
                    @error('parent_id') aria-invalid="true" aria-describedby="parent_id-error" @enderror
                    class="mt-1.5 block w-full shadow-sm @error('parent_id') border-rose-400 @enderror">
                <option value="" x-text="requiredParentLevel ? '— Pilih lokasi induk —' : 'Tiada (aras kampus)'">
                    Tiada (aras kampus)
                </option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent['id'] }}" data-level="{{ $parent['level'] }}"
                            :hidden="! allows('{{ $parent['level'] }}')"
                            :disabled="! allows('{{ $parent['level'] }}')"
                            @selected((string) old('parent_id', $location->parent_id) === (string) $parent['id'])>
                        {{ $parent['label'] }}
                    </option>
                @endforeach
            </select>
            @error('parent_id')
                <p id="parent_id-error" class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-slate-500">
                    <span x-show="! requiredParentLevel">Aras kampus berada di puncak hierarki dan tiada induk.</span>
                    <span x-show="requiredParentLevel" x-cloak>Induk mesti berada tepat satu aras di atas aras yang dipilih.</span>
                </p>
            @enderror
        </div>
    </div>

    <div class="mt-6 border-t border-slate-100 pt-5">
        <label for="is_active" class="flex cursor-pointer items-start gap-3">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   @checked(old('is_active', $location->is_active ?? true)) class="mt-0.5">
            <span>
                <span class="block text-sm font-medium text-slate-700">Aktif</span>
                <span class="block text-xs text-slate-500">
                    Lokasi tidak aktif kekal dalam rekod tetapi tidak boleh dipilih dalam borang lain.
                </span>
            </span>
        </label>
    </div>
</div>

<div class="-mx-6 -mb-6 mt-8 flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50 px-6 py-4">
    <a href="{{ route('admin.locations.index') }}"
       class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-200/60 hover:text-slate-900">
        Batal
    </a>
    <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
        Simpan
    </button>
</div>

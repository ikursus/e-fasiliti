@csrf

<div x-data="{ parentId: @js((string) old('parent_id', $unit->parent_id ?? '')) }">
    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
        <div>
            <label for="code" class="block text-sm font-medium text-slate-700">
                Kod <span class="text-rose-500" aria-hidden="true">*</span>
            </label>
            <input type="text" id="code" name="code" value="{{ old('code', $unit->code) }}"
                   required maxlength="50" autocomplete="off" placeholder="cth. BKP"
                   @error('code') aria-invalid="true" aria-describedby="code-error" @enderror
                   class="mt-1.5 block w-full font-mono uppercase shadow-sm placeholder:normal-case @error('code') border-rose-400 @enderror">
            @error('code')
                <p id="code-error" class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-slate-500">Unik untuk seluruh organisasi, maksimum 50 aksara.</p>
            @enderror
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">
                Nama <span class="text-rose-500" aria-hidden="true">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $unit->name) }}"
                   required maxlength="150" placeholder="cth. Bahagian Khidmat Pengurusan"
                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                   class="mt-1.5 block w-full shadow-sm @error('name') border-rose-400 @enderror">
            @error('name')
                <p id="name-error" class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-slate-500">Nama penuh seperti yang dipaparkan dalam direktori.</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="parent_id" class="block text-sm font-medium text-slate-700">Bahagian induk</label>
            <select id="parent_id" name="parent_id" x-model="parentId"
                    @error('parent_id') aria-invalid="true" aria-describedby="parent_id-error" @enderror
                    class="mt-1.5 block w-full shadow-sm @error('parent_id') border-rose-400 @enderror">
                <option value="">Tiada — rekod ini ialah sebuah bahagian</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" @selected((string) old('parent_id', $unit->parent_id) === (string) $division->id)>
                        {{ $division->name }} ({{ $division->code }})
                    </option>
                @endforeach
            </select>
            @error('parent_id')
                <p id="parent_id-error" class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-slate-500">
                    <span x-show="parentId === ''">Hierarki ini dua aras sahaja. Tanpa induk, rekod ini menjadi sebuah bahagian.</span>
                    <span x-show="parentId !== ''" x-cloak>Induk mesti sebuah bahagian, bukan unit.</span>
                </p>
            @enderror
        </div>
    </div>

    <div class="mt-6 border-t border-slate-100 pt-5">
        <label for="is_active" class="flex cursor-pointer items-start gap-3">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   @checked(old('is_active', $unit->is_active ?? true)) class="mt-0.5">
            <span>
                <span class="block text-sm font-medium text-slate-700">Aktif</span>
                <span class="block text-xs text-slate-500">
                    Unit tidak aktif kekal dalam rekod tetapi tidak boleh dipilih dalam borang lain.
                </span>
            </span>
        </label>
        @error('is_active')
            <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="-mx-6 -mb-6 mt-8 flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50 px-6 py-4">
    <a href="{{ route('admin.organization-units.index') }}"
       class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-200/60 hover:text-slate-900">
        Batal
    </a>
    <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
        Simpan
    </button>
</div>

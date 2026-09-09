@csrf

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Kod</label>
        <input type="text" id="code" name="code" value="{{ old('code', $unit->code) }}" required maxlength="50"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('code')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
        <input type="text" id="name" name="name" value="{{ old('name', $unit->name) }}" required maxlength="150"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="parent_id" class="block text-sm font-medium text-slate-700">Bahagian induk</label>
        <select id="parent_id" name="parent_id"
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Tiada (rekod ini ialah sebuah bahagian)</option>
            @foreach ($divisions as $division)
                <option value="{{ $division->id }}" @selected((string) old('parent_id', $unit->parent_id) === (string) $division->id)>
                    {{ $division->name }} ({{ $division->code }})
                </option>
            @endforeach
        </select>
        @error('parent_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $unit->is_active ?? true))
           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
    Aktif
</label>
@error('is_active')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        Simpan
    </button>
    <a href="{{ route('admin.organization-units.index') }}" class="text-sm font-medium text-slate-500 hover:underline">Batal</a>
</div>

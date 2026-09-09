@csrf

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="registration_number" class="block text-sm font-medium text-slate-700">No. Pendaftaran *</label>
        <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number', $asset->registration_number) }}" required maxlength="50"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('registration_number')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="category_id" class="block text-sm font-medium text-slate-700">Kategori *</label>
        <select id="category_id" name="category_id" required
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— Pilih kategori —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $asset->category_id) === (string) $category->id)>{{ $category->label }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="brand" class="block text-sm font-medium text-slate-700">Jenama *</label>
        <input type="text" id="brand" name="brand" value="{{ old('brand', $asset->brand) }}" required maxlength="100"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('brand')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="model" class="block text-sm font-medium text-slate-700">Model *</label>
        <input type="text" id="model" name="model" value="{{ old('model', $asset->model) }}" required maxlength="100"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('model')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="serial_number" class="block text-sm font-medium text-slate-700">No. Siri</label>
        <input type="text" id="serial_number" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" maxlength="100"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('serial_number')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="acquisition_date" class="block text-sm font-medium text-slate-700">Tarikh Perolehan *</label>
        <input type="date" id="acquisition_date" name="acquisition_date" value="{{ old('acquisition_date', $asset->acquisition_date?->format('Y-m-d')) }}" required
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('acquisition_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="acquisition_cost" class="block text-sm font-medium text-slate-700">Harga Perolehan (RM)</label>
        <input type="number" id="acquisition_cost" name="acquisition_cost" step="0.01" min="0" value="{{ old('acquisition_cost', $asset->acquisition_cost) }}"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('acquisition_cost')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="order_number" class="block text-sm font-medium text-slate-700">No. Pesanan</label>
        <input type="text" id="order_number" name="order_number" value="{{ old('order_number', $asset->order_number) }}" maxlength="50"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('order_number')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="warranty_start_date" class="block text-sm font-medium text-slate-700">Waranti Mula</label>
        <input type="date" id="warranty_start_date" name="warranty_start_date" value="{{ old('warranty_start_date', $asset->warranty_start_date?->format('Y-m-d')) }}"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('warranty_start_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="warranty_months" class="block text-sm font-medium text-slate-700">Tempoh Waranti (bulan)</label>
        <input type="number" id="warranty_months" name="warranty_months" min="0" max="600" value="{{ old('warranty_months', $asset->warranty_months) }}"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('warranty_months')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-slate-700">Status *</label>
        <select id="status" name="status" required
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $asset->status?->value ?? 'digunakan') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="responsible_user_id" class="block text-sm font-medium text-slate-700">Pengguna bertanggungjawab</label>
        <select id="responsible_user_id" name="responsible_user_id"
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— Tiada (aset dalam simpanan) —</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('responsible_user_id', $asset->responsible_user_id) === (string) $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('responsible_user_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <x-ui.location-picker name="location_id" :selected="old('location_id', $asset->location_id)" label="Lokasi semasa *" :required="true" />
        @error('location_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="mac_address" class="block text-sm font-medium text-slate-700">Alamat MAC</label>
        <input type="text" id="mac_address" name="mac_address" value="{{ old('mac_address', $asset->mac_address) }}" maxlength="20"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('mac_address')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="ip_address" class="block text-sm font-medium text-slate-700">Alamat IP</label>
        <input type="text" id="ip_address" name="ip_address" value="{{ old('ip_address', $asset->ip_address) }}" maxlength="45"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('ip_address')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="hostname" class="block text-sm font-medium text-slate-700">Nama Hos</label>
        <input type="text" id="hostname" name="hostname" value="{{ old('hostname', $asset->hostname) }}" maxlength="100"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('hostname')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="notes" class="block text-sm font-medium text-slate-700">Catatan</label>
        <textarea id="notes" name="notes" rows="3"
                  class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $asset->notes) }}</textarea>
        @error('notes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    @if ($asset->exists)
        <div class="sm:col-span-2 rounded-lg border border-amber-200 bg-amber-50 p-3">
            <label for="reason" class="block text-sm font-medium text-amber-900">Sebab perubahan</label>
            <p class="mt-0.5 text-xs text-amber-700">Wajib jika lokasi, pemilik atau status berubah — direkod dalam sejarah aset.</p>
            <textarea id="reason" name="reason" rows="2" maxlength="500"
                      class="mt-1 block w-full rounded-lg border-amber-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('reason') }}</textarea>
            @error('reason')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    @endif
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        {{ $asset->exists ? 'Kemas Kini' : 'Daftar Aset' }}
    </button>
    <a href="{{ $asset->exists ? route('admin.assets.show', $asset) : route('admin.assets.index') }}" class="text-sm font-medium text-slate-500 hover:underline">Batal</a>
</div>
@php
    $oldLayouts = old('layouts');

    if ($oldLayouts !== null) {
        $layoutRows = array_values($oldLayouts);
    } elseif ($room->layouts->isNotEmpty()) {
        $layoutRows = $room->layouts->map(fn ($layout) => [
            'layout_code' => $layout->layout_code,
            'capacity' => $layout->capacity,
            'is_default' => $layout->is_default,
        ])->all();
    } else {
        $layoutRows = [['layout_code' => '', 'capacity' => '', 'is_default' => true]];
    }

    $selectedFacilities = old('facilities', $room->facilities->pluck('facility_code')->all()) ?? [];
    $selectedRoles = old('allowed_roles', $room->allowed_roles ?? []) ?? [];
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Kod bilik</label>
        <input type="text" id="code" name="code" value="{{ old('code', $room->code) }}" required maxlength="30"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('code')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama bilik</label>
        <input type="text" id="name" name="name" value="{{ old('name', $room->name) }}" required maxlength="150"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <x-ui.location-picker name="location_id" :selected="$room->location_id" label="Lokasi bilik" required />
        @error('location_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="base_capacity" class="block text-sm font-medium text-slate-700">Kapasiti asas (orang)</label>
        <input type="number" id="base_capacity" name="base_capacity" min="1" required
               value="{{ old('base_capacity', $room->base_capacity ?? 20) }}"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('base_capacity')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="min_duration_minutes" class="block text-sm font-medium text-slate-700">Tempoh min (minit)</label>
            <input type="number" id="min_duration_minutes" name="min_duration_minutes" min="1" required
                   value="{{ old('min_duration_minutes', $room->min_duration_minutes ?? 30) }}"
                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('min_duration_minutes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="max_duration_minutes" class="block text-sm font-medium text-slate-700">Tempoh maks (minit)</label>
            <input type="number" id="max_duration_minutes" name="max_duration_minutes" min="1" required
                   value="{{ old('max_duration_minutes', $room->max_duration_minutes ?? 480) }}"
                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('max_duration_minutes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="mt-4 space-y-2">
    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="hidden" name="requires_approval" value="0">
        <input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval', $room->requires_approval ?? false))
               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        Tempahan bilik ini perlu kelulusan
    </label>
    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $room->is_active ?? true))
               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        Aktif
    </label>
    @error('is_active')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
</div>

<fieldset class="mt-6">
    <legend class="text-sm font-semibold text-slate-900">Peranan yang dibenarkan menempah</legend>
    <p class="mb-2 text-xs text-slate-500">Kosongkan untuk membenarkan semua peranan (FR-BLK-06).</p>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        @foreach ($roleOptions as $role)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="allowed_roles[]" value="{{ $role }}"
                       @checked(in_array($role, $selectedRoles))
                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                {{ ucfirst(str_replace('-', ' ', $role)) }}
            </label>
        @endforeach
    </div>
    @error('allowed_roles.*')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</fieldset>

<fieldset class="mt-6" x-data="roomLayouts(@js($layoutRows), @js($layoutOptions->all()))">
    <legend class="text-sm font-semibold text-slate-900">Susun atur yang disokong (FR-BLK-03)</legend>
    <p class="mb-2 text-xs text-slate-500">Setiap susun atur membawa kapasitinya sendiri. Tandakan tepat satu sebagai lalai.</p>
    @error('layouts')<p class="mb-2 text-sm text-rose-600">{{ $message }}</p>@enderror

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <th class="pb-2">Jenis susun atur</th>
                <th class="pb-2">Kapasiti</th>
                <th class="pb-2">Lalai</th>
                <th class="pb-2"></th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, index) in rows" :key="index">
                <tr>
                    <td class="py-1 pr-2">
                        <select :name="`layouts[${index}][layout_code]`" x-model="rows[index].layout_code"
                                class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Pilih susun atur —</option>
                            <template x-for="(label, code) in options" :key="code">
                                <option :value="code" x-text="label"></option>
                            </template>
                        </select>
                    </td>
                    <td class="py-1 pr-2">
                        <input type="number" min="1" :name="`layouts[${index}][capacity]`" x-model="rows[index].capacity"
                               class="block w-24 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </td>
                    <td class="py-1 pr-2 text-center">
                        <input type="radio" name="default_layout_choice" :value="String(index)"
                               x-model="defaultIndex" @change="markDefault(index)"
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <input type="hidden" :name="`layouts[${index}][is_default]`" :value="defaultIndex === index ? '1' : '0'">
                    </td>
                    <td class="py-1 text-right">
                        <button type="button" @click="removeRow(index)"
                                class="text-sm font-medium text-rose-600 hover:underline">Buang</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>

    <button type="button" @click="addRow()"
            class="mt-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
        + Tambah susun atur
    </button>
</fieldset>

<fieldset class="mt-6">
    <legend class="text-sm font-semibold text-slate-900">Kemudahan tetap (FR-BLK-04)</legend>
    <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
        @foreach ($facilityOptions as $code => $label)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="facilities[]" value="{{ $code }}"
                       @checked(in_array($code, $selectedFacilities))
                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                {{ $label }}
            </label>
        @endforeach
    </div>
    @error('facilities.*')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</fieldset>

@if (isset($hours))
    <fieldset class="mt-6">
        <legend class="text-sm font-semibold text-slate-900">Waktu operasi bilik</legend>
        <p class="mb-2 text-xs text-slate-500">Hari yang dibiarkan tidak aktif mengikut waktu organisasi; tempahan di luar waktu operasi bilik ditolak (FR-TMP-06, kecuali pentadbir).</p>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="pb-2">Hari</th>
                    <th class="pb-2">Tutup</th>
                    <th class="pb-2">Buka</th>
                    <th class="pb-2">Tutup</th>
                </tr>
            </thead>
            <tbody>
                @foreach (range(0, 6) as $day)
                    @php($dayValues = old("days.{$day}", $hours[$day]))
                    <tr>
                        <td class="py-1 pr-2 font-medium text-slate-700">{{ \App\Models\OperatingHour::DAY_NAMES[$day] }}</td>
                        <td class="py-1 pr-2">
                            <input type="hidden" name="days[{{ $day }}][is_closed]" value="0">
                            <input type="checkbox" name="days[{{ $day }}][is_closed]" value="1"
                                   @checked((bool) ($dayValues['is_closed'] ?? false))
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="py-1 pr-2">
                            <input type="time" name="days[{{ $day }}][opens_at]" value="{{ $dayValues['opens_at'] ?? '08:00' }}"
                                   class="block w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error("days.{$day}.opens_at")<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="py-1">
                            <input type="time" name="days[{{ $day }}][closes_at]" value="{{ $dayValues['closes_at'] ?? '17:00' }}"
                                   class="block w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error("days.{$day}.closes_at")<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </fieldset>
@endif

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        Simpan
    </button>
    <a href="{{ route('admin.rooms.index') }}" class="text-sm font-medium text-slate-500 hover:underline">Batal</a>
</div>

@once
    @push('scripts')
        <script>
            function roomLayouts(rows, options) {
                const initialDefault = rows.findIndex(
                    (row) => row.is_default === true || row.is_default === '1' || row.is_default === 1,
                );

                return {
                    rows,
                    options,
                    defaultIndex: initialDefault >= 0 ? initialDefault : 0,

                    init() {
                        this.syncFlags();
                    },

                    addRow() {
                        this.rows.push({ layout_code: '', capacity: '', is_default: false });
                        this.syncFlags();
                    },

                    removeRow(index) {
                        this.rows.splice(index, 1);

                        if (this.defaultIndex >= this.rows.length) {
                            this.defaultIndex = Math.max(0, this.rows.length - 1);
                        }

                        this.syncFlags();
                    },

                    markDefault(index) {
                        this.defaultIndex = index;
                        this.syncFlags();
                    },

                    syncFlags() {
                        this.rows.forEach((row, position) => {
                            row.is_default = position === Number(this.defaultIndex);
                        });
                    },
                };
            }
        </script>
    @endpush
@endonce

@php
    $userRoleNames = [];
    if ($user !== null) {
        foreach ($user->getRoleNames() as $roleName) {
            $userRoleNames[] = $roleName;
        }
    }
@endphp

<form method="POST" action="{{ $action }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @if ($method ?? null)
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nama Penuh *</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user?->name) }}" required
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Emel *</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" required
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">
                Kata Laluan {{ ($user ?? null) ? '' : '*' }}
            </label>
            <input id="password" type="password" name="password" autocomplete="new-password"
                {{ ($user ?? null) ? '' : 'required' }}
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
            @if ($user ?? null)
                <p class="mt-1 text-xs text-slate-400">Kosong jika tidak hendak ubah kata laluan.</p>
            @endif
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Sahkan Kata Laluan</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
        </div>

        <div>
            <label for="organization_unit_id" class="block text-sm font-medium text-slate-700">Unit Organisasi</label>
            <select id="organization_unit_id" name="organization_unit_id" class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                <option value="">— Tiada —</option>
                @foreach ($organizationUnits as $unit)
                    <option value="{{ $unit->id }}" {{ (string) $user?->organization_unit_id === (string) $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <x-ui.location-picker name="primary_location_id" :selected="$user->primary_location_id ?? null" label="Lokasi utama" />
            @error('primary_location_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-6">
        <p class="text-sm font-medium text-slate-700">Peranan *</p>
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($roles as $role)
                <label class="flex items-start gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <input
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->name }}"
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        @php
                            $roleChecked = false;
                            foreach ($userRoleNames as $roleName) {
                                if ($roleName === $role->name) {
                                    $roleChecked = true;
                                    break;
                                }
                            }
                        @endphp
                        {{ $roleChecked ? 'checked' : '' }}
                    >
                    <span class="text-slate-700">{{ $role->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-4">
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1"
                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                {{ (($user?->is_active) ?? true) ? 'checked' : '' }}
            >
            Akaun aktif
        </label>

        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
            {{ $submitText }}
        </button>
    </div>
</form>
<div class="mt-6">
    <p class="text-sm font-medium text-slate-700">Kebenaran modul</p>
    <div class="mt-3 space-y-4">
        @foreach ($permissions as $module => $items)
            <fieldset class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <legend class="px-2 text-sm font-semibold uppercase tracking-wider text-slate-600">{{ $module }}</legend>
                @foreach ($items as $permission)
                    @php
                        $isChecked = false;
                        foreach ($checkedPermissions as $checkedName) {
                            if ($checkedName === $permission->name) {
                                $isChecked = true;
                                break;
                            }
                        }
                    @endphp
                    <label class="flex items-start gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->name }}"
                            class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            {{ $isChecked ? 'checked' : '' }}
                        >
                        <span class="text-slate-700">{{ $permission->name }}</span>
                    </label>
                @endforeach
            </fieldset>
        @endforeach
    </div>
</div>
@extends('layouts.app')

@section('title', 'Edit Peranan · Pentadbiran')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-900">Edit Peranan</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $role->name }} — sunting nama dan kebenaran.</p>
    </div>

    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PATCH')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nama Peranan *</label>
            <input id="name" type="text" name="name" value="{{ old('name', $role->name) }}" required
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
        </div>

        @php
            $checkedRoleNames = [];
            foreach ($role->permissions as $permission) {
                $checkedRoleNames[] = $permission->name;
            }
        @endphp

        @include('admin.roles._permissions', [
            'permissions' => $permissions,
            'checkedPermissions' => $checkedRoleNames,
        ])

        <div class="mt-6 flex flex-wrap items-center gap-4">
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                Simpan Perubahan
            </button>
        </div>
    </form>

    <div class="mt-8 rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-rose-700">Zon Bahaya</h3>
        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
            onsubmit="return confirm('Padam peranan ini secara kekal? Pengguna yang memiliki peranan ini akan hilang kebenarannya.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="mt-3 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                Padam Peranan
            </button>
        </form>
    </div>
@endsection
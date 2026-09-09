@extends('layouts.app')

@section('title', 'Peranan Baharu · Pentadbiran')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-900">Peranan Baharu</h2>
        <p class="mt-1 text-sm text-slate-500">Cipta peranan baharu dan penataskan kebenarannya.</p>
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nama Peranan *</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
            <p class="mt-1 text-xs text-slate-400">Guna slug kebab-case, contoh: <code>penyelia-ict</code>.</p>
        </div>

        @include('admin.roles._permissions', [
            'permissions' => $permissions,
            'checkedPermissions' => [],
        ])

        <div class="mt-6">
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                Cipta Peranan
            </button>
        </div>
    </form>
@endsection
@extends('layouts.app')

@section('title', 'Inventori Aset · Aset')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Inventori Aset</h2>
            <p class="mt-1 text-sm text-slate-500">Daftar induk aset ICT (M09).</p>
        </div>
        @can('aset.cipta')
            <a href="{{ route('admin.assets.create') }}"
               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                + Daftar Aset
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin.assets.index') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="min-w-56">
            <label for="search" class="block text-xs font-medium text-slate-500">Cari no. pendaftaran / siri / jenama / model / hos</label>
            <input id="search" type="text" name="search" value="{{ $search }}"
                   class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div class="min-w-40">
            <label for="category_id" class="block text-xs font-medium text-slate-500">Kategori</label>
            <select id="category_id" name="category_id" class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                <option value="">— Semua —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request()->input('category_id') === (string) $category->id)>{{ $category->label }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-40">
            <label for="status" class="block text-xs font-medium text-slate-500">Status</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                <option value="">— Semua —</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request()->input('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-44">
            <label for="location_id" class="block text-xs font-medium text-slate-500">Lokasi</label>
            <select id="location_id" name="location_id" class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                <option value="">— Semua —</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(request()->input('location_id') === (string) $location->id)>{{ $location->name }} ({{ $location->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-44">
            <label for="responsible_user_id" class="block text-xs font-medium text-slate-500">Pengguna</label>
            <select id="responsible_user_id" name="responsible_user_id" class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                <option value="">— Semua —</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request()->input('responsible_user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Tapis</button>
        <a href="{{ route('admin.assets.index') }}" class="px-3 py-2 text-sm font-medium text-slate-500 hover:underline">Kosongkan</a>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">No. Pendaftaran</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Jenama / Model</th>
                    <th class="px-4 py-3">No. Siri</th>
                    <th class="px-4 py-3">Lokasi</th>
                    <th class="px-4 py-3">Pengguna</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($assets as $asset)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.assets.show', $asset) }}" class="font-medium text-indigo-600 hover:underline">{{ $asset->registration_number }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $asset->category?->label ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $asset->brand }} {{ $asset->model }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $asset->serial_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $asset->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $asset->responsibleUser?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $asset->status->badge() }}">{{ $asset->status->label() }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('aset.kemaskini')
                                <a href="{{ route('admin.assets.edit', $asset) }}" class="font-medium text-indigo-600 hover:underline">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">Tiada aset sepadan. Daftar aset baharu untuk mula.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $assets->links() }}</div>
@endsection
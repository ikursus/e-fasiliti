@extends('layouts.app')

@section('title', 'Pengguna · Pentadbiran')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Pengguna</h2>
            <p class="mt-1 text-sm text-slate-500">Pengurusan akaun pengguna, peranan dan status.</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
            + Pengguna Baharu
        </a>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="min-w-56">
            <label for="search" class="block text-xs font-medium text-slate-500">Cari nama / emel</label>
            <input id="search" type="text" name="search" value="{{ request()->input('search') }}"
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div class="min-w-44">
            <label for="role" class="block text-xs font-medium text-slate-500">Peranan</label>
            <select id="role" name="role" class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                <option value="">— Semua —</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" {{ request()->input('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
            Tapis
        </button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Pengguna</th>
                    <th class="px-4 py-3">Peranan</th>
                    <th class="px-4 py-3">Unit</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @foreach ($user->roles as $role)
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->organizationUnit?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700">Nyahaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-indigo-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Tiada pengguna yang sepadan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
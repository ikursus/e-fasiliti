@extends('layouts.app')

@section('title', 'Peranan · Pentadbiran')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Peranan &amp; Kebenaran</h2>
            <p class="mt-1 text-sm text-slate-500">Peranan sistem dan kebenaran modul yang diberikaan.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}"
           class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
            + Peranan Baharu
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Peranan</th>
                    <th class="px-4 py-3 text-center">Kebenaran</th>
                    <th class="px-4 py-3 text-center">Pengguna</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($roles as $role)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $role->name }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $role->permissions_count }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $role->users_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="font-medium text-indigo-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Tiada peranan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $roles->links() }}</div>
@endsection
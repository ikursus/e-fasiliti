@extends('layouts.app')

@section('title', 'Katalog Bilik · Pentadbiran')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Katalog Bilik Mesyuarat</h2>
            <p class="mt-1 text-sm text-slate-500">Bilik, susun atur, kemudahan dan peraturan tempahan khusus bilik.</p>
        </div>
        @can('bilik.cipta')
            <a href="{{ route('admin.rooms.create') }}"
               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                + Bilik Baharu
            </a>
        @endcan
    </div>

    @error('id')
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $message }}</div>
    @enderror

    <form method="GET" action="{{ route('admin.rooms.index') }}" class="mb-4 flex gap-2">
        <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau kod bilik"
               class="w-full max-w-sm rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Cari
        </button>
        @if ($search !== '')
            <a href="{{ route('admin.rooms.index') }}" class="px-3 py-2 text-sm font-medium text-slate-500 hover:underline">Kosongkan</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Kod</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Lokasi</th>
                    <th class="px-4 py-3">Kapasiti</th>
                    <th class="px-4 py-3">Kelulusan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rooms as $room)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $room->code }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $room->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $room->locationFullPath() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $room->base_capacity }} orang</td>
                        <td class="px-4 py-3">
                            @if ($room->requires_approval)
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Perlu kelulusan</span>
                            @else
                                <span class="text-xs text-slate-400">Tiada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($room->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600">Tidak aktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-3">
                                @can('bilik.kemaskini')
                                    <a href="{{ route('admin.rooms.edit', $room) }}" class="text-sm font-medium text-indigo-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.rooms.toggle', $room) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm font-medium text-slate-600 hover:underline">
                                            {{ $room->is_active ? 'Nyahaktif' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endcan
                                @can('bilik.padam')
                                    <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}"
                                          onsubmit="return confirm('Padam bilik ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-rose-600 hover:underline">Padam</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                            Tiada bilik didaftarkan lagi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

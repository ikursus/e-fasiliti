@extends('layouts.app')

@section('title', 'Konfigurasi · Cuti Umum')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Cuti umum dan hari yang tidak boleh ditempah.</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-800">Tahun {{ $year }}</h3>
                <form method="GET" action="{{ route('admin.settings.holidays') }}" class="flex items-center gap-2">
                    <input type="number" name="year" value="{{ $year }}" min="2020" max="2100"
                           class="w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Papar
                    </button>
                </form>
            </div>

            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Tarikh</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Berulang</th>
                        <th class="px-4 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($holidays as $holiday)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $holiday->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $holiday->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $holiday->typeLabel() }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $holiday->recurs_annually ? 'Ya' : 'Tidak' }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('tetapan.kemaskini')
                                    <form method="POST" action="{{ route('admin.settings.holidays.destroy', $holiday) }}"
                                          onsubmit="return confirm('Padam {{ $holiday->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-rose-600 hover:underline">Padam</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Tiada cuti direkodkan bagi tahun ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @can('tetapan.kemaskini')
            <form method="POST" action="{{ route('admin.settings.holidays.store') }}"
                  class="h-fit rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                <h3 class="mb-4 text-sm font-semibold text-slate-800">Tambah cuti</h3>

                <div class="space-y-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-slate-700">Tarikh</label>
                        <input type="date" id="date" name="date" value="{{ old('date') }}" required
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="150"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700">Jenis</label>
                        <select id="type" name="type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="cuti_umum" @selected(old('type') === 'cuti_umum')>Cuti umum</option>
                            <option value="hari_tanpa_tempahan" @selected(old('type') === 'hari_tanpa_tempahan')>Hari tanpa tempahan</option>
                        </select>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="recurs_annually" value="0">
                        <input type="checkbox" name="recurs_annually" value="1" @checked(old('recurs_annually'))
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Berulang setiap tahun
                    </label>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Aktif
                    </label>
                </div>

                <button type="submit" class="mt-6 w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Tambah
                </button>
            </form>
        @endcan
    </div>
@endsection

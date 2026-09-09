@extends('layouts.app')

@section('title', 'Konfigurasi · Waktu Operasi')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Waktu operasi lalai organisasi. Waktu khusus bilik ditetapkan dalam modul bilik (M04).</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.operating-hours.update') }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="py-2">Hari</th>
                        <th class="py-2">Tutup</th>
                        <th class="py-2">Buka</th>
                        <th class="py-2">Tutup pada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($days as $day)
                        <tr x-data="{ closed: {{ $day['is_closed'] ? 'true' : 'false' }} }">
                            <td class="py-3 font-medium text-slate-800">{{ $day['name'] }}</td>
                            <td class="py-3">
                                <input type="hidden" name="days[{{ $day['day'] }}][is_closed]" value="0">
                                <input type="checkbox" name="days[{{ $day['day'] }}][is_closed]" value="1"
                                       x-model="closed" @checked($day['is_closed'])
                                       @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="py-3">
                                <input type="time" name="days[{{ $day['day'] }}][opens_at]"
                                       value="{{ old("days.{$day['day']}.opens_at", $day['opens_at']) }}"
                                       :disabled="closed || {{ auth()->user()->can('tetapan.kemaskini') ? 'false' : 'true' }}"
                                       class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error("days.{$day['day']}.opens_at")<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </td>
                            <td class="py-3">
                                <input type="time" name="days[{{ $day['day'] }}][closes_at]"
                                       value="{{ old("days.{$day['day']}.closes_at", $day['closes_at']) }}"
                                       :disabled="closed || {{ auth()->user()->can('tetapan.kemaskini') ? 'false' : 'true' }}"
                                       class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error("days.{$day['day']}.closes_at")<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @can('tetapan.kemaskini')
            <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Simpan
            </button>
        @endcan
    </form>
@endsection

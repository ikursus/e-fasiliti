@extends('layouts.app')

@section('title', 'Konfigurasi · Peraturan Tempahan')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Had tempoh dan tempoh awalan tempahan bagi setiap peranan. Digunakan oleh enjin tempahan (M05).</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.booking-rules.update') }}"
          class="overflow-x-auto rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <table class="min-w-full text-sm">
            <thead class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="py-2">Peranan</th>
                    <th class="py-2">Tempoh minimum (minit)</th>
                    <th class="py-2">Tempoh maksimum (minit)</th>
                    <th class="py-2">Tempoh awalan maksimum (hari)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($rows as $row)
                    <tr>
                        <td class="py-3 font-medium text-slate-800">{{ $row['name'] }}</td>
                        <td class="py-3">
                            <input type="number" min="5" max="1440" required
                                   name="rules[{{ $row['id'] }}][min_duration_minutes]"
                                   value="{{ old("rules.{$row['id']}.min_duration_minutes", $row['min_duration_minutes']) }}"
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="py-3">
                            <input type="number" min="5" max="1440" required
                                   name="rules[{{ $row['id'] }}][max_duration_minutes]"
                                   value="{{ old("rules.{$row['id']}.max_duration_minutes", $row['max_duration_minutes']) }}"
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error("rules.{$row['id']}.max_duration_minutes")<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="py-3">
                            <input type="number" min="1" max="730" required
                                   name="rules[{{ $row['id'] }}][max_advance_days]"
                                   value="{{ old("rules.{$row['id']}.max_advance_days", $row['max_advance_days']) }}"
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @can('tetapan.kemaskini')
            <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Simpan
            </button>
        @endcan
    </form>
@endsection

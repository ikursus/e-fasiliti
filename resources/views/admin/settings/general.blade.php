@extends('layouts.app')

@section('title', 'Konfigurasi · Umum')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Tetapan yang boleh diubah tanpa menulis kod.</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.general.update') }}"
          class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label for="umum_nama_organisasi" class="block text-sm font-medium text-slate-700">Nama organisasi</label>
                <input type="text" id="umum_nama_organisasi" name="umum_nama_organisasi"
                       value="{{ old('umum_nama_organisasi', $organisationName) }}" required maxlength="150"
                       @disabled(! auth()->user()->can('tetapan.kemaskini'))
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('umum_nama_organisasi')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="umum_zon_masa" class="block text-sm font-medium text-slate-700">Zon masa</label>
                <input type="text" id="umum_zon_masa" name="umum_zon_masa" value="{{ old('umum_zon_masa', $timezone) }}"
                       @disabled(! auth()->user()->can('tetapan.kemaskini'))
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('umum_zon_masa')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="umum_bahasa_lalai" class="block text-sm font-medium text-slate-700">Bahasa lalai</label>
                <select id="umum_bahasa_lalai" name="umum_bahasa_lalai"
                        @disabled(! auth()->user()->can('tetapan.kemaskini'))
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="ms" @selected(old('umum_bahasa_lalai', $locale) === 'ms')>Bahasa Melayu</option>
                    <option value="en" @selected(old('umum_bahasa_lalai', $locale) === 'en')>English</option>
                </select>
            </div>
        </div>

        @can('tetapan.kemaskini')
            <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Simpan
            </button>
        @endcan
    </form>
@endsection

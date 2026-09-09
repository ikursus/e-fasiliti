@extends('layouts.app')

@section('title', 'Lokasi · Pentadbiran')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Direktori Lokasi</h2>
            <p class="mt-1 text-sm text-slate-500">Hierarki kampus, bangunan, tingkat dan ruang.</p>
        </div>
        @can('lokasi.cipta')
            <a href="{{ route('admin.locations.create') }}"
               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                + Lokasi Baharu
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @error('id')
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $message }}</div>
    @enderror

    <form method="GET" action="{{ route('admin.locations.index') }}" class="mb-4 flex gap-2">
        <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau kod"
               class="w-full max-w-sm rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Cari
        </button>
        @if ($search !== '')
            <a href="{{ route('admin.locations.index') }}" class="px-3 py-2 text-sm font-medium text-slate-500 hover:underline">Kosongkan</a>
        @endif
    </form>

    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        @if ($matches !== null)
            @forelse ($matches as $match)
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 py-2 last:border-0">
                    <span class="font-medium text-slate-900">{{ $match->name }}</span>
                    <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">{{ $match->code }}</span>
                    <span class="text-xs text-slate-500">{{ $match->fullPath() }}</span>
                    @can('lokasi.kemaskini')
                        <a href="{{ route('admin.locations.edit', $match) }}" class="ml-auto text-sm font-medium text-indigo-600 hover:underline">Edit</a>
                    @endcan
                </div>
            @empty
                <p class="py-6 text-center text-sm text-slate-500">Tiada lokasi sepadan dengan carian.</p>
            @endforelse
        @else
            <ul>
                @forelse ($roots as $root)
                    @include('admin.locations._node', ['node' => $root])
                @empty
                    <li class="py-6 text-center text-sm text-slate-500">Tiada lokasi didaftarkan lagi.</li>
                @endforelse
            </ul>
        @endif
    </div>
@endsection

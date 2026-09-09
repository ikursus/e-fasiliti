@extends('layouts.app')

@section('title', 'Lokasi · Pentadbiran')

@section('content')
    <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Direktori Lokasi</h2>
            <p class="mt-1 text-sm text-slate-500">Hierarki kampus, bangunan, tingkat dan ruang.</p>
        </div>
        @can('lokasi.cipta')
            <a href="{{ route('admin.locations.create') }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Lokasi Baharu
            </a>
        @endcan
    </div>

    <form method="GET" action="{{ route('admin.locations.index') }}" class="mb-4 flex flex-wrap items-center gap-2">
        <div class="relative w-full max-w-sm">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/>
                </svg>
            </span>
            <label for="search" class="sr-only">Cari lokasi</label>
            <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Cari nama atau kod"
                   class="block w-full py-2 pl-9 shadow-sm">
        </div>

        <button type="submit"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
            Cari
        </button>

        @if ($search !== '')
            <a href="{{ route('admin.locations.index') }}"
               class="rounded-lg px-3 py-2 text-sm font-medium text-slate-500 transition hover:text-slate-900">
                Kosongkan
            </a>
        @endif
    </form>

    <div x-data class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/70 px-4 py-2.5">
            @if ($matches !== null)
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ $matches->count() }} hasil carian untuk &ldquo;{{ $search }}&rdquo;
                </p>
            @else
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Struktur hierarki</p>
                @if ($roots->isNotEmpty())
                    <div class="flex items-center gap-1 text-xs font-medium text-slate-500">
                        <button type="button" @click="$dispatch('locations-expand')" class="rounded px-2 py-1 transition hover:bg-white hover:text-slate-900">
                            Buka semua
                        </button>
                        <span class="text-slate-300" aria-hidden="true">|</span>
                        <button type="button" @click="$dispatch('locations-collapse')" class="rounded px-2 py-1 transition hover:bg-white hover:text-slate-900">
                            Tutup semua
                        </button>
                    </div>
                @endif
            @endif
        </div>

        <div class="p-3">
            @if ($matches !== null)
                @forelse ($matches as $match)
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 rounded-lg px-2 py-2 transition hover:bg-slate-50">
                        <span class="text-sm font-semibold text-slate-900">{{ $match->name }}</span>
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-slate-600">{{ $match->code }}</span>
                        @unless ($match->is_active)
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-100">
                                Tidak aktif
                            </span>
                        @endunless
                        <span class="w-full truncate text-xs text-slate-500 sm:w-auto">{{ $match->fullPath() }}</span>
                        @can('lokasi.kemaskini')
                            <a href="{{ route('admin.locations.edit', $match) }}"
                               class="ml-auto rounded-md px-2 py-1 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-indigo-600 hover:shadow-sm">
                                Edit
                            </a>
                        @endcan
                    </div>
                @empty
                    <p class="px-2 py-10 text-center text-sm text-slate-500">Tiada lokasi sepadan dengan carian.</p>
                @endforelse
            @else
                <ul>
                    @forelse ($roots as $root)
                        @include('admin.locations._node', ['node' => $root])
                    @empty
                        <li class="px-2 py-10 text-center text-sm text-slate-500">Tiada lokasi didaftarkan lagi.</li>
                    @endforelse
                </ul>
            @endif
        </div>
    </div>
@endsection

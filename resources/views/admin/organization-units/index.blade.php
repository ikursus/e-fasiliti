@extends('layouts.app')

@section('title', 'Unit Organisasi · Pentadbiran')

@section('content')
    <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Direktori Organisasi</h2>
            <p class="mt-1 text-sm text-slate-500">Bahagian dan unit di bawahnya.</p>
        </div>
        @can('unit-organisasi.cipta')
            <a href="{{ route('admin.organization-units.create') }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Unit Baharu
            </a>
        @endcan
    </div>

    <div x-data class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/70 px-4 py-2.5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Struktur organisasi</p>
            @if ($divisions->isNotEmpty())
                <div class="flex items-center gap-1 text-xs font-medium text-slate-500">
                    <button type="button" @click="$dispatch('units-expand')" class="rounded px-2 py-1 transition hover:bg-white hover:text-slate-900">
                        Buka semua
                    </button>
                    <span class="text-slate-300" aria-hidden="true">|</span>
                    <button type="button" @click="$dispatch('units-collapse')" class="rounded px-2 py-1 transition hover:bg-white hover:text-slate-900">
                        Tutup semua
                    </button>
                </div>
            @endif
        </div>

        <div class="p-3">
            <ul>
                @forelse ($divisions as $division)
                    <li x-data="{ open: true }"
                        @units-expand.window="open = true"
                        @units-collapse.window="open = false">
                        @include('admin.organization-units._row', [
                            'unit' => $division,
                            'isDivision' => true,
                            'hasChildren' => $division->children->isNotEmpty(),
                        ])

                        @if ($division->children->isNotEmpty())
                            <ul x-show="open" x-cloak class="ml-5 border-l border-slate-200 pl-3">
                                @foreach ($division->children as $unit)
                                    <li>
                                        @include('admin.organization-units._row', [
                                            'unit' => $unit,
                                            'isDivision' => false,
                                            'hasChildren' => false,
                                        ])
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="ml-5 border-l border-slate-200 py-2 pl-5 text-xs text-slate-500">
                                Tiada unit di bawah bahagian ini.
                            </p>
                        @endif
                    </li>
                @empty
                    <li class="px-2 py-10 text-center text-sm text-slate-500">Tiada bahagian didaftarkan lagi.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection

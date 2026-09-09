@extends('layouts.app')

@section('title', 'Sunting Lokasi · Pentadbiran')

@section('content')
    <div class="mx-auto max-w-3xl">
        <nav class="mb-4 flex items-center gap-2 text-sm text-slate-500" aria-label="Laluan">
            <a href="{{ route('admin.locations.index') }}" class="font-medium text-slate-600 hover:text-indigo-600 hover:underline">
                Direktori Lokasi
            </a>
            <span aria-hidden="true">&rsaquo;</span>
            <span class="truncate font-medium text-slate-900">{{ $location->name }}</span>
        </nav>

        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-xl font-bold text-slate-900">Sunting Lokasi</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $location->fullPath() }}</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $location->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                {{ $location->is_active ? 'Aktif' : 'Tidak aktif' }}
            </span>
        </div>

        <form method="POST" action="{{ route('admin.locations.update', $location) }}"
              class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @method('PUT')
            @include('admin.locations._form')
        </form>
    </div>
@endsection

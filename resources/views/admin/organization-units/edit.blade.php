@extends('layouts.app')

@section('title', 'Sunting Unit · Pentadbiran')

@section('content')
    <div class="mx-auto max-w-3xl">
        <nav class="mb-4 flex items-center gap-2 text-sm text-slate-500" aria-label="Laluan">
            <a href="{{ route('admin.organization-units.index') }}" class="font-medium text-slate-600 hover:text-indigo-600 hover:underline">
                Direktori Organisasi
            </a>
            <span aria-hidden="true">&rsaquo;</span>
            <span class="truncate font-medium text-slate-900">{{ $unit->name }}</span>
        </nav>

        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-xl font-bold text-slate-900">Sunting Unit Organisasi</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $unit->fullPath() }}</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $unit->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                {{ $unit->is_active ? 'Aktif' : 'Tidak aktif' }}
            </span>
        </div>

        <form method="POST" action="{{ route('admin.organization-units.update', $unit) }}"
              class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @method('PUT')
            @include('admin.organization-units._form')
        </form>
    </div>
@endsection

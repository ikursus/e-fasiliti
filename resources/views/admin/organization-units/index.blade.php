@extends('layouts.app')

@section('title', 'Unit Organisasi · Pentadbiran')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Direktori Organisasi</h2>
            <p class="mt-1 text-sm text-slate-500">Bahagian dan unit di bawahnya.</p>
        </div>
        @can('unit-organisasi.cipta')
            <a href="{{ route('admin.organization-units.create') }}"
               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                + Unit Baharu
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

    <div class="space-y-4">
        @forelse ($divisions as $division)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-slate-900">{{ $division->name }}</span>
                    <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">{{ $division->code }}</span>
                    @unless ($division->is_active)
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Tidak aktif</span>
                    @endunless

                    @can('unit-organisasi.kemaskini')
                        <span class="ml-auto flex items-center gap-3 text-sm">
                            <a href="{{ route('admin.organization-units.edit', $division) }}" class="font-medium text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.organization-units.toggle', $division) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="font-medium text-slate-600 hover:underline">
                                    {{ $division->is_active ? 'Nyahaktif' : 'Aktifkan' }}
                                </button>
                            </form>
                            @can('unit-organisasi.padam')
                                <form method="POST" action="{{ route('admin.organization-units.destroy', $division) }}"
                                      onsubmit="return confirm('Padam {{ $division->name }} secara kekal?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-rose-600 hover:underline">Padam</button>
                                </form>
                            @endcan
                        </span>
                    @endcan
                </div>

                <ul class="mt-3 space-y-2 border-l border-slate-200 pl-4">
                    @forelse ($division->children as $unit)
                        <li class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="text-slate-800">{{ $unit->name }}</span>
                            <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">{{ $unit->code }}</span>
                            @unless ($unit->is_active)
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Tidak aktif</span>
                            @endunless
                            @can('unit-organisasi.kemaskini')
                                <a href="{{ route('admin.organization-units.edit', $unit) }}" class="ml-auto font-medium text-indigo-600 hover:underline">Edit</a>
                            @endcan
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Tiada unit di bawah bahagian ini.</li>
                    @endforelse
                </ul>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                Tiada bahagian didaftarkan lagi.
            </div>
        @endforelse
    </div>
@endsection

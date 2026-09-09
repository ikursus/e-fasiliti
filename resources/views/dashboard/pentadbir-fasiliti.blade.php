@extends('layouts.app')

@section('title', 'Papan Pemuka · Pentadbir Fasiliti')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Selamat kembali, {{ auth()->user()->name }}.</h2>
                <p class="mt-1 text-sm text-slate-500">Kawalan katalog bilik, tempahan dan laporan penggunaan.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Pentadbir Fasiliti</span>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        @include('components.partials.empty-state', [
            'title' => 'Kalendar bilik',
            'description' => 'Ketersediaan bilik dan kadar penggunaan akan ditapaparkan di sini dalam Fasa 1.',
            'icon' => '🗓',
        ])

        @include('components.partials.empty-state', [
            'title' => 'Katalog bilik',
            'description' => 'Susun atur, kemudahan dan peraturan tempahan setiap bilik diuruskan di sini dalam Fasa 1.',
            'icon' => '🏢',
        ])
    </div>
@endsection
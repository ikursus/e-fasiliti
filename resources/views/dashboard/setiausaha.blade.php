@extends('layouts.app')

@section('title', 'Papan Pemuka · Setiausaha')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Selamat kembali, {{ auth()->user()->name }}.</h2>
                <p class="mt-1 text-sm text-slate-500">Urus tempahan bilik bagi pihak unit dan tugas sokongan mesyuarat.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Setiausaha</span>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        @include('components.partials.empty-state', [
            'title' => 'Tempahan unit',
            'description' => 'Kalendar penuh dan tempahan bagi pihak pegawai akan muncul di sini dalam Fasa 1.',
            'icon' => '📅',
        ])

        @include('components.partials.empty-state', [
            'title' => 'Sokongan mesyuarat',
            'description' => 'Permintaan susun atur dan minuman akan diuruskan di sini dalam Fasa 3.',
            'icon' => '☕',
        ])
    </div>
@endsection
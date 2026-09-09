@extends('layouts.app')

@section('title', 'Papan Pemuka · Juruteknik ICT')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Selamat kembali, {{ auth()->user()->name }}.</h2>
                <p class="mt-1 text-sm text-slate-500">Tugasan pembaikan anda dan baki masa SLA.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Juruteknik ICT</span>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        @include('components.partials.empty-state', [
            'title' => 'Tugasan hari ini',
            'description' => 'Senarai tugasan ditugaskan kepada anda akan muncul di sini dalam Fasa 2.',
            'icon' => '🔧',
        ])

        @include('components.partials.empty-state', [
            'title' => 'Imbas kod QR aset',
            'description' => 'Buka rekod aset cepat dengan kamera telefon. Dalam Fasa 3.',
            'icon' => '▦',
        ])
    </div>
@endsection
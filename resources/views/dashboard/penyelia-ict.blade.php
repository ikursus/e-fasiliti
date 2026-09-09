@extends('layouts.app')

@section('title', 'Papan Pemuka · Penyelia ICT')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Selamat kembali, {{ auth()->user()->name }}.</h2>
                <p class="mt-1 text-sm text-slate-500">Beban kerja juruteknik, status tiket dan pemantauan SLA.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Penyelia ICT</span>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        @include('components.partials.empty-state', [
            'title' => 'Tiket &amp; SLA',
            'description' => 'Tiket hampir melanggar SLA dan beban kerja juruteknik akan dipantau di sini dalam Fasa 2.',
            'icon' => '📟',
        ])

        @include('components.partials.empty-state', [
            'title' => 'Agihan tugasan',
            'description' => 'Pengagihan tugasan kepada juruteknik diuruskan di sini dalam Fasa 2.',
            'icon' => '⇄',
        ])
    </div>
@endsection
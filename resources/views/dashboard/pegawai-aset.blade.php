@extends('layouts.app')

@section('title', 'Papan Pemuka · Pegawai Aset')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Selamat kembali, {{ auth()->user()->name }}.</h2>
                <p class="mt-1 text-sm text-slate-500">Inventari aset ICT, vendor, waranti dan sejar</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Pegawai Aset</span>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        @include('components.partials.empty-state', [
            'title' => 'Inventari aset',
            'description' => 'Daftar aset, lokasi, pemilik dan sejar pergerakan akan ditapaparkan di sini dalam Fasa 2.',
            'icon' => '💻',
        ])

        @include('components.partials.empty-state', [
            'title' => 'Vendor &amp; waranti',
            'description' => 'Rekod vendor, kontrak dan waranti diuruskan di sini dalam Fasa 3.',
            'icon' => '🤝',
        ])
    </div>
@endsection
@extends('layouts.app')

@section('title', 'Papan Pemuka · Pentadbir Sistem')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Selamat kembali, {{ auth()->user()->name }}.</h2>
                <p class="mt-1 text-sm text-slate-500">Ringkasan platform: pengguna, peranan, lokasi dan struktur organisasi.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Pentadbir Sistem</span>
        </div>
    </div>

    @php
        $userTotal = App\Models\User::count();
        $userActive = App\Models\User::where('is_active', true)->count();
        $roleTotal = Spatie\Permission\Models\Role::count();
        $locationTotal = App\Models\Location::count();
    @endphp

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Jumlah Pengguna</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $userTotal }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Akaun Aktif</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $userActive }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Peranan</p>
            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $roleTotal }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Lokasi Berdaftar</p>
            <p class="mt-2 text-3xl font-bold text-slate-600">{{ $locationTotal }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800">Kebenaran</h3>
                <a href="{{ route('admin.roles.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">Urus peranan &rarr;</a>
            </div>
            <p class="mt-2 text-sm text-slate-500">Tambah peranan baharu atau sunting kebenaran modul daripada pangkalan data.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800">Pengguna</h3>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">Urus pengguna &rarr;</a>
            </div>
            <p class="mt-2 text-sm text-slate-500">Pembuka akaun, penataskan peranan, dan nyahaktif akaun tanpa memadam rekod.</p>
        </div>
    </div>
@endsection
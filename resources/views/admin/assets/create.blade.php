@extends('layouts.app')

@section('title', 'Daftar Aset · Inventori')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-900">Daftar Aset Baharu</h2>
        <p class="mt-1 text-sm text-slate-500">Medan bertanda * wajib diisi (FR-AST-01). No. pendaftaran mestilah unik.</p>
    </div>

    <form method="POST" action="{{ route('admin.assets.store') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.assets._form', ['statuses' => $statuses])
    </form>
@endsection
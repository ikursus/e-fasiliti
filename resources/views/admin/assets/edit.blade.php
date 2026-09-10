@extends('layouts.app')

@section('title', 'Kemas Kini Aset · Inventori')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-900">Kemas Kini Aset</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $asset->registration_number }} — {{ $asset->brand }} {{ $asset->model }}</p>
    </div>

    <form method="POST" action="{{ route('admin.assets.update', $asset) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.assets._form', ['statuses' => $statuses])
    </form>
@endsection
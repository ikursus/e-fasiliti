@extends('layouts.app')

@section('title', 'Sunting Lokasi · Pentadbiran')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Sunting Lokasi</h2>
    <p class="mb-4 text-sm text-slate-500">{{ $location->fullPath() }}</p>

    <form method="POST" action="{{ route('admin.locations.update', $location) }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.locations._form')
    </form>
@endsection

@extends('layouts.app')

@section('title', 'Sunting Unit · Pentadbiran')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Sunting Unit Organisasi</h2>
    <p class="mb-4 text-sm text-slate-500">{{ $unit->fullPath() }}</p>

    <form method="POST" action="{{ route('admin.organization-units.update', $unit) }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.organization-units._form')
    </form>
@endsection

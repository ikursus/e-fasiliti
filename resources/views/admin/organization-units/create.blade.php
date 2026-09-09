@extends('layouts.app')

@section('title', 'Unit Baharu · Pentadbiran')

@section('content')
    <h2 class="mb-4 text-xl font-bold text-slate-900">Unit Organisasi Baharu</h2>

    <form method="POST" action="{{ route('admin.organization-units.store') }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.organization-units._form')
    </form>
@endsection

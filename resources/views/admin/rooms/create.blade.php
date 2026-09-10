@extends('layouts.app')

@section('title', 'Bilik Baharu · Pentadbiran')

@section('content')
    <h2 class="mb-4 text-xl font-bold text-slate-900">Bilik Baharu</h2>

    <form method="POST" action="{{ route('admin.rooms.store') }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.rooms._form')
    </form>
@endsection

@extends('layouts.app')

@section('title', 'Sunting Bilik · Pentadbiran')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Sunting Bilik</h2>
    <p class="mb-4 text-sm text-slate-500">{{ $room->locationFullPath() }}</p>

    <form method="POST" action="{{ route('admin.rooms.update', $room) }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.rooms._form')
    </form>
@endsection

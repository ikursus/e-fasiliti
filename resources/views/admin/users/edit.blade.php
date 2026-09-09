@extends('layouts.app')

@section('title', 'Edit Pengguna · Pentadbiran')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-900">Edit Pengguna</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $user->name }} · {{ $user->email }}</p>
    </div>

    @include('admin.users._form', [
        'action' => route('admin.users.update', $user),
        'method' => 'PATCH',
        'submitText' => 'Simpan Perubahan',
        'user' => $user,
        'roles' => $roles,
        'organizationUnits' => $organizationUnits,
    ])

    <div class="mt-8 rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-rose-700">Zon Bahaya</h3>
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
            onsubmit="return confirm('Padam akaun ini secara kekal? Tindakan ini tidak boleh dibatalkan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="mt-3 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                Padam Akaun
            </button>
        </form>
    </div>
@endsection
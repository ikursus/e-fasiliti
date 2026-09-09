@extends('layouts.app')

@section('title', 'Pengguna Baharu · Pentadbiran')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-900">Pengguna Baharu</h2>
        <p class="mt-1 text-sm text-slate-500">Pembuka akaun pengguna dan penataskan peranan.</p>
    </div>

    @include('admin.users._form', [
        'action' => route('admin.users.store'),
        'submitText' => 'Pembuka Akaun',
        'user' => null,
        'roles' => $roles,
        'organizationUnits' => $organizationUnits,
    ])
@endsection
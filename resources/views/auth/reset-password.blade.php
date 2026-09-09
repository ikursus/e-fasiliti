@extends('layouts.guest')

@section('title', 'Tetapkan Kata Laluan')

@section('content')
    <h1 class="text-xl font-bold text-slate-900">Tetapkan kata laluan baharu</h1>
    <p class="mt-1 text-sm text-slate-600">Sila pilih kata laluan kukuh yang belum digunakan sebelum ini.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Emel</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->input('email')) }}"
                autocomplete="email"
                required
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            >
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Kata Laluan</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="new-password"
                required
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            >
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Sahkan Kata Laluan</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                required
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            >
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Tetapkan kata laluan
        </button>
    </form>
@endsection
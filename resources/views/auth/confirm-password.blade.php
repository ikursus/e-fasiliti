@extends('layouts.guest')

@section('title', 'Sahkan Kata Laluan')

@section('content')
    <h1 class="text-xl font-bold text-slate-900">Sahkan kata laluan</h1>
    <p class="mt-1 text-sm text-slate-600">Ini adalah kawasan sensitif. Silakan masukkan kata laluan anda untuk meneruskan.</p>

    <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Kata Laluan</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
                autofocus
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            >
            @error('password')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Sahkan
        </button>
    </form>
@endsection
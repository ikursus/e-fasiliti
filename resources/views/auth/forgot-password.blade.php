@extends('layouts.guest')

@section('title', 'Lupa Kata Laluan')

@section('content')
    <h1 class="text-xl font-bold text-slate-900">Tetapkan semula kata laluan</h1>
    <p class="mt-1 text-sm text-slate-600">
        Masukkan emel anda. Kami akan menghantar pautan untuk menetapkan semula kata laluan.
    </p>

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Emel</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
                autofocus
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            >
            @error('email')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Hantar pautan tetapan
        </button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:underline">Kembali ke log masuk</a>
        </p>
    </form>
@endsection
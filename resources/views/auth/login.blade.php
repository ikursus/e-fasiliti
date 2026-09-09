@extends('layouts.guest')

@section('title', 'Log Masuk')

@section('content')
    <h1 class="text-xl font-bold text-slate-900 text-center">Log Masuk</h1>

    <p class="mt-1 text-sm text-slate-500">Masukkan emel dan kata laluan anda untuk meneruskan.</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4" novalidate>
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
                class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
            >
        </div>

        <div x-data="{ show: false }">
            <div class="flex items-center justify-between">
                <label for="password" class="text-sm font-medium text-slate-700">Kata Laluan</label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-indigo-600 hover:underline">
                    Lupa kata laluan?
                </a>
            </div>
            <div class="relative mt-1">
                <input
                    id="password"
                    x-bind:type="show ? 'text' : 'password'"
                    name="password"
                    autocomplete="current-password"
                    required
                    class="block w-full rounded-lg border-slate-300 px-3 py-2.5 pr-11 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                >
                <button
                    type="button"
                    x-on:click="show = ! show"
                    x-bind:aria-pressed="show"
                    aria-label="Tunjuk atau sorok kata laluan"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 transition hover:text-slate-600 focus:outline-none focus:text-indigo-600"
                >
                    <!-- Mata terbuka (kata laluan disorok) -->
                    <svg x-show="! show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <!-- Mata tertutup (kata laluan ditunjuk) -->
                    <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                    </svg>
                </button>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input
                type="checkbox"
                name="remember"
                value="1"
                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            >
            Ingat saya
        </label>

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Log Masuk
        </button>
    </form>
@endsection

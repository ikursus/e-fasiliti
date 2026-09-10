<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Papan Pemuka') — {{ config('app.name') }}</title>
    <style>[x-cloak]{display:none!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div class="min-h-screen lg:flex" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">

        {{-- Mobile backdrop: klik untuk tutup sidebar --}}
        <div
            x-cloak
            x-show="sidebarOpen"
            x-transition.opacity.duration.200ms
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-300 transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center gap-3 border-b border-slate-800 px-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m4.5 3h1.5m-1.5-9h1.5m-1.5 3h1.5m-1.5 3h1.5"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-white">e-Fasiliti</p>
                    <p class="text-[11px] font-medium text-slate-400">SPFA</p>
                </div>
                {{-- Butang tutup (mobile sahaja) --}}
                <button type="button"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden"
                    aria-label="Tutup navigasi"
                    @click="sidebarOpen = false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <a href="{{ route('dashboard') }}"
                   @click="sidebarOpen = false"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    Papan Pemuka
                </a>

                @canany(['tiket.lihat-sendiri', 'tiket.kemas-kini', 'tiket.lihat-semua'])
                    <p class="mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Tiket ICT</p>
                @endcanany

                @can('tiket.lihat-sendiri')
                    <a href="{{ route('tiket.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('tiket.index') || request()->routeIs('tiket.create') || request()->routeIs('tiket.show') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Aduan Saya
                    </a>
                @endcan

                @can('tiket.kemas-kini')
                    <a href="{{ route('tiket.tugasan') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('tiket.tugasan') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Tugasan Saya
                    </a>
                @endcan

                @can('tiket.lihat-semua')
                    <a href="{{ route('tiket.senarai') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('tiket.senarai') || request()->routeIs('tiket.agih*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Semua Tiket
                    </a>
                @endcan

                @canany(['lokasi.lihat', 'unit-organisasi.lihat'])
                    <p class="mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Direktori</p>
                @endcanany

                @can('lokasi.lihat')
                    <a href="{{ route('admin.locations.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.locations*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Lokasi
                    </a>
                @endcan

                @can('unit-organisasi.lihat')
                    <a href="{{ route('admin.organization-units.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.organization-units*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Unit Organisasi
                    </a>
                @endcan

                @can('tetapan.lihat')
                    <a href="{{ route('admin.settings.general') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.settings*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Konfigurasi
                    </a>
                @endcan

                @if (auth()->user()->isSuperAdmin())
                    <p class="mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Pentadbiran</p>
                    <a href="{{ route('admin.users.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Pengguna
                    </a>
                    <a href="{{ route('admin.roles.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.roles*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Peranan &amp; Kebenaran
                    </a>

                    <a href="{{ route('log-viewer.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('log-viewer*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Log Sistem
                    </a>
                @endif

                <p class="mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Modul (akan datang)</p>
                <p class="px-3 py-2 text-sm text-slate-500">Tempahan Bilik Mesyuarat</p>
                <p class="px-3 py-2 text-sm text-slate-500">Aduan &amp; Tiket ICT</p>
                <p class="px-3 py-2 text-sm text-slate-500">Inventari Aset</p>
            </nav>

            <div class="border-t border-slate-800 px-5 py-4">
                <p class="text-xs text-slate-400">Daftar masuk</p>
                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
            </div>
        </aside>

        {{-- Main column --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
                <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" type="button" aria-label="Buka navigasi" @click="sidebarOpen = !sidebarOpen">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-8.25 5.25h8.25"/>
                    </svg>
                </button>

                <h1 class="truncate text-lg font-semibold text-slate-900">@yield('title', 'Papan Pemuka')</h1>

                <div class="ml-auto flex items-center gap-3">
                    <div class="relative" x-data="{ open: false }">
                        <button type="button"
                            class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                            @click="open = !open">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">
                                {{ mb_substr(auth()->user()->name, 0, 1) }}
                            </span>
                            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>

                        <div x-show="open" x-transition @click.outside="open = false"
                            class="absolute right-0 z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                            <p class="px-4 py-2 text-xs text-slate-500">
                                Masuk sebagai <span class="font-semibold text-slate-700">{{ auth()->user()->email }}</span>
                            </p>
                            <div class="border-t border-slate-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Log Keluar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="border-t border-slate-200 px-6 py-4 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} e-Fasiliti — SPFA
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
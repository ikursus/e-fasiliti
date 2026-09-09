<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Log Masuk') — {{ config('app.name') }}</title>
    <style>[x-cloak]{display:none!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-8 flex flex-col items-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-8 w-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m4.5 3h1.5m-1.5-9h1.5m-1.5 3h1.5m-1.5 3h1.5"/>
                    </svg>
                </div>
                <p class="mt-3 text-xl font-bold text-slate-900">e-Fasiliti</p>
                <p class="text-sm text-slate-500">Sistem Pengurusan Fasiliti &amp; Aset ICT</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} — e-Fasiliti. Hak cipta terpelihara.
            </p>
        </div>
    </main>
</body>
</html>
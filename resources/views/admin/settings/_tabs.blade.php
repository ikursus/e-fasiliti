@php
    // Route::has guards every entry, so a tab appears only once its task has
    // shipped. Check-in and ticket priorities are phase 2 and 3 work and are
    // deliberately not built yet; they stay listed so they slot in later.
    $tabs = [
        ['route' => 'admin.settings.general', 'label' => 'Umum', 'pattern' => 'admin.settings.general*'],
        ['route' => 'admin.settings.operating-hours', 'label' => 'Waktu Operasi', 'pattern' => 'admin.settings.operating-hours*'],
        ['route' => 'admin.settings.holidays', 'label' => 'Cuti Umum', 'pattern' => 'admin.settings.holidays*'],
        ['route' => 'admin.settings.booking-rules', 'label' => 'Peraturan Tempahan', 'pattern' => 'admin.settings.booking-rules*'],
        ['route' => 'admin.settings.checkin', 'label' => 'Daftar Masuk', 'pattern' => 'admin.settings.checkin*'],
        ['route' => 'admin.settings.ticket-priorities', 'label' => 'Keutamaan Tiket', 'pattern' => 'admin.settings.ticket-priorities*'],
        ['route' => 'admin.settings.reference-values', 'label' => 'Nilai Rujukan', 'pattern' => 'admin.settings.reference-values*'],
        ['route' => 'admin.settings.notification-templates', 'label' => 'Templat Notifikasi', 'pattern' => 'admin.settings.notification-templates*'],
    ];
@endphp

<nav class="mb-6 flex flex-wrap gap-1 border-b border-slate-200" aria-label="Tab konfigurasi">
    @foreach ($tabs as $tab)
        @if (Route::has($tab['route']))
            <a href="{{ route($tab['route']) }}"
               class="-mb-px border-b-2 px-3 py-2 text-sm font-medium {{ request()->routeIs($tab['pattern']) ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                {{ $tab['label'] }}
            </a>
        @endif
    @endforeach

    {{-- M17 — stricter permission than the other tabs, so it sits outside --}}
    {{-- the shared $tabs list and renders only for its own audience. --}}
    @can('chatbot.tetapan')
        @if (Route::has('admin.settings.chatbot'))
            <a href="{{ route('admin.settings.chatbot') }}"
               class="-mb-px border-b-2 px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.settings.chatbot*') ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                Chatbot
            </a>
        @endif
    @endcan
</nav>

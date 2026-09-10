@extends('layouts.app')

@section('title', 'Tugasan Saya')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Tugasan Saya</h2>
        <p class="mt-1 text-sm text-slate-500">Disusun mengikut keutamaan, kemudian baki masa SLA (UR-23).</p>
    </div>

    @if ($tickets->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white px-6 py-16 text-center">
            <p class="text-sm font-medium text-slate-900">Tiada tugasan untuk hari ini.</p>
            <p class="mt-1 text-sm text-slate-500">Semak semula kemudian atau lihat semua tiket unit.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($tickets as $ticket)
                <a href="{{ route('tiket.show', $ticket) }}"
                   class="block rounded-xl border border-slate-200 bg-white p-4 hover:border-indigo-300 hover:shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold text-indigo-600">{{ $ticket->no_tiket }}</span>
                                <x-ticket-priority-badge :keutamaan="$ticket->keutamaan" />
                                <x-ticket-status-badge :status="$ticket->status" />
                            </div>
                            <p class="mt-1 truncate text-sm text-slate-700">{{ $ticket->kategoriLabel() }} — {{ $ticket->lokasi?->fullPath() }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">Pelapor: {{ $ticket->pelapor?->name ?? '—' }} · Sasaran {{ $ticket->sasaran_pemulihan->format('d/m H:i') }}</p>
                        </div>
                        @php $peratus = $peratus[$ticket->id] ?? 0; @endphp
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $peratus >= 100 ? 'bg-rose-100 text-rose-700' : ($peratus >= 80 ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600') }}">
                            SLA {{ min($peratus, 999) }}%
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
@endsection

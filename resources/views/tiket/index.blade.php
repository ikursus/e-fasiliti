@extends('layouts.app')

@section('title', 'Aduan Saya')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Aduan Saya</h2>
            <p class="mt-1 text-sm text-slate-500">Status aduan kerosakan peralatan yang anda laporkan.</p>
        </div>

        @can('tiket.buka')
            <a href="{{ route('tiket.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                Lapor Kerosakan
            </a>
        @endcan
    </div>

    @php $jumlahTerbuka = array_sum($terbuka); @endphp

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">Terbuka</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $jumlahTerbuka }}</p>
        </div>
        @foreach ($terbuka as $statusNilai => $bilangan)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">
                    {{ \App\Enums\TicketStatus::from($statusNilai)->label() }}
                </p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $bilangan }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        @if ($tickets->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="text-sm font-medium text-slate-900">Anda tidak mempunyai aduan aktif.</p>
                <p class="mt-1 text-sm text-slate-500">Jika peralatan anda bermasalah, laporkan di sini.</p>
                @can('tiket.buka')
                    <a href="{{ route('tiket.create') }}"
                       class="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Lapor Kerosakan
                    </a>
                @endcan
            </div>
        @else
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-4 py-3">No. Tiket</th>
                        <th scope="col" class="px-4 py-3">Kategori</th>
                        <th scope="col" class="px-4 py-3">Lokasi</th>
                        <th scope="col" class="px-4 py-3">Keutamaan</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                        <th scope="col" class="px-4 py-3">Juruteknik</th>
                        <th scope="col" class="px-4 py-3">Anggaran Selesai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($tickets as $ticket)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-indigo-600">
                                <a href="{{ route('tiket.show', $ticket) }}">{{ $ticket->no_tiket }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $ticket->kategori_masalah }}</td>
                            <td class="px-4 py-3">{{ $ticket->lokasi?->fullPath() }}</td>
                            <td class="px-4 py-3">
                                <x-ticket-priority-badge :keutamaan="$ticket->keutamaan" />
                            </td>
                            <td class="px-4 py-3">
                                <x-ticket-status-badge :status="$ticket->status" />
                            </td>
                            <td class="px-4 py-3">{{ $ticket->juruteknik?->name ?? 'Belum agih' }}</td>
                            <td class="px-4 py-3">
                                {{ $ticket->status->isOpen() ? $ticket->sasaran_pemulihan->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
@endsection

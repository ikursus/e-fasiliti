@extends('layouts.app')

@section('title', 'Semua Tiket')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Semua Tiket</h2>
        <p class="mt-1 text-sm text-slate-500">Papan tugas agihan dan pemantauan SLA unit ICT (UR-26, UR-27).</p>
    </div>

    {{-- Ringkasan status --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach ($ringkasan as $statusNilai => $bilangan)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">{{ \App\Enums\TicketStatus::from($statusNilai)->label() }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $bilangan }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            {{-- Penapis --}}
            <form method="GET" action="{{ route('tiket.senarai') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4">
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-600">Status</label>
                    <select id="status" name="status" class="mt-1 block rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua</option>
                        @foreach ($status as $statusItem)
                            <option value="{{ $statusItem->value }}" @selected($filters['status'] === $statusItem->value)>{{ $statusItem->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="keutamaan" class="block text-xs font-semibold text-slate-600">Keutamaan</label>
                    <select id="keutamaan" name="keutamaan" class="mt-1 block rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua</option>
                        @foreach ($keutamaan as $keutamaanItem)
                            <option value="{{ $keutamaanItem->value }}" @selected($filters['keutamaan'] === $keutamaanItem->value)>{{ $keutamaanItem->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="juruteknik_id" class="block text-xs font-semibold text-slate-600">Juruteknik</label>
                    <select id="juruteknik_id" name="juruteknik_id" class="mt-1 block rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua</option>
                        @foreach ($juruteknik as $teknik)
                            <option value="{{ $teknik->id }}" @selected($filters['juruteknik_id'] === $teknik->id)>{{ $teknik->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Tapis</button>
            </form>

            @if ($tickets->isEmpty())
                <div class="rounded-xl border border-slate-200 bg-white px-6 py-16 text-center">
                    <p class="text-sm font-medium text-slate-900">Tiada tiket menepati penapis.</p>
                </div>
            @else
                <form method="POST" action="{{ route('tiket.agih-pukal') }}">
                    @csrf

                    @can('tiket.agih')
                        <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white p-4">
                            <label for="pukal_juruteknik" class="text-sm font-semibold text-slate-700">Agih tiket bertanda kepada:</label>
                            <select id="pukal_juruteknik" name="juruteknik_id" required class="block rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Pilih juruteknik —</option>
                                @foreach ($juruteknik as $teknik)
                                    <option value="{{ $teknik->id }}">{{ $teknik->name }}</option>
                                @endforeach
                            </select>
                            @error('juruteknik_id') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                            @error('ids') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                Agih Pukal
                            </button>
                        </div>
                    @endcan

                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                                <tr>
                                    @can('tiket.agih')
                                        <th scope="col" class="px-3 py-3"><span class="sr-only">Pilih</span></th>
                                    @endcan
                                    <th scope="col" class="px-3 py-3">No. Tiket</th>
                                    <th scope="col" class="px-3 py-3">Keutamaan</th>
                                    <th scope="col" class="px-3 py-3">Status</th>
                                    <th scope="col" class="px-3 py-3">Lokasi</th>
                                    <th scope="col" class="px-3 py-3">Juruteknik</th>
                                    <th scope="col" class="px-3 py-3">SLA</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($tickets as $ticket)
                                    <tr class="hover:bg-slate-50">
                                        @can('tiket.agih')
                                            <td class="px-3 py-3">
                                                @if ($ticket->status->isOpen())
                                                    <input type="checkbox" name="ids[]" value="{{ $ticket->id }}" class="text-indigo-600 focus:ring-indigo-500">
                                                @endif
                                            </td>
                                        @endcan
                                        <td class="px-3 py-3 font-semibold text-indigo-600">
                                            <a href="{{ route('tiket.show', $ticket) }}">{{ $ticket->no_tiket }}</a>
                                        </td>
                                        <td class="px-3 py-3"><x-ticket-priority-badge :keutamaan="$ticket->keutamaan" /></td>
                                        <td class="px-3 py-3"><x-ticket-status-badge :status="$ticket->status" /></td>
                                        <td class="px-3 py-3">{{ $ticket->lokasi?->fullPath() }}</td>
                                        <td class="px-3 py-3">{{ $ticket->juruteknik?->name ?? 'Belum agih' }}</td>
                                        <td class="px-3 py-3">
                                            @if (isset($peratusSla[$ticket->id]))
                                                @php $peratus = $peratusSla[$ticket->id]; @endphp
                                                <span class="font-semibold {{ $peratus >= 100 ? 'text-rose-600' : ($peratus >= 80 ? 'text-orange-600' : 'text-slate-600') }}">
                                                    {{ min($peratus, 999) }}%
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif

            <div class="mt-4">{{ $tickets->links() }}</div>
        </div>

        {{-- Beban kerja juruteknik --}}
        <div>
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="text-sm font-bold text-slate-900">Beban Kerja Juruteknik</h3>
                <p class="mt-1 text-xs text-slate-500">Bilangan tiket terbuka setiap orang (FR-TKT-10).</p>
                <ul class="mt-4 space-y-3">
                    @forelse ($beban as $bebanItem)
                        <li>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $bebanItem['nama'] }}</span>
                                <span class="font-bold text-slate-900">{{ $bebanItem['terbuka'] }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-slate-100">
                                @php $maks = max(1, max(array_column($beban, 'terbuka'))); @endphp
                                <div class="h-2 rounded-full bg-indigo-500" style="width: {{ (int) round(($bebanItem['terbuka'] / $maks) * 100) }}%"></div>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Tiada juruteknik berdaftar.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', $asset->registration_number.' · Inventori')

@section('content')
    @php
        $describe = function (array $snapshot) use ($locationNames, $userNames): string {
            if (array_key_exists('location_id', $snapshot)) {
                return 'Lokasi: '.($locationNames[$snapshot['location_id']] ?? '—');
            }

            if (array_key_exists('responsible_user_id', $snapshot)) {
                return 'Pemilik: '.($snapshot['responsible_user_id'] === null
                    ? 'Tiada (simpanan)'
                    : ($userNames[$snapshot['responsible_user_id']] ?? $snapshot['responsible_user_id']));
            }

            return 'Status: '.($snapshot['status'] ?? '—');
        };
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ $asset->registration_number }}</h2>
            <p class="mt-1 flex items-center gap-2 text-sm text-slate-500">
                {{ $asset->brand }} {{ $asset->model }}
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $asset->status->badge() }}">{{ $asset->status->label() }}</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.assets.index') }}" class="text-sm font-medium text-slate-500 hover:underline">← Senarai</a>
            @can('aset.kemaskini')
                <a href="{{ route('admin.assets.edit', $asset) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Edit</a>
            @endcan
            @can('aset.padam')
                <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}" onsubmit="return confirm('Padam {{ $asset->registration_number }} secara kekal? Sejarah akan turut dipadam.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">Padam</button>
                </form>
            @endcan
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Maklumat Aset</h3>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                <div><dt class="text-xs font-medium text-slate-500">Kategori</dt><dd class="text-sm text-slate-800">{{ $asset->category?->label ?? '—' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">No. Siri</dt><dd class="font-mono text-sm text-slate-800">{{ $asset->serial_number ?? '—' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">Jenama / Model</dt><dd class="text-sm text-slate-800">{{ $asset->brand }} {{ $asset->model }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">Tarikh Perolehan</dt><dd class="text-sm text-slate-800">{{ $asset->acquisition_date?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">Harga Perolehan</dt><dd class="text-sm text-slate-800">{{ $asset->acquisition_cost !== null ? 'RM '.number_format((float) $asset->acquisition_cost, 2) : '—' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">No. Pesanan</dt><dd class="text-sm text-slate-800">{{ $asset->order_number ?? '—' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">Lokasi Semasa</dt><dd class="text-sm text-slate-800">{{ $asset->location?->name ?? '—' }}{{ $asset->location?->code ? ' ('.$asset->location->code.')' : '' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">Pengguna Bertanggungjawab</dt><dd class="text-sm text-slate-800">{{ $asset->responsibleUser?->name ?? '— (dalam simpanan)' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">Alamat MAC</dt><dd class="font-mono text-sm text-slate-800">{{ $asset->mac_address ?? '—' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">Alamat IP</dt><dd class="font-mono text-sm text-slate-800">{{ $asset->ip_address ?? '—' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">Nama Hos</dt><dd class="text-sm text-slate-800">{{ $asset->hostname ?? '—' }}</dd></div>
                <div><dt class="text-xs font-medium text-slate-500">Token QR</dt><dd class="font-mono text-xs text-slate-500">{{ $asset->qr_code }}</dd></div>
                @if ($asset->notes)
                    <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Catatan</dt><dd class="text-sm text-slate-800">{{ $asset->notes }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">Status Waranti</h3>
            @if ($warranty['active'] === null)
                <p class="text-sm text-slate-500">Tiada maklumat waranti.</p>
            @elseif ($warranty['active'])
                <p class="text-sm font-medium text-emerald-700">Masih dalam waranti</p>
                <p class="mt-1 text-sm text-slate-600">Hingga {{ $warranty['ends_at']->format('d/m/Y') }} ({{ $warranty['days_remaining'] }} hari lagi)</p>
            @else
                <p class="text-sm font-medium text-rose-700">Waranti telah tamat</p>
                <p class="mt-1 text-sm text-slate-600">Pada {{ $warranty['ends_at']->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-slate-200 bg-white shadow-sm">
        <h3 class="border-b border-slate-100 px-6 py-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Sejarah Pergerakan</h3>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Tarikh</th>
                    <th class="px-4 py-3">Peristiwa</th>
                    <th class="px-4 py-3">Butiran</th>
                    <th class="px-4 py-3">Sebab</th>
                    <th class="px-4 py-3">Direkod Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($histories as $history)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-600">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $history->event_type->label() }}</span></td>
                        <td class="px-4 py-3 text-slate-600">
                            @if ($history->before)
                                {{ ($describe)($history->before) }} → {{ ($describe)($history->after ?? []) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $history->reason ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $history->recorder?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Tiada sejarah pergerakan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
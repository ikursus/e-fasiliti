@extends('layouts.app')

@section('title', 'Sahkan Nyahaktif · Pentadbiran')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Nyahaktif bilik?</h2>
    <p class="mb-4 text-sm text-slate-500">{{ $room->name }} ({{ $room->code }}) · {{ $room->locationFullPath() }}</p>

    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Bilik ini mempunyai <strong>{{ $affected->count() }} tempahan akan datang</strong>.
        Menyahaktifkan bilik menyembunyikannya daripada tempahan baharu, tetapi tempahan di bawah
        kekal dan perlu dibatalkan secara berasingan.
    </div>

    <div class="mb-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Rujukan</th>
                    <th class="px-4 py-3">Tajuk</th>
                    <th class="px-4 py-3">Masa</th>
                    <th class="px-4 py-3">Pemilik</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($affected as $booking)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $booking->reference_no }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $booking->title }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $booking->starts_at->translatedFormat('d M Y, H:i') }} – {{ $booking->ends_at->format('H:i') }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $booking->owner?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $booking->status->label() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex items-center gap-3">
        <form method="POST" action="{{ route('admin.rooms.toggle', $room) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="confirm" value="1">
            <button type="submit"
                    class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500">
                Ya, nyahaktifkan bilik ini
            </button>
        </form>
        <a href="{{ route('admin.rooms.index') }}" class="text-sm font-medium text-slate-500 hover:underline">Batal</a>
    </div>
@endsection

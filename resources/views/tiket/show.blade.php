@extends('layouts.app')

@section('title', $ticket->no_tiket)

@section('content')
    <div class="mx-auto max-w-4xl">
        <a href="{{ $adalahJuruteknik && !$adalahPelapor ? route('tiket.tugasan') : route('tiket.index') }}"
           class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            &larr; Kembali
        </a>

        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $ticket->no_tiket }}</h2>
                <p class="mt-1 text-sm text-slate-500">Dibuka {{ $ticket->masa_dibuka->format('d/m/Y H:i') }} oleh {{ $ticket->pelapor->name }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ticket-priority-badge :keutamaan="$ticket->keutamaan" />
                <x-ticket-status-badge :status="$ticket->status" />
            </div>
        </div>

        {{-- Ringkasan SLA --}}
        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">Sasaran pemulihan</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->sasaran_pemulihan->format('d/m/Y, H:i') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">Juruteknik</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->juruteknik?->name ?? 'Belum diagihkan' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">Masa SLA terpakai</p>
                @if (is_null($peratusSla))
                    <p class="mt-1 text-sm font-semibold text-slate-900">—</p>
                @else
                    <p class="mt-1 text-sm font-semibold {{ $peratusSla >= 100 ? 'text-rose-600' : ($peratusSla >= 80 ? 'text-orange-600' : 'text-slate-900') }}">
                        {{ min($peratusSla, 999) }}%{{ $peratusSla >= 100 ? ' (dilanggar)' : '' }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Butiran aduan --}}
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6">
            <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium text-slate-500">Kategori masalah</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-slate-900">{{ $ticket->kategoriLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">Lokasi</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-slate-900">{{ $ticket->lokasi?->fullPath() }}</dd>
                </div>
                @if ($ticket->telefon_hubungan)
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Telefon pelapor</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900">{{ $ticket->telefon_hubungan }}</dd>
                    </div>
                @endif
                @if ($ticket->status === \App\Enums\TicketStatus::MenungguVendor)
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Vendor</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900">
                            {{ $ticket->vendor_nama }} ({{ $ticket->no_rujukan_vendor }})
                        </dd>
                    </div>
                @endif
                @if ($ticket->sebab_keutamaan)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-slate-500">Sebab keutamaan semasa</dt>
                        <dd class="mt-0.5 text-sm text-slate-700">{{ $ticket->sebab_keutamaan }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-4 border-t border-slate-100 pt-4">
                <p class="text-xs font-medium text-slate-500">Keterangan masalah</p>
                <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $ticket->keterangan }}</p>
            </div>

            @if (filled($ticket->lampiran))
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <p class="text-xs font-medium text-slate-500">Lampiran</p>
                    <ul class="mt-1 space-y-1">
                        @foreach ($ticket->lampiran as $indeks => $fail)
                            <li>
                                <a href="{{ route('tiket.lampiran', [$ticket, $indeks]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    {{ $fail['name'] ?? 'Lampiran '.($indeks + 1) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (filled($ticket->diagnosis) || filled($ticket->tindakan))
                <div class="mt-4 grid gap-4 border-t border-slate-100 pt-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Diagnosis</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $ticket->diagnosis }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500">Tindakan pembaikan</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $ticket->tindakan }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Tindakan pelapor --}}
        @if ($adalahPelapor && $ticket->status === \App\Enums\TicketStatus::MenungguPengesahan)
            <div class="mb-6 rounded-xl border border-violet-200 bg-violet-50 p-6">
                <h3 class="text-sm font-bold text-violet-900">Sahkan penutupan tiket</h3>
                <p class="mt-1 text-sm text-violet-700">Juruteknik telah menanda kerja selesai. Sila sahkan sama ada masalah benar-benar selesai.</p>
                <div class="mt-4 flex flex-wrap items-start gap-3">
                    <form method="POST" action="{{ route('tiket.sahkan', $ticket) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                            Sah Selesai
                        </button>
                    </form>
                    <form method="POST" action="{{ route('tiket.buka-semula', $ticket) }}" class="flex-1 space-y-2">
                        @csrf
                        <input type="text" name="alasan" required maxlength="500" placeholder="Nyatakan masalah yang masih berlaku"
                               class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('alasan') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        <button type="submit" class="rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 ring-1 ring-inset ring-rose-300 hover:bg-rose-50">
                            Masih Bermasalah
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Tindakan juruteknik: mula kerja --}}
        @if ($adalahJuruteknik && $ticket->status === \App\Enums\TicketStatus::Diagih)
            <form method="POST" action="{{ route('tiket.mula', $ticket) }}" class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-6">
                @csrf
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">
                    Mula Kerja
                </button>
            </form>
        @endif

        {{-- Tindakan juruteknik: rekod kerja --}}
        @if ($adalahJuruteknik && in_array($ticket->status, [\App\Enums\TicketStatus::DalamTindakan, \App\Enums\TicketStatus::MenungguVendor], true))
            <form method="POST" action="{{ route('tiket.kerja', $ticket) }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-6">
                @csrf
                @method('PUT')
                <h3 class="text-sm font-bold text-slate-900">Rekod kerja</h3>
                <div>
                    <label for="diagnosis" class="block text-sm font-semibold text-slate-700">Diagnosis <span class="text-rose-600">*</span></label>
                    <textarea id="diagnosis" name="diagnosis" rows="2" required
                              class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('diagnosis', $ticket->diagnosis) }}</textarea>
                    @error('diagnosis') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tindakan" class="block text-sm font-semibold text-slate-700">Tindakan pembaikan <span class="text-rose-600">*</span></label>
                    <textarea id="tindakan" name="tindakan" rows="2" required
                              class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('tindakan', $ticket->tindakan) }}</textarea>
                    @error('tindakan') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="kos_pembaikan" class="block text-sm font-semibold text-slate-700">Kos (RM)</label>
                        <input type="number" id="kos_pembaikan" name="kos_pembaikan" step="0.01" min="0" value="{{ old('kos_pembaikan', $ticket->kos_pembaikan) }}"
                               class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="masa_kerja_minit" class="block text-sm font-semibold text-slate-700">Masa kerja (minit)</label>
                        <input type="number" id="masa_kerja_minit" name="masa_kerja_minit" min="0" value="{{ old('masa_kerja_minit', $ticket->masa_kerja_minit) }}"
                               class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Simpan Rekod Kerja
                </button>
            </form>

            @if ($ticket->status === \App\Enums\TicketStatus::DalamTindakan)
                <div class="mb-6 grid gap-4 sm:grid-cols-2">
                    <form method="POST" action="{{ route('tiket.rujuk-vendor', $ticket) }}" class="space-y-3 rounded-xl border border-orange-200 bg-orange-50 p-6">
                        @csrf
                        <h3 class="text-sm font-bold text-orange-900">Rujuk kepada vendor</h3>
                        <p class="text-xs text-orange-700">Jam SLA akan dijeda sehingga kerja disambung semula.</p>
                        <input type="text" name="vendor_nama" required maxlength="200" placeholder="Nama vendor"
                               class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <input type="text" name="no_rujukan_vendor" required maxlength="50" placeholder="No. rujukan vendor"
                               class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('no_rujukan_vendor') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        <button type="submit" class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-500">
                            Rujuk Vendor
                        </button>
                    </form>

                    <form method="POST" action="{{ route('tiket.selesai', $ticket) }}" class="rounded-xl border border-emerald-200 bg-emerald-50 p-6">
                        @csrf
                        <h3 class="text-sm font-bold text-emerald-900">Kerja selesai</h3>
                        <p class="text-xs text-emerald-700">Permintaan pengesahan akan dihantar kepada pelapor.</p>
                        <button type="submit" class="mt-3 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            Tanda Kerja Selesai
                        </button>
                    </form>
                </div>
            @endif
        @endif

        {{-- Tindakan juruteknik: sambung selepas vendor dan catatan --}}
        @if ($adalahJuruteknik && $ticket->status === \App\Enums\TicketStatus::MenungguVendor)
            <form method="POST" action="{{ route('tiket.sambung-vendor', $ticket) }}" class="mb-6 rounded-xl border border-orange-200 bg-orange-50 p-6">
                @csrf
                <h3 class="text-sm font-bold text-orange-900">Vendor telah selesai</h3>
                <button type="submit" class="mt-2 rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-500">
                    Sambung Kerja
                </button>
            </form>
        @endif

        @if ($adalahJuruteknik && $ticket->status->isOpen() && $ticket->status !== \App\Enums\TicketStatus::MenungguPengesahan)
            <form method="POST" action="{{ route('tiket.catatan', $ticket) }}" class="mb-6 space-y-3 rounded-xl border border-slate-200 bg-white p-6">
                @csrf
                <h3 class="text-sm font-bold text-slate-900">Catatan kemajuan</h3>
                <textarea name="catatan" rows="2" required placeholder="Kemas kini kemajuan kerja..."
                          class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('catatan') }}</textarea>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="hidden" name="boleh_dilihat_pelapor" value="0">
                    <input type="checkbox" name="boleh_dilihat_pelapor" value="1" checked class="text-indigo-600 focus:ring-indigo-500">
                    Paparkan catatan ini kepada pelapor
                </label>
                @error('catatan') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    Tambah Catatan
                </button>
            </form>
        @endif

        {{-- Tindakan penyelia: agih, batal, keutamaan --}}
        @can('tiket.agih')
            @if ($ticket->status === \App\Enums\TicketStatus::Baharu)
                <div class="mb-6 grid gap-4 sm:grid-cols-2">
                    <form method="POST" action="{{ route('tiket.agih', $ticket) }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-6">
                        @csrf
                        <h3 class="text-sm font-bold text-slate-900">Agihkan kepada juruteknik</h3>
                        <select name="juruteknik_id" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Pilih juruteknik —</option>
                            @foreach ($juruteknik as $teknik)
                                <option value="{{ $teknik->id }}">{{ $teknik->name }}</option>
                            @endforeach
                        </select>
                        @error('juruteknik_id') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            Agih
                        </button>
                    </form>

                    <form method="POST" action="{{ route('tiket.batal', $ticket) }}" class="space-y-3 rounded-xl border border-rose-200 bg-rose-50 p-6">
                        @csrf
                        <h3 class="text-sm font-bold text-rose-900">Batalkan aduan (laporan tidak sah)</h3>
                        <input type="text" name="sebab_batal" required maxlength="500" placeholder="Sebab pembatalan"
                               class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('sebab_batal') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                            Batal Tiket
                        </button>
                    </form>
                </div>
            @endif
        @endcan

        @can('tiket.keutamaan')
            @if ($ticket->status->isOpen())
                <form method="POST" action="{{ route('tiket.keutamaan', $ticket) }}" class="mb-6 space-y-3 rounded-xl border border-slate-200 bg-white p-6">
                    @csrf
                    <h3 class="text-sm font-bold text-slate-900">Ubah keutamaan</h3>
                    <p class="text-xs text-slate-500">Sasaran SLA akan dikira semula daripada masa tiket dibuka.</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <select name="keutamaan" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (\App\Enums\TicketPriority::cases() as $pilihan)
                                <option value="{{ $pilihan->value }}" @selected($ticket->keutamaan === $pilihan)>{{ $pilihan->label() }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="sebab" required maxlength="500" placeholder="Sebab perubahan (wajib)"
                               class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    @error('sebab') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Kemas Kini Keutamaan
                    </button>
                </form>
            @endif
        @endcan

        {{-- Timeline catatan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="text-sm font-bold text-slate-900">Catatan kemajuan</h3>
            @if ($notes->isEmpty())
                <p class="mt-2 text-sm text-slate-500">Tiada catatan kemajuan lagi.</p>
            @else
                <ol class="mt-4 space-y-4">
                    @foreach ($notes as $note)
                        <li class="border-l-2 border-slate-200 pl-4">
                            <p class="text-xs text-slate-500">
                                {{ $note->pengguna?->name ?? 'Pengguna' }} · {{ $note->created_at->format('d/m/Y H:i') }}
                                @unless ($note->boleh_dilihat_pelapor)
                                    · <span class="font-semibold text-slate-400">dalaman</span>
                                @endunless
                            </p>
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $note->catatan }}</p>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>
@endsection

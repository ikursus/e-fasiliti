@extends('layouts.app')

@section('title', 'Lapor Kerosakan')

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('tiket.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            &larr; Kembali ke Aduan Saya
        </a>

        <h2 class="text-xl font-bold text-slate-900">Lapor Kerosakan Peralatan</h2>
        <p class="mt-1 text-sm text-slate-500">Lima medan sahaja. Anda akan menerima nombor rujukan serta-merta selepas menghantar.</p>

        <form method="POST" action="{{ route('tiket.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6 rounded-xl border border-slate-200 bg-white p-6">
            @csrf

            <div>
                <label for="lokasi_id" class="block text-sm font-semibold text-slate-700">Lokasi masalah <span class="text-rose-600">*</span></label>
                <select id="lokasi_id" name="lokasi_id" required
                        class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— Pilih lokasi —</option>
                    @foreach ($lokasi as $lokasiItem)
                        <option value="{{ $lokasiItem['id'] }}" @selected(old('lokasi_id') == $lokasiItem['id'])>
                            {{ $lokasiItem['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('lokasi_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="kategori_masalah" class="block text-sm font-semibold text-slate-700">Jenis masalah <span class="text-rose-600">*</span></label>
                <select id="kategori_masalah" name="kategori_masalah" required
                        class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— Pilih jenis masalah —</option>
                    @foreach ($kategori as $kategoriItem)
                        <option value="{{ $kategoriItem['code'] }}" @selected(old('kategori_masalah') === $kategoriItem['code'])>
                            {{ $kategoriItem['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('kategori_masalah') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <fieldset>
                <legend class="block text-sm font-semibold text-slate-700">Tahap gangguan <span class="text-rose-600">*</span></legend>
                <div class="mt-2 space-y-2">
                    @foreach ($gangguan as $pilihan)
                        <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2.5 has-checked:border-indigo-500 has-checked:bg-indigo-50">
                            <input type="radio" name="keutamaan" value="{{ $pilihan['value'] }}"
                                   @checked(old('keutamaan') === $pilihan['value'])
                                   class="text-indigo-600 focus:ring-indigo-500" required>
                            <span class="text-sm text-slate-700">{{ $pilihan['gangguan'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('keutamaan') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </fieldset>

            <div>
                <label for="keterangan" class="block text-sm font-semibold text-slate-700">Keterangan masalah <span class="text-rose-600">*</span></label>
                <textarea id="keterangan" name="keterangan" rows="4" required
                          placeholder="Terangkan apa yang berlaku, bila ia bermula, dan mesej ralat jika ada."
                          class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('keterangan') }}</textarea>
                @error('keterangan') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="telefon_hubungan" class="block text-sm font-semibold text-slate-700">Telefon untuk dihubungi</label>
                    <input type="text" id="telefon_hubungan" name="telefon_hubungan" value="{{ old('telefon_hubungan') }}" maxlength="30"
                           class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="lampiran" class="block text-sm font-semibold text-slate-700">Foto / dokumen (pilihan)</label>
                    <input type="file" id="lampiran" name="lampiran[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx"
                           class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                    <p class="mt-1 text-xs text-slate-400">Maksimum 3 fail, setiap satu bawah 10 MB.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('tiket.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Hantar Aduan
                </button>
            </div>
        </form>
    </div>
@endsection

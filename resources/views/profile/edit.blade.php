@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    @php $photoUrl = $user->profilePhotoUrl(); @endphp

    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-900">Profil Saya</h2>
        <p class="mt-1 text-sm text-slate-500">Kemas kini maklumat peribadi dan gambar profil anda.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kad gambar profil --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" x-data="{ preview: null }">
            <h3 class="text-sm font-semibold text-slate-900">Gambar Profil</h3>

            <div class="mt-4 flex flex-col items-center gap-3">
                @if ($photoUrl !== '')
                    <img src="{{ $photoUrl }}" alt="Gambar profil {{ $user->name }}"
                        class="h-24 w-24 rounded-full border border-slate-200 object-cover" x-show="! preview">
                @else
                    <span x-show="! preview"
                        class="flex h-24 w-24 items-center justify-center rounded-full bg-indigo-100 text-2xl font-bold text-indigo-700">
                        {{ mb_substr($user->name, 0, 1) }}
                    </span>
                @endif
                <img :src="preview" alt="Pratonton gambar baharu" x-cloak x-show="preview"
                    class="h-24 w-24 rounded-full border border-slate-200 object-cover">
            </div>

            <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                @method('PUT')

                <label for="photo" class="block text-sm font-medium text-slate-700">Muat naik gambar baharu</label>
                <input id="photo" type="file" name="photo" accept=".jpg,.jpeg,.png"
                    x-on:change="if ($event.target.files.length > 0) { preview = URL.createObjectURL($event.target.files[0]) }"
                    class="mt-1 block w-full cursor-pointer rounded-lg border-slate-300 text-sm shadow-sm file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                @error('photo')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-slate-400">Format JPG, JPEG atau PNG. Saiz maksimum 2 MB.</p>

                <button type="submit"
                    class="mt-4 w-full rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    Simpan Gambar
                </button>
            </form>
        </div>

        {{-- Maklumat peribadi + organisasi --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('profile.update') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <h3 class="text-sm font-semibold text-slate-900">Maklumat Peribadi</h3>

                <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Nama Penuh *</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                        @error('name')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Emel *</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                        @error('email')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-4">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        Simpan Perubahan
                    </button>
                    <p class="text-xs text-slate-400">Emel digunakan untuk log masuk dan notifikasi sistem.</p>
                </div>
            </form>

            <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900">Maklumat Organisasi</h3>
                <dl class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Unit Organisasi</dt>
                        <dd class="font-medium text-slate-800">{{ $user->organizationUnit?->name ?? '— Tiada —' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Lokasi Utama</dt>
                        <dd class="font-medium text-slate-800">{{ $user->primaryLocation?->name ?? '— Tiada —' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Peranan</dt>
                        <dd class="font-medium text-slate-800">{{ $user->getRoleNames()->implode(', ') ?: '— Tiada —' }}</dd>
                    </div>
                </dl>
                <p class="mt-3 text-xs text-slate-400">Maklumat organisasi hanya boleh dikemas kini oleh pentadbir sistem.</p>
            </div>
        </div>
    </div>
@endsection

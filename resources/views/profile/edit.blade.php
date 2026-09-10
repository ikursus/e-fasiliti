@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    @php $photoUrl = $user->profilePhotoUrl(); @endphp

    {{-- Buka tab yang mengandungi ralat validasi selepas submit gagal. --}}
    @php
        $defaultTab = match (true) {
            $errors->hasAny(['current_password', 'password', 'password_confirmation']) => 'keselamatan',
            $errors->has('photo') => 'gambar',
            default => 'peribadi',
        };
    @endphp

    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-900">Profil Saya</h2>
        <p class="mt-1 text-sm text-slate-500">Kemas kini maklumat peribadi, gambar dan keselamatan akaun anda.</p>
    </div>

    <div x-data="{ tab: '{{ $defaultTab }}' }">
        {{-- Navigasi tab --}}
        <div class="mb-6 flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm" role="tablist">
            <button type="button" role="tab" @click="tab = 'peribadi'" :aria-selected="tab === 'peribadi'"
                :class="tab === 'peribadi' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition">Maklumat Peribadi</button>
            <button type="button" role="tab" @click="tab = 'gambar'" :aria-selected="tab === 'gambar'"
                :class="tab === 'gambar' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition">Gambar Profil</button>
            <button type="button" role="tab" @click="tab = 'keselamatan'" :aria-selected="tab === 'keselamatan'"
                :class="tab === 'keselamatan' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition">Keselamatan</button>
        </div>

        {{-- Panel: gambar profil --}}
        <section x-cloak x-show="tab === 'gambar'" class="max-w-md" aria-label="Gambar profil">
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

        </section>

        {{-- Panel: maklumat peribadi + organisasi --}}
        <section x-cloak x-show="tab === 'peribadi'" class="space-y-6" aria-label="Maklumat peribadi">
            <form method="POST" action="{{ route('profile.update') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <h3 class="text-sm font-semibold text-slate-900">Maklumat Peribadi</h3>

                <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Nama Penuh *</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="mt-1 block w-full shadow-sm {{ $errors->has('name') ? 'border-rose-400' : '' }}">
                        @error('name')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Emel *</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="mt-1 block w-full shadow-sm {{ $errors->has('email') ? 'border-rose-400' : '' }}">
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
        </section>

        {{-- Panel: keselamatan (kata laluan) --}}
        <section x-cloak x-show="tab === 'keselamatan'" class="max-w-2xl" aria-label="Keselamatan akaun">
            <form method="POST" action="{{ route('profile.password.update') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <h3 class="text-sm font-semibold text-slate-900">Tukar Kata Laluan</h3>

                <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label for="current_password" class="block text-sm font-medium text-slate-700">Kata Laluan Semasa *</label>
                        <input id="current_password" type="password" name="current_password" required autocomplete="current-password"
                            class="mt-1 block w-full shadow-sm {{ $errors->has('current_password') ? 'border-rose-400' : '' }}">
                        @error('current_password')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Kata Laluan Baharu *</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            class="mt-1 block w-full shadow-sm {{ $errors->has('password') ? 'border-rose-400' : '' }}">
                        @error('password')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-slate-400">Minimum 8 aksara, mengandungi huruf besar, huruf kecil dan nombor.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Sahkan Kata Laluan Baharu *</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="mt-1 block w-full shadow-sm">
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-4">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        Tukar Kata Laluan
                    </button>
                    <p class="text-xs text-slate-400">Anda kekal log masuk; semua sesi lain akan dilog keluar secara automatik.</p>
                </div>
            </form>
        </section>
    </div>
@endsection

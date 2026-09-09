@extends('layouts.app')

@section('title', 'Konfigurasi · Templat Notifikasi')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Subjek dan kandungan mesej keluar. Dihantar oleh modul notifikasi (M14).</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <h3 class="border-b border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800">Templat</h3>
            <ul class="divide-y divide-slate-100">
                @forelse ($templates as $item)
                    <li>
                        <a href="{{ route('admin.settings.notification-templates.edit', $item) }}"
                           class="block px-4 py-3 text-sm hover:bg-slate-50 {{ $template?->is($item) ? 'bg-indigo-50' : '' }}">
                            <span class="font-mono text-xs text-slate-700">{{ $item->key }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $item->channelLabel() }} · {{ strtoupper($item->locale) }}</span>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-slate-500">Tiada templat.</li>
                @endforelse
            </ul>
        </div>

        <div class="lg:col-span-2">
            @if ($template === null)
                <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                    Pilih satu templat untuk disunting.
                </div>
            @else
                <form method="POST" action="{{ route('admin.settings.notification-templates.update', $template) }}"
                      class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    @csrf
                    @method('PUT')

                    <h3 class="mb-1 font-mono text-sm text-slate-800">{{ $template->key }}</h3>
                    <p class="mb-4 text-xs text-slate-500">{{ $template->channelLabel() }} · {{ strtoupper($template->locale) }}</p>

                    <div class="mb-4 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-semibold text-slate-600">Pemegang tempat yang dibenarkan</p>
                        <p class="mt-1 flex flex-wrap gap-1">
                            @foreach ($template->allowedPlaceholders() as $placeholder)
                                <span class="rounded bg-white px-2 py-0.5 font-mono text-xs text-indigo-700 ring-1 ring-slate-200">
                                    &#123;&#123;{{ $placeholder }}&#125;&#125;
                                </span>
                            @endforeach
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-700">Subjek</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject', $template->subject) }}" required maxlength="200"
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('subject')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="body" class="block text-sm font-medium text-slate-700">Kandungan</label>
                            <textarea id="body" name="body" rows="10" required
                                      @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                      class="mt-1 block w-full rounded-lg border-slate-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('body', $template->body) }}</textarea>
                            @error('body')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active))
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Aktif
                        </label>
                    </div>

                    @can('tetapan.kemaskini')
                        <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Simpan
                        </button>
                    @endcan
                </form>
            @endif
        </div>
    </div>
@endsection

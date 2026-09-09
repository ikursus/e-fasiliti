@extends('layouts.app')

@section('title', 'Konfigurasi · Nilai Rujukan')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Senarai nilai yang boleh disunting tanpa menulis kod.</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($types as $type)
            <a href="{{ route('admin.settings.reference-values', ['type' => $type->value]) }}"
               class="rounded-full px-3 py-1.5 text-sm font-medium {{ $activeType === $type ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                {{ $type->label() }}
            </a>
        @endforeach
    </div>

    <p class="mb-4 text-xs text-slate-500">Senarai ini digunakan oleh {{ $activeType->consumer() }}.</p>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Kod</th>
                        <th class="px-4 py-3">Label</th>
                        <th class="px-4 py-3 text-center">Susunan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($values as $value)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $value->code }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $value->label }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $value->sort_order }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $value->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $value->is_active ? 'Aktif' : 'Tidak aktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @can('tetapan.kemaskini')
                                    <form method="POST" action="{{ route('admin.settings.reference-values.toggle', $value) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="font-medium text-indigo-600 hover:underline">
                                            {{ $value->is_active ? 'Nyahaktif' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Senarai ini masih kosong.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @can('tetapan.kemaskini')
            <form method="POST" action="{{ route('admin.settings.reference-values.store') }}"
                  class="h-fit rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                <input type="hidden" name="type" value="{{ $activeType->value }}">
                <h3 class="mb-4 text-sm font-semibold text-slate-800">Tambah ke {{ $activeType->label() }}</h3>

                <div class="space-y-4">
                    <div>
                        <label for="code" class="block text-sm font-medium text-slate-700">Kod</label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}" required maxlength="50"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('code')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="label" class="block text-sm font-medium text-slate-700">Label</label>
                        <input type="text" id="label" name="label" value="{{ old('label') }}" required maxlength="150"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('label')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700">Susunan</label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}" min="0"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Aktif
                    </label>
                </div>

                <button type="submit" class="mt-6 w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Tambah
                </button>
            </form>
        @endcan
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Unit Baharu · Pentadbiran')

@section('content')
    <div class="mx-auto max-w-3xl">
        <nav class="mb-4 flex items-center gap-2 text-sm text-slate-500" aria-label="Laluan">
            <a href="{{ route('admin.organization-units.index') }}" class="font-medium text-slate-600 hover:text-indigo-600 hover:underline">
                Direktori Organisasi
            </a>
            <span aria-hidden="true">&rsaquo;</span>
            <span class="font-medium text-slate-900">Unit Baharu</span>
        </nav>

        <div class="mb-5">
            <h2 class="text-xl font-bold text-slate-900">Unit Organisasi Baharu</h2>
            <p class="mt-1 text-sm text-slate-500">
                Daftarkan sebuah bahagian, atau satu unit di bawah bahagian sedia ada.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.organization-units.store') }}"
              class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @include('admin.organization-units._form')
        </form>
    </div>
@endsection

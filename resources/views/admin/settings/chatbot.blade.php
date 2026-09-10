@extends('layouts.app')

@section('title', 'Konfigurasi · Pembantu AI')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Tetapan Pembantu AI (chatbot) — Google AI Studio (Gemini).</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if (! $apiKeyConfigured)
        <div class="mb-4 max-w-2xl rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Kunci API belum dikonfigurasi. Letakkan <code class="font-mono">GEMINI_API_KEY=...</code> dalam fail
            <code class="font-mono">.env</code>. Kunci diperoleh daripada
            <a href="https://aistudio.google.com/apikey" class="font-semibold underline" target="_blank" rel="noopener">Google AI Studio</a>.
            Chatbot tidak akan berfungsi sehingga kunci ditetapkan.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.chatbot.update') }}"
          class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <input type="hidden" name="chatbot_enabled" value="0">
                <input type="checkbox" id="chatbot_enabled" name="chatbot_enabled" value="1"
                       @checked(old('chatbot_enabled', $enabled))
                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <label for="chatbot_enabled" class="block text-sm font-medium text-slate-700">Aktifkan Pembantu AI</label>
                    <p class="text-xs text-slate-500">Bila dinyahaktifkan, semua pengguna nampak mesej bahawa perkhidmatan ditutup.</p>
                    @error('chatbot_enabled')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="chatbot_model" class="block text-sm font-medium text-slate-700">Model Gemini</label>
                <input type="text" id="chatbot_model" name="chatbot_model"
                       value="{{ old('chatbot_model', $model) }}" required maxlength="100"
                       placeholder="gemini-2.5-flash"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-slate-500">Contoh: gemini-2.5-flash, gemini-2.5-pro.</p>
                @error('chatbot_model')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="chatbot_temperature" class="block text-sm font-medium text-slate-700">Suhu (0–2)</label>
                    <input type="number" id="chatbot_temperature" name="chatbot_temperature" step="0.1" min="0" max="2"
                           value="{{ old('chatbot_temperature', $temperature) }}" required
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('chatbot_temperature')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="chatbot_max_output_tokens" class="block text-sm font-medium text-slate-700">Token balasan</label>
                    <input type="number" id="chatbot_max_output_tokens" name="chatbot_max_output_tokens" step="1" min="64" max="8192"
                           value="{{ old('chatbot_max_output_tokens', $maxOutputTokens) }}" required
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('chatbot_max_output_tokens')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="chatbot_max_history" class="block text-sm font-medium text-slate-700">Sejarah (mesej)</label>
                    <input type="number" id="chatbot_max_history" name="chatbot_max_history" step="1" min="2" max="50"
                           value="{{ old('chatbot_max_history', $maxHistory) }}" required
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('chatbot_max_history')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="chatbot_system_prompt" class="block text-sm font-medium text-slate-700">Arahan sistem</label>
                <textarea id="chatbot_system_prompt" name="chatbot_system_prompt" rows="6" maxlength="8000"
                          class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('chatbot_system_prompt', $systemPrompt) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">Menentukan personaliti dan skop jawapan Pembantu AI.</p>
                @error('chatbot_system_prompt')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        @can('chatbot.tetapan')
            <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Simpan
            </button>
        @endcan
    </form>
@endsection

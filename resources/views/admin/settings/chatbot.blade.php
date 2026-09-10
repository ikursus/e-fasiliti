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

    @error('chatbot_test')
        <div class="mb-4 max-w-2xl rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ $message }}
        </div>
    @enderror

    <div class="mb-4 max-w-2xl rounded-xl border px-4 py-3 text-sm {{ $apiKeyConfigured ? 'border-slate-200 bg-white text-slate-700' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-semibold">Kunci API Gemini</p>
                @if ($apiKeyConfigured)
                    <p class="mt-0.5">
                        <span class="font-mono">{{ $apiKeyMasked }}</span>
                        <span class="text-slate-500">
                            &middot;
                            {{ $apiKeyFromSettings ? 'ditetapkan melalui skrin ini' : 'diambil daripada fail .env' }}
                        </span>
                    </p>
                @else
                    <p class="mt-0.5">
                        Belum ditetapkan. Chatbot tidak akan berfungsi sehingga kunci dimasukkan. Dapatkan kunci
                        daripada <a href="https://aistudio.google.com/apikey" class="font-semibold underline" target="_blank" rel="noopener">Google AI Studio</a>.
                    </p>
                @endif
            </div>

            @can('chatbot.tetapan')
                @if ($apiKeyConfigured)
                    <form method="POST" action="{{ route('admin.settings.chatbot.test') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                            Uji sambungan
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

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
                <label for="chatbot_api_key" class="block text-sm font-medium text-slate-700">Kunci API Gemini</label>
                <input type="password" id="chatbot_api_key" name="chatbot_api_key" maxlength="255"
                       autocomplete="off" spellcheck="false"
                       placeholder="{{ $apiKeyConfigured ? 'Biarkan kosong untuk kekalkan kunci semasa' : 'Tampal kunci daripada Google AI Studio' }}"
                       class="mt-1 block w-full shadow-sm @error('chatbot_api_key') border-rose-400 @enderror">
                <p class="mt-1 text-xs text-slate-500">Kunci disimpan tersulit dan tidak pernah dipaparkan semula.</p>
                @error('chatbot_api_key')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror

                @if ($apiKeyFromSettings)
                    <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                        <input type="hidden" name="chatbot_api_key_remove" value="0">
                        <input type="checkbox" name="chatbot_api_key_remove" value="1" class="h-4 w-4 rounded text-indigo-600">
                        Buang kunci tersimpan dan kembali kepada GEMINI_API_KEY dalam fail .env.
                    </label>
                @endif
            </div>

            <div>
                <label for="chatbot_model" class="block text-sm font-medium text-slate-700">Model Gemini</label>
                <input type="text" id="chatbot_model" name="chatbot_model"
                       value="{{ old('chatbot_model', $model) }}" required maxlength="100"
                       placeholder="gemini-3.6-flash"
                       class="mt-1 block w-full shadow-sm">
                <p class="mt-1 text-xs text-slate-500">Contoh: gemini-3.6-flash. Model yang ditarik balik oleh Google akan gagal dengan status 404.</p>
                @error('chatbot_model')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="chatbot_temperature" class="block text-sm font-medium text-slate-700">Suhu (0–2)</label>
                    <input type="number" id="chatbot_temperature" name="chatbot_temperature" step="0.1" min="0" max="2"
                           value="{{ old('chatbot_temperature', $temperature) }}" required
                           class="mt-1 block w-full shadow-sm">
                    @error('chatbot_temperature')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="chatbot_max_output_tokens" class="block text-sm font-medium text-slate-700">Token balasan</label>
                    <input type="number" id="chatbot_max_output_tokens" name="chatbot_max_output_tokens" step="1" min="64" max="8192"
                           value="{{ old('chatbot_max_output_tokens', $maxOutputTokens) }}" required
                           class="mt-1 block w-full shadow-sm">
                    @error('chatbot_max_output_tokens')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="chatbot_max_history" class="block text-sm font-medium text-slate-700">Sejarah (mesej)</label>
                    <input type="number" id="chatbot_max_history" name="chatbot_max_history" step="1" min="2" max="50"
                           value="{{ old('chatbot_max_history', $maxHistory) }}" required
                           class="mt-1 block w-full shadow-sm">
                    @error('chatbot_max_history')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="chatbot_system_prompt" class="block text-sm font-medium text-slate-700">Arahan sistem</label>
                <textarea id="chatbot_system_prompt" name="chatbot_system_prompt" rows="6" maxlength="8000"
                          class="mt-1 block w-full shadow-sm">{{ old('chatbot_system_prompt', $systemPrompt) }}</textarea>
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

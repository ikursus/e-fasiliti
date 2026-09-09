@extends('layouts.app')

@section('title', 'Pembantu AI')

@section('content')
    <div class="mx-auto flex h-[calc(100vh-11rem)] max-w-5xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
         x-data="pembantuAi()">

        <div class="flex min-h-0 flex-1">

            {{-- Senarai perbualan --}}
            <aside class="hidden w-60 shrink-0 flex-col border-r border-slate-200 bg-slate-50 sm:flex">
                <form method="POST" action="{{ route('chatbot.sessions.store') }}" class="p-3">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        + Perbualan Baharu
                    </button>
                </form>

                <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 pb-3" aria-label="Senarai perbualan">
                    @forelse ($sessions as $session)
                        <div class="group flex items-center rounded-lg {{ ($activeSession?->id === $session->id) ? 'bg-indigo-100' : 'hover:bg-slate-100' }}">
                            <a href="{{ route('chatbot.index', ['sesi' => $session->id]) }}"
                               class="min-w-0 flex-1 truncate px-3 py-2 text-sm {{ ($activeSession?->id === $session->id) ? 'font-semibold text-indigo-800' : 'text-slate-700' }}">
                                {{ $session->title }}
                            </a>
                            <form method="POST" action="{{ route('chatbot.sessions.destroy', $session) }}"
                                  class="pr-1"
                                  x-data
                                  @submit.prevent="if (confirm('Padam perbualan ini?')) $el.submit()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Padam perbualan"
                                        class="rounded p-1 text-slate-400 opacity-0 transition group-hover:opacity-100 hover:text-rose-600 focus:opacity-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="px-3 py-4 text-sm text-slate-500">Tiada perbualan lagi.</p>
                    @endforelse
                </nav>
            </aside>
            {{-- Ruang mesej --}}
            <div class="flex min-w-0 flex-1 flex-col">
                <div id="kotak-mesej" class="min-h-0 flex-1 space-y-4 overflow-y-auto p-4 sm:p-6">

                    @if ($activeSession === null)
                        <div class="flex h-full items-center justify-center">
                            <div class="max-w-sm text-center">
                                <p class="text-sm text-slate-500">
                                    Selamat datang ke <span class="font-semibold text-slate-700">Pembantu AI e-Fasiliti</span>.
                                    Mulakan perbualan baharu untuk bertanya tentang tempahan bilik, aduan ICT, inventari aset dan prosedur sistem.
                                </p>
                                <form method="POST" action="{{ route('chatbot.sessions.store') }}" class="mt-4">
                                    @csrf
                                    <button type="submit"
                                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        + Perbualan Baharu
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <template x-for="(mesej, indeks) in senarai" :key="indeks">
                            <div class="flex" :class="mesej.role === 'user' ? 'justify-end' : 'justify-start'">
                                <div class="max-w-[80%] whitespace-pre-wrap rounded-2xl px-4 py-2.5 text-sm leading-relaxed"
                                     :class="mesej.role === 'user'
                                         ? 'rounded-br-sm bg-indigo-600 text-white'
                                         : 'rounded-bl-sm border border-slate-200 bg-slate-100 text-slate-800'"
                                     x-text="mesej.content"></div>
                            </div>
                        </template>

                        <div x-show="hantar" x-cloak class="flex justify-start">
                            <div class="rounded-2xl rounded-bl-sm border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm text-slate-500">
                                Pembantu sedang menaip…
                            </div>
                        </div>

                        <div x-show="ralat" x-cloak class="flex justify-start">
                            <div class="max-w-[80%] rounded-2xl rounded-bl-sm border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-700" x-text="ralat"></div>
                        </div>
                    @endif
                </div>
                @if ($activeSession !== null)
                    <form class="border-t border-slate-200 p-3 sm:p-4" @submit.prevent="kirim()">
                        <div class="flex items-end gap-2">
                            <textarea x-model="mesej" rows="1" maxlength="4000"
                                      placeholder="Tulis mesej anda… (Enter untuk hantar, Shift+Enter untuk baris baharu)"
                                      class="max-h-40 min-h-[2.75rem] flex-1 resize-none rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      @keydown.enter.prevent="if (!$event.shiftKey) kirim()"></textarea>
                            <button type="submit" :disabled="hantar"
                                    class="h-[2.75rem] rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                                Hantar
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function pembantuAi() {
            return {
                senarai: @json($initialMessages),
                sesiId: {{ $activeSession?->id ?? 'null' }},
                mesej: '',
                hantar: false,
                ralat: '',

                async kirim() {
                    const teks = this.mesej.trim();

                    if (teks === '' || this.hantar || !this.sesiId) {
                        return;
                    }

                    this.senarai.push({ role: 'user', content: teks });
                    this.mesej = '';
                    this.hantar = true;
                    this.ralat = '';
                    this.$nextTick(() => this.skrol());

                    try {
                        const respon = await fetch(`/chatbot/sessions/${this.sesiId}/messages`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ message: teks }),
                        });

                        const data = await respon.json();

                        if (!respon.ok) {
                            this.ralat = data.message || 'Ralat tidak dijangka. Cuba lagi.';
                        } else {
                            this.senarai.push({ role: 'model', content: data.reply });
                        }
                    } catch (ralat) {
                        this.ralat = 'Sambungan ke pelayan gagal. Cuba lagi.';
                    } finally {
                        this.hantar = false;
                        this.$nextTick(() => this.skrol());
                    }
                },

                skrol() {
                    const kotak = document.getElementById('kotak-mesej');

                    if (kotak) {
                        kotak.scrollTop = kotak.scrollHeight;
                    }
                },
            };
        }
    </script>
@endpush

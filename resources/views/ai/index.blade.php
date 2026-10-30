@extends('layouts.app')

@section('title', 'Asisten Cerdas (AI)')
@section('page-title', 'Kecerdasan Buatan (AI) & Prediksi Toko')

@push('styles')
<style>
    .chat-container {
        height: 380px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        background-color: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
    }
    .chat-bubble {
        max-width: 80%;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-size: 0.9rem;
        line-height: 1.4;
    }
    .chat-bubble-user {
        background-color: var(--tokoku-primary);
        color: #ffffff;
        align-self: flex-end;
        border-bottom-right-radius: 2px;
    }
    .chat-bubble-ai {
        background-color: #ffffff;
        color: var(--tokoku-text);
        border: 1px solid var(--tokoku-border);
        align-self: flex-start;
        border-bottom-left-radius: 2px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .confidence-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 50rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- HEADER & TOMBOL UPDATE MODEL AI --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1"><i class="bi bi-robot text-success me-2"></i>Asisten AI & Optimasi Inventaris</h1>
            <p class="text-muted small mb-0">Prediksi restok barang otomatis, bundling produk laris, dan konsultasi cerdas on-the-fly.</p>
        </div>

        <form method="POST" action="{{ route('ai.refresh') }}">
            @csrf
            <button type="submit" class="btn btn-outline-success fw-semibold shadow-sm">
                <i class="bi bi-arrow-repeat me-1"></i> Perbarui Analisis AI
            </button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        {{-- KOLOM KIRI: PREDIKSI RESTOK (MOVING AVERAGE) --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-calendar-event text-primary me-2"></i>Prediksi Waktu Restok
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border">Moving Average</span>
                </div>
                <div class="card-body p-3">
                    @if($prediksiStok->isEmpty())
                        <div class="text-center py-4 text-muted small">Belum ada barang di katalog untuk dianalisis.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Barang</th>
                                        <th class="text-center">Sisa Stok</th>
                                        <th>Estimasi Habis</th>
                                        <th class="text-center">Rekomendasi Restok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prediksiStok as $p)
                                        @php
                                            $tglHabis = \Carbon\Carbon::parse($p->estimasi_stok_habis);
                                            $sisaHari = (int) now()->diffInDays($tglHabis, false);
                                            $isDarurat = $sisaHari <= 7;
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $p->barang->nama ?? 'Barang #' . $p->id_barang }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $p->barang->stok_sekarang <= 5 ? 'bg-danger' : 'bg-light text-dark border' }}">
                                                    {{ $p->barang->stok_sekarang ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="{{ $isDarurat ? 'text-danger fw-bold' : 'text-dark' }}">
                                                    {{ $tglHabis->format('d M Y') }}
                                                </div>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    {{ $sisaHari > 0 ? "Sekitar {$sisaHari} hari lagi" : 'Harus segera pesan' }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success fs-6">
                                                    +{{ $p->rekomendasi_jumlah_restok }} unit
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: REKOMENDASI BUNDLING --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-box2-heart text-warning me-2"></i>Rekomendasi Paket Bundling
                    </h6>
                    <span class="badge bg-warning-subtle text-warning-emphasis border">Market Basket Analysis</span>
                </div>
                <div class="card-body p-3">
                    @if($rekomendasiBundling->isEmpty())
                        <div class="text-center py-4 text-muted small">Belum cukup data transaksi untuk membentuk asosiasi bundling.</div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($rekomendasiBundling as $b)
                                <div class="p-3 rounded bg-light border d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle p-2 bg-white border text-center shadow-sm" style="width: 44px; height: 44px;">
                                            <i class="bi bi-bag-plus text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">
                                                {{ $b->utama->nama ?? 'Item 1' }} <span class="text-muted fw-normal">+</span> {{ $b->pasangan->nama ?? 'Item 2' }}
                                            </div>
                                            <small class="text-muted">Sering dibeli bersamaan oleh pelanggan saat transaksi kasir.</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success confidence-badge">
                                            Kecocokan: {{ (int)($b->skor_asosiasi * 100) }}%
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODUL CHAT: AI SMART ASSISTANT ON-THE-FLY (AI-01 / US-03) --}}
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-0 pt-3 px-3 d-flex align-items-center gap-2">
            <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                <i class="bi bi-chat-quote-fill"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0">Konsultasi Bisnis: SITOKO Smart Assistant</h6>
                <small class="text-muted">Pemrosesan data real-time langsung di memori tanpa membebani penyimpanan database.</small>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            
            {{-- CHAT LOG BOX --}}
            <div class="chat-container mb-3" id="chatBox">
                <div class="chat-bubble chat-bubble-ai">
                    <strong>SITOKO Assistant:</strong><br>
                    Halo Owner! Saya siap membantu menganalisis toko Anda. Anda bisa bertanya hal seperti:<br>
                    &bull; <em>"Bagaimana rekap omzet hari ini?"</em><br>
                    &bull; <em>"Barang apa saja yang stoknya hampir habis?"</em><br>
                    &bull; <em>"Berapa total kasbon pelanggan yang belum dibayar?"</em>
                </div>
            </div>

            {{-- FORM INPUT PROMPT CHAT --}}
            <form id="formChatAi" class="d-flex gap-2">
                <input type="text" id="inputPrompt" class="form-control form-control-lg border" placeholder="Ketik pertanyaan untuk asisten AI..." autocomplete="off" required>
                <button type="submit" id="btnSendChat" class="btn btn-tokoku-primary px-4 fw-bold">
                    <i class="bi bi-send-fill me-1"></i> Tanya
                </button>
            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formChat = document.getElementById('formChatAi');
        const inputPrompt = document.getElementById('inputPrompt');
        const btnSend = document.getElementById('btnSendChat');
        const chatBox = document.getElementById('chatBox');

        formChat.addEventListener('submit', function (e) {
            e.preventDefault();
            const prompt = inputPrompt.value.trim();
            if (!prompt) return;

            // 1. Tampilkan bubble pesan user
            appendBubble(prompt, 'user');
            inputPrompt.value = '';
            inputPrompt.focus();

            // 2. Tampilkan loading state AI
            const loadingBubble = appendBubble('<span class="spinner-border spinner-border-sm me-1"></span> Menghitung analisis...', 'ai');
            btnSend.disabled = true;

            // 3. Request ke API AI On-The-Fly
            fetch(`{{ route('ai.chat') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ prompt: prompt })
            })
            .then(res => res.json())
            .then(res => {
                btnSend.disabled = false;
                if (res.success) {
                    loadingBubble.innerHTML = `<strong>SITOKO Assistant:</strong><br>${formatMarkdown(res.response)}`;
                } else {
                    loadingBubble.innerHTML = `<span class="text-danger">Maaf, terjadi kesalahan saat memproses jawaban.</span>`;
                }
                scrollChatBottom();
            })
            .catch(() => {
                btnSend.disabled = false;
                loadingBubble.innerHTML = `<span class="text-danger">Koneksi ke asisten terputus. Silakan coba lagi.</span>`;
                scrollChatBottom();
            });
        });

        function appendBubble(content, sender) {
            const bubble = document.createElement('div');
            bubble.className = `chat-bubble chat-bubble-${sender}`;
            bubble.innerHTML = content;
            chatBox.appendChild(bubble);
            scrollChatBottom();
            return bubble;
        }

        function scrollChatBottom() {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function formatMarkdown(text) {
            // Parser sederhana bold, list & newline
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
        }
    });
</script>
@endpush


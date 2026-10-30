@extends('layouts.app')

@section('title', 'Laporan & Omzet')
@section('page-title', 'Laporan Finansial & Omzet')

@push('styles')
<style>
    .metric-card {
        position: relative;
        overflow: hidden;
        border-radius: 16px !important;
        background: #ffffff;
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }
    .metric-omzet::before { background: linear-gradient(90deg, #10b981, #0e7c67); }
    .metric-hpp::before { background: linear-gradient(90deg, #94a3b8, #64748b); }
    .metric-laba::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
    .metric-kasbon::before { background: linear-gradient(90deg, #f43f5e, #be123c); }

    .metric-bg-icon {
        position: absolute;
        right: -10px;
        bottom: -15px;
        font-size: 5.5rem;
        opacity: 0.04;
        transform: rotate(-10deg);
        pointer-events: none;
    }
    .metric-val {
        font-family: 'JetBrains Mono', monospace;
        letter-spacing: -0.03em;
    }
</style>
@endpush

@section('content')
<div class="w-100">

    {{-- FILTER WAKTU LAPORAN --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Ikhtisar Performa Bisnis</h1>
            <p class="text-muted small mb-0">Periode: <strong class="text-dark">{{ $labelRentang }}</strong></p>
        </div>

        <div class="btn-group shadow-sm bg-white p-1 rounded-pill border" role="group">
            <a href="{{ route('laporan.index', ['rentang' => 'hari_ini']) }}" class="btn btn-sm rounded-pill px-3 fw-bold {{ $rentang === 'hari_ini' ? 'btn-tokoku-primary' : 'btn-light border-0' }}">Hari Ini</a>
            <a href="{{ route('laporan.index', ['rentang' => '7_hari']) }}" class="btn btn-sm rounded-pill px-3 fw-bold {{ $rentang === '7_hari' ? 'btn-tokoku-primary' : 'btn-light border-0' }}">7 Hari</a>
            <a href="{{ route('laporan.index', ['rentang' => 'bulan_ini']) }}" class="btn btn-sm rounded-pill px-3 fw-bold {{ $rentang === 'bulan_ini' ? 'btn-tokoku-primary' : 'btn-light border-0' }}">Bulan Ini</a>
            <a href="{{ route('laporan.index', ['rentang' => 'semua']) }}" class="btn btn-sm rounded-pill px-3 fw-bold {{ $rentang === 'semua' ? 'btn-tokoku-primary' : 'btn-light border-0' }}">Semua Waktu</a>
        </div>
    </div>

    {{-- KARTU METRIK UTAMA DENGAN RESPONSIVITAS ULTRA-WIDE (col-xxl-3) --}}
    <div class="row g-3 g-xl-4 mb-4">
        {{-- TOTAL OMZET --}}
        <div class="col-sm-6 col-lg-3 col-xxl-3">
            <div class="card metric-card metric-omzet p-3 p-md-4 h-100 shadow-sm">
                <i class="bi bi-cash-stack metric-bg-icon"></i>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold" style="letter-spacing: 0.05em; font-size: 0.72rem;">TOTAL OMZET KOTOR</span>
                    <div class="rounded-circle p-2 bg-success-subtle text-success">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 metric-val" style="color: var(--tokoku-primary);">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h3>
                <small class="text-muted">{{ $ringkasanNota->total_transaksi }} transaksi berhasil</small>
            </div>
        </div>

        {{-- TOTAL HPP --}}
        <div class="col-sm-6 col-lg-3 col-xxl-3">
            <div class="card metric-card metric-hpp p-3 p-md-4 h-100 shadow-sm">
                <i class="bi bi-box-arrow-in-down metric-bg-icon"></i>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold" style="letter-spacing: 0.05em; font-size: 0.72rem;">TOTAL HPP (MODAL)</span>
                    <div class="rounded-circle p-2 bg-secondary-subtle text-secondary">
                        <i class="bi bi-boxes fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-secondary mb-1 metric-val">Rp {{ number_format($totalHpp, 0, ',', '.') }}</h3>
                <small class="text-muted">Biaya kulakan barang terjual</small>
            </div>
        </div>

        {{-- LABA BERSIH --}}
        <div class="col-sm-6 col-lg-3 col-xxl-3">
            <div class="card metric-card metric-laba p-3 p-md-4 h-100 shadow-sm">
                <i class="bi bi-graph-up-arrow metric-bg-icon"></i>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold" style="letter-spacing: 0.05em; font-size: 0.72rem;">ESTIMASI LABA BERSIH</span>
                    <div class="rounded-circle p-2 {{ $labaBersih >= 0 ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }}">
                        <i class="bi bi-pie-chart-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 metric-val {{ $labaBersih >= 0 ? 'text-primary' : 'text-danger' }}">
                    Rp {{ number_format($labaBersih, 0, ',', '.') }}
                </h3>
                <small class="text-muted">Laba kotor setelah operasional</small>
            </div>
        </div>

        {{-- PIUTANG KASBON --}}
        <div class="col-sm-6 col-lg-3 col-xxl-3">
            <div class="card metric-card metric-kasbon p-3 p-md-4 h-100 shadow-sm">
                <i class="bi bi-journal-x metric-bg-icon"></i>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold" style="letter-spacing: 0.05em; font-size: 0.72rem;">PIUTANG KASBON TERTAMPUNG</span>
                    <div class="rounded-circle p-2 bg-danger-subtle text-danger">
                        <i class="bi bi-exclamation-triangle fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-danger mb-1 metric-val">Rp {{ number_format($totalPiutangSeluruhToko, 0, ',', '.') }}</h3>
                <small class="text-muted">Saldo kasbon di tangan pembeli</small>
            </div>
        </div>
    </div>

    {{-- KONTEN TENGAH: PRODUK TERLARIS & RESTOK KRITIS (FULL PROPORTIONAL) --}}
    <div class="row g-3 g-xl-4 mb-4">
        {{-- PRODUK TERLARIS --}}
        <div class="col-lg-6 col-xxl-6">
            <div class="card bg-white h-100 p-3 p-md-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-trophy-fill text-warning me-2"></i>Produk Paling Laris</h6>
                    <span class="badge bg-light text-secondary border">Top 5 Teratas</span>
                </div>
                @if($topBarang->isEmpty())
                    <div class="text-center py-4 text-muted small">Belum ada data barang terjual pada periode ini.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="text-muted small border-bottom">
                                    <th>Nama Barang</th>
                                    <th class="text-center">Terjual</th>
                                    <th class="text-end">Kontribusi Omzet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topBarang as $b)
                                    <tr>
                                        <td class="fw-bold py-2 text-dark">{{ $b->nama }}</td>
                                        <td class="text-center"><span class="badge bg-success-subtle text-success py-1 px-2">{{ (int)$b->total_terjual }} unit</span></td>
                                        <td class="text-end fw-bold text-dark" style="font-family: 'JetBrains Mono', monospace;">Rp {{ number_format($b->total_omzet_barang, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- PERINGATAN RESTOK KRITIS --}}
        <div class="col-lg-6 col-xxl-6">
            <div class="card bg-white h-100 p-3 p-md-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-octagon-fill me-2"></i>Peringatan Stok Kritis (&le; 5)</h6>
                    <a href="{{ route('produk.index') }}" class="small fw-semibold text-decoration-none">Buka Inventaris &rarr;</a>
                </div>
                @if($stokMenipis->isEmpty())
                    <div class="text-center py-4 text-success small">
                        <i class="bi bi-check-circle-fill fs-3 d-block mb-1"></i>
                        Semua stok barang berada dalam kuantitas aman.
                    </div>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($stokMenipis as $sm)
                            <div class="p-2 px-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark small">{{ $sm->nama }}</div>
                                    <span class="text-muted" style="font-size: 0.72rem;">Barcode: {{ $sm->barcode ?? '-' }}</span>
                                </div>
                                <span class="badge bg-danger fs-6 px-3 py-1">Sisa: {{ $sm->stok_sekarang }} {{ $sm->satuan }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TABEL TRANSAKSI TERAKHIR (FLUID LEBAR PENUH) --}}
    <div class="card bg-white p-3 p-md-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2"></i>10 Transaksi Penjualan Terakhir</h6>
        </div>
        @if($riwayatTransaksi->isEmpty())
            <div class="text-center py-4 text-muted small">Belum ada transaksi yang tercatat.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nota</th>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th>Pelanggan</th>
                            <th>Rincian Item</th>
                            <th class="text-center">Metode</th>
                            <th class="text-end">Total Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayatTransaksi as $t)
                            <tr>
                                <td><code>{{ $t->nomor_nota }}</code></td>
                                <td class="text-muted">{{ \Carbon\Carbon::parse($t->tanggal)->format('d M H:i') }}</td>
                                <td><span class="fw-semibold text-dark">{{ $t->nama_kasir }}</span></td>
                                <td><span class="fw-semibold">{{ $t->nama_pelanggan ?? 'Umum' }}</span></td>
                                <td class="text-truncate text-muted" style="max-width: 320px;" title="{{ $t->rincian_belanja }}">
                                    {{ $t->rincian_belanja }}
                                </td>
                                <td class="text-center">
                                    @if($t->metode_bayar === 'hutang')
                                        <span class="badge bg-danger-subtle text-danger fw-bold">Kasbon</span>
                                    @elseif($t->metode_bayar === 'transfer')
                                        <span class="badge bg-info-subtle text-info fw-bold">Transfer</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success fw-bold">Tunai</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-dark" style="font-family: 'JetBrains Mono', monospace;">Rp {{ number_format($t->total_akhir, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

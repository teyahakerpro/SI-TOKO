@extends('layouts.app')

@section('title', 'Pelanggan & Kasbon')
@section('page-title', 'Pelanggan & Kasbon (Piutang)')

@section('content')
<div class="container-fluid px-0">

    {{-- KARTU STATISTIK RINGKASAN PIUTANG --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-danger-subtle text-danger">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold">TOTAL PIUTANG KASBON</small>
                        <h4 class="fw-bold text-danger mb-0">Rp {{ number_format($totalSemuaHutang, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-person-exclamation fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold">PELANGGAN BELUM LUNAS</small>
                        <h4 class="fw-bold text-dark mb-0">{{ $totalPelangganBerhutang }} Orang</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 d-flex flex-row align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-1">Tambah Pelanggan</h6>
                    <small class="text-muted">Daftarkan pelanggan tetap toko.</small>
                </div>
                <button class="btn btn-tokoku-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggan">
                    <i class="bi bi-person-plus-fill me-1"></i> Baru
                </button>
            </div>
        </div>
    </div>

    {{-- FILTER PENCARIAN & TABEL PELANGGAN --}}
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-body p-3 p-md-4">
            
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-bold mb-0">Daftar Pelanggan</h5>
                <form method="GET" action="{{ route('pelanggan.index') }}" class="d-flex gap-2">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Cari nama atau telepon..." value="{{ $keyword }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                    @if($keyword)
                        <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </form>
            </div>

            @if($pelanggan->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    <h6 class="fw-semibold">Belum Ada Data Pelanggan</h6>
                    <small>Klik tombol "Baru" di atas untuk mendaftarkan pelanggan.</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 30%;">Nama Pelanggan</th>
                                <th style="width: 20%;">Nomor Telepon</th>
                                <th style="width: 25%;" class="text-end">Tanggungan Kasbon</th>
                                <th style="width: 20%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pelanggan as $idx => $p)
                                <tr>
                                    <td>{{ $pelanggan->firstItem() + $idx }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $p->nama }}</div>
                                        <small class="text-muted">{{ $p->jumlah_transaksi }}x transaksi</small>
                                    </td>
                                    <td>
                                        @if($p->telepon)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $p->telepon) }}" target="_blank" class="text-decoration-none">
                                                <i class="bi bi-whatsapp text-success me-1"></i>{{ $p->telepon }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($p->total_hutang > 0)
                                            <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2 border border-danger-subtle">
                                                Rp {{ number_format($p->total_hutang, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success fs-6 px-3 py-2">
                                                Lunas (Rp 0)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            @if($p->total_hutang > 0)
                                                <button class="btn btn-sm btn-success fw-semibold" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalBayarHutang"
                                                        data-id="{{ $p->id_pelanggan }}"
                                                        data-nama="{{ $p->nama }}"
                                                        data-hutang="{{ $p->total_hutang }}"
                                                        title="Pelunasan Kasbon">
                                                    <i class="bi bi-cash-stack me-1"></i> Lunasi
                                                </button>
                                            @endif

                                            <button class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditPelanggan"
                                                    data-id="{{ $p->id_pelanggan }}"
                                                    data-nama="{{ $p->nama }}"
                                                    data-telepon="{{ $p->telepon }}"
                                                    title="Edit Data Kontak">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            @if($p->total_hutang <= 0)
                                                <form method="POST" action="{{ route('pelanggan.destroy', $p->id_pelanggan) }}" class="d-inline" onsubmit="return confirm('Hapus pelanggan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pelanggan">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-center">
                    {{ $pelanggan->links('pagination::bootstrap-5') }}
                </div>
            @endif

        </div>
    </div>
</div>

{{-- MODAL TAMBAH PELANGGAN --}}
<div class="modal fade" id="modalTambahPelanggan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('pelanggan.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Pelanggan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="namaTambah" class="form-label fw-semibold">Nama Pelanggan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="namaTambah" name="nama" required placeholder="Contoh: Pak RT / Bu Siti">
                </div>
                <div class="mb-3">
                    <label for="teleponTambah" class="form-label fw-semibold">Nomor WhatsApp / Telepon</label>
                    <input type="text" class="form-control" id="teleponTambah" name="telepon" placeholder="Contoh: 08123456789">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-tokoku-primary fw-semibold">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT PELANGGAN --}}
<div class="modal fade" id="modalEditPelanggan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formEditPelanggan" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Kontak Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="namaEdit" class="form-label fw-semibold">Nama Pelanggan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="namaEdit" name="nama" required>
                </div>
                <div class="mb-3">
                    <label for="teleponEdit" class="form-label fw-semibold">Nomor WhatsApp / Telepon</label>
                    <input type="text" class="form-control" id="teleponEdit" name="telepon">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-tokoku-primary fw-semibold">Perbarui Kontak</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL LUNASI KASBON --}}
<div class="modal fade" id="modalBayarHutang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formBayarHutang" class="modal-content">
            @csrf
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-cash-coin me-1"></i> Pelunasan Kasbon</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="text-muted small">Pelanggan:</div>
                    <h5 class="fw-bold text-dark mb-2" id="bayarNamaPelanggan">-</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Total Kasbon:</span>
                        <strong class="text-danger fs-5" id="bayarTotalHutang">Rp 0</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nominalBayar" class="form-label fw-semibold">Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-lg fw-bold" id="nominalBayar" name="nominal" required min="1">
                    <div class="form-text">Masukkan jumlah uang yang dibayarkan pelanggan (bisa dicicil atau lunas).</div>
                </div>

                <button type="button" class="btn btn-outline-success btn-sm w-100 fw-semibold" id="btnPenuhkanBayar">
                    <i class="bi bi-check2-all me-1"></i> Lunasi Penuh Sekaligus
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success fw-bold">Konfirmasi Pembayaran</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal Edit Kontak
        const modalEdit = document.getElementById('modalEditPelanggan');
        if (modalEdit) {
            modalEdit.addEventListener('show.bs.event' in window ? 'show.bs.event' : 'show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                const id = btn.getAttribute('data-id');
                const nama = btn.getAttribute('data-nama');
                const telepon = btn.getAttribute('data-telepon');

                document.getElementById('namaEdit').value = nama;
                document.getElementById('teleponEdit').value = telepon || '';
                document.getElementById('formEditPelanggan').action = `/pelanggan/${id}`;
            });
        }

        // Modal Lunasi Kasbon
        const modalBayar = document.getElementById('modalBayarHutang');
        if (modalBayar) {
            let maxHutang = 0;
            modalBayar.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                const id = btn.getAttribute('data-id');
                const nama = btn.getAttribute('data-nama');
                maxHutang = parseFloat(btn.getAttribute('data-hutang')) || 0;

                document.getElementById('bayarNamaPelanggan').textContent = nama;
                document.getElementById('bayarTotalHutang').textContent = 'Rp ' + maxHutang.toLocaleString('id-ID');
                const inp = document.getElementById('nominalBayar');
                inp.value = maxHutang;
                inp.max = maxHutang;
                document.getElementById('formBayarHutang').action = `/pelanggan/${id}/bayar-hutang`;
            });

            document.getElementById('btnPenuhkanBayar').addEventListener('click', function () {
                document.getElementById('nominalBayar').value = maxHutang;
            });
        }
    });
</script>
@endpush


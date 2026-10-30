@extends('layouts.app')

@section('title', 'Katalog Barang')
@section('page-title', 'Katalog Barang')

@push('styles')
<style>
    .card-produk {
        transition: transform .2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid var(--tokoku-border);
    }
    .card-produk:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 25px -5px rgba(14, 124, 103, 0.12), 0 8px 10px -6px rgba(14, 124, 103, 0.05) !important;
        border-color: #cbd5e1;
    }
    .produk-thumbnail {
        height: 140px;
        background-color: var(--tokoku-primary-light);
        position: relative;
        overflow: hidden;
    }
    .produk-img-cover {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .img-upload-preview {
        width: 70px;
        height: 70px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px dashed #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8fafc;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- HEADER & FILTER PENCARIAN / KATEGORI --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Katalog Inventaris Toko</h1>
            <p class="text-muted small mb-0">{{ $produk->total() }} barang terdaftar &middot; inventaris aktif toko</p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            {{-- FORM FILTER & CARI --}}
            <form method="GET" action="{{ route('produk.index') }}" class="d-flex gap-2">
                <input type="text" name="q" class="form-control" placeholder="Cari nama atau barcode..." value="{{ $keyword ?? '' }}" style="max-width: 200px;">
                <select name="kategori" class="form-select" onchange="this.form.submit()" aria-label="Filter kategori">
                    <option value="">Semua Kategori</option>
                    @foreach ($daftarKategori as $kat)
                        <option value="{{ $kat->id_kategori }}" @selected($kategoriAktif == $kat->id_kategori)>
                            {{ $kat->nama }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
                @if ($kategoriAktif || !empty($keyword))
                    <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary text-nowrap">Reset</a>
                @endif
            </form>

            {{-- TOMBOL TAMBAH BARANG (HANYA OWNER / ADMIN) --}}
            @if (Auth::user()->peran === 'admin')
                <button class="btn btn-tokoku-primary fw-semibold text-nowrap shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Barang
                </button>
            @endif
        </div>
    </div>

    {{-- DAFTAR KARTU PRODUK --}}
    @if ($produk->isEmpty())
        <div class="card border-0 shadow-sm py-5 text-center text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
            <h6 class="fw-semibold">Belum ada barang di kategori atau pencarian ini</h6>
            <small>Coba pilih kategori lain atau reset kata kunci pencarian.</small>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3">
            @foreach ($produk as $item)
                <div class="col">
                    <div class="card card-produk h-100 bg-white rounded-3 overflow-hidden shadow-sm">

                        {{-- FOTO / LOGO PRODUK ATAU FALLBACK IKON --}}
                        <div class="produk-thumbnail d-flex align-items-center justify-content-center">
                            @if ($item->gambar_url)
                                <img src="{{ $item->gambar_url }}" alt="{{ $item->nama }}" class="produk-img-cover">
                            @else
                                <i class="bi bi-box-seam fs-1" style="color: var(--tokoku-primary);"></i>
                            @endif

                            {{-- TOMBOL EDIT / HAPUS (HANYA OWNER) --}}
                            @if (Auth::user()->peran === 'admin')
                                <div class="position-absolute top-0 end-0 p-2 d-flex gap-1" style="z-index: 2;">
                                    <button class="btn btn-sm btn-white bg-white border rounded-circle shadow-sm p-1 d-flex align-items-center justify-content-center"
                                            style="width: 30px; height: 30px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditBarang"
                                            data-id="{{ $item->id_barang }}"
                                            data-nama="{{ $item->nama }}"
                                            data-kategori="{{ $item->id_kategori }}"
                                            data-barcode="{{ $item->barcode }}"
                                            data-satuan="{{ $item->satuan }}"
                                            data-hargabeli="{{ (int)$item->harga_beli }}"
                                            data-hargajual="{{ (int)$item->harga_jual }}"
                                            data-stok="{{ $item->stok_sekarang }}"
                                            data-gambar="{{ $item->gambar_url ?? '' }}"
                                            title="Edit Barang">
                                        <i class="bi bi-pencil-fill small text-secondary"></i>
                                    </button>
                                    <form method="POST" action="{{ route('produk.destroy', $item->id_barang) }}" onsubmit="return confirm('Hapus barang {{ $item->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-white bg-white border rounded-circle shadow-sm p-1 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" title="Hapus Barang">
                                            <i class="bi bi-trash-fill small text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="card-body d-flex flex-column p-3">
                            <span class="badge bg-light text-secondary border align-self-start mb-2" style="font-size: 0.72rem;">
                                {{ $item->kategori->nama ?? 'Tanpa kategori' }}
                            </span>

                            <h2 class="h6 fw-bold mb-1 text-truncate" title="{{ $item->nama }}">
                                {{ $item->nama }}
                            </h2>
                            <div class="small text-muted mb-3">
                                <code>{{ $item->barcode ?? '-' }}</code> &middot; per {{ $item->satuan ?? 'pcs' }}
                            </div>

                            <div class="mt-auto">
                                {{-- ATURAN RBAC: Sembunyikan HPP & Laba jika Kasir --}}
                                @if (Auth::user()->peran === 'admin')
                                    <div class="row g-2 small mb-3 p-2 rounded-3 bg-light border">
                                        <div class="col-6">
                                            <div class="text-muted" style="font-size: 0.7rem;">HPP (Beli)</div>
                                            <div class="fw-semibold text-secondary">{{ $item->harga_beli_format }}</div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <div class="text-muted" style="font-size: 0.7rem;">Estimasi Laba</div>
                                            <div class="fw-bold {{ $item->laba_per_unit < 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $item->laba_format }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="border-top pt-2 d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Harga Jual</div>
                                        <div class="fw-bold fs-5" style="color: var(--tokoku-primary); font-family: 'JetBrains Mono', monospace;">
                                            {{ $item->harga_format }}
                                        </div>
                                    </div>

                                    @switch($item->status_stok)
                                        @case('habis')
                                            <span class="badge text-bg-danger">Habis</span>
                                            @break
                                        @case('menipis')
                                            <span class="badge text-bg-warning">Sisa {{ $item->stok_sekarang }}</span>
                                            @break
                                        @default
                                            <span class="badge text-bg-success">Stok {{ $item->stok_sekarang }}</span>
                                    @endswitch
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- PAGINASI --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $produk->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

{{-- MODAL TAMBAH BARANG (DENGAN UPLOAD GAMBAR) --}}
@if (Auth::user()->peran === 'admin')
<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('produk.store') }}" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Barang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- PILIH FOTO / LOGO PRODUK DENGAN PREVIEW --}}
                <div class="mb-3 p-3 bg-light rounded-3 border">
                    <label class="form-label fw-bold small text-muted mb-2">FOTO / LOGO PRODUK (OPSIONAL)</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="img-upload-preview" id="previewContainerTambah">
                            <i class="bi bi-image text-muted fs-3" id="iconPlaceholderTambah"></i>
                            <img src="" id="imgPreviewTambah" class="w-100 h-100 object-fit-cover d-none" alt="Preview">
                        </div>
                        <div class="flex-grow-1">
                            <input type="file" class="form-control form-control-sm" id="inputFotoTambah" name="gambar" accept="image/png, image/jpeg, image/jpg, image/webp">
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Format: JPG, PNG, WEBP (Maksimal 2MB).</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="namaTambah" class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="namaTambah" name="nama" required placeholder="Contoh: Minyak Goreng 2L">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="kategoriTambah" class="form-label fw-semibold">Kategori</label>
                        <select class="form-select" id="kategoriTambah" name="id_kategori">
                            <option value="">Pilih Kategori</option>
                            @foreach($daftarKategori as $kat)
                                <option value="{{ $kat->id_kategori }}">{{ $kat->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="satuanTambah" class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="satuanTambah" name="satuan" required placeholder="Pcs / Kg / Sak" value="Pcs">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="barcodeTambah" class="form-label fw-semibold">Barcode / Kode SKU</label>
                    <input type="text" class="form-control" id="barcodeTambah" name="barcode" placeholder="Scan atau ketik kode barcode">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="hargaBeliTambah" class="form-label fw-semibold">Harga Beli / HPP (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="hargaBeliTambah" name="harga_beli" required min="0" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label for="hargaJualTambah" class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="hargaJualTambah" name="harga_jual" required min="0" placeholder="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="stokTambah" class="form-label fw-semibold">Jumlah Stok Awal <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stokTambah" name="stok_sekarang" required min="0" value="10">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-tokoku-primary fw-semibold">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT BARANG (DENGAN UPLOAD GAMBAR) --}}
<div class="modal fade" id="modalEditBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formEditBarang" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Data Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- PILIH / GANTI FOTO DENGAN PREVIEW --}}
                <div class="mb-3 p-3 bg-light rounded-3 border">
                    <label class="form-label fw-bold small text-muted mb-2">FOTO / LOGO PRODUK</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="img-upload-preview" id="previewContainerEdit">
                            <i class="bi bi-image text-muted fs-3" id="iconPlaceholderEdit"></i>
                            <img src="" id="imgPreviewEdit" class="w-100 h-100 object-fit-cover d-none" alt="Preview">
                        </div>
                        <div class="flex-grow-1">
                            <input type="file" class="form-control form-control-sm" id="inputFotoEdit" name="gambar" accept="image/png, image/jpeg, image/jpg, image/webp">
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Pilih gambar baru untuk mengganti foto lama.</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="namaEdit" class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="namaEdit" name="nama" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="kategoriEdit" class="form-label fw-semibold">Kategori</label>
                        <select class="form-select" id="kategoriEdit" name="id_kategori">
                            <option value="">Pilih Kategori</option>
                            @foreach($daftarKategori as $kat)
                                <option value="{{ $kat->id_kategori }}">{{ $kat->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="satuanEdit" class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="satuanEdit" name="satuan" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="barcodeEdit" class="form-label fw-semibold">Barcode / Kode SKU</label>
                    <input type="text" class="form-control" id="barcodeEdit" name="barcode">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="hargaBeliEdit" class="form-label fw-semibold">Harga Beli / HPP (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="hargaBeliEdit" name="harga_beli" required min="0">
                    </div>
                    <div class="col-md-6">
                        <label for="hargaJualEdit" class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="hargaJualEdit" name="harga_jual" required min="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="stokEdit" class="form-label fw-semibold">Jumlah Stok Sekarang <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stokEdit" name="stok_sekarang" required min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-tokoku-primary fw-semibold">Perbarui Barang</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // PREVIEW FOTO MODAL TAMBAH
        const inputFotoTambah = document.getElementById('inputFotoTambah');
        const imgPreviewTambah = document.getElementById('imgPreviewTambah');
        const iconPlaceholderTambah = document.getElementById('iconPlaceholderTambah');

        if (inputFotoTambah) {
            inputFotoTambah.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imgPreviewTambah.src = e.target.result;
                        imgPreviewTambah.classList.remove('d-none');
                        iconPlaceholderTambah.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    imgPreviewTambah.src = '';
                    imgPreviewTambah.classList.add('d-none');
                    iconPlaceholderTambah.classList.remove('d-none');
                }
            });
        }

        // PREVIEW FOTO MODAL EDIT
        const modalEdit = document.getElementById('modalEditBarang');
        const inputFotoEdit = document.getElementById('inputFotoEdit');
        const imgPreviewEdit = document.getElementById('imgPreviewEdit');
        const iconPlaceholderEdit = document.getElementById('iconPlaceholderEdit');

        if (modalEdit) {
            modalEdit.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                const id = btn.getAttribute('data-id');
                const gambarUrl = btn.getAttribute('data-gambar');
                
                document.getElementById('namaEdit').value = btn.getAttribute('data-nama');
                document.getElementById('kategoriEdit').value = btn.getAttribute('data-kategori') || '';
                document.getElementById('satuanEdit').value = btn.getAttribute('data-satuan');
                document.getElementById('barcodeEdit').value = btn.getAttribute('data-barcode') || '';
                document.getElementById('hargaBeliEdit').value = btn.getAttribute('data-hargabeli');
                document.getElementById('hargaJualEdit').value = btn.getAttribute('data-hargajual');
                document.getElementById('stokEdit').value = btn.getAttribute('data-stok');

                // Tampilkan foto produk saat ini jika ada
                if (gambarUrl) {
                    imgPreviewEdit.src = gambarUrl;
                    imgPreviewEdit.classList.remove('d-none');
                    iconPlaceholderEdit.classList.add('d-none');
                } else {
                    imgPreviewEdit.src = '';
                    imgPreviewEdit.classList.add('d-none');
                    iconPlaceholderEdit.classList.remove('d-none');
                }

                if (inputFotoEdit) {
                    inputFotoEdit.value = '';
                }

                document.getElementById('formEditBarang').action = `/produk/${id}`;
            });
        }

        if (inputFotoEdit) {
            inputFotoEdit.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imgPreviewEdit.src = e.target.result;
                        imgPreviewEdit.classList.remove('d-none');
                        iconPlaceholderEdit.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endpush

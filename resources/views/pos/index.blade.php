@extends('layouts.app')

@section('title', 'Point of Sales (Kasir)')
@section('page-title', 'Kasir POS')

@push('styles')
<style>
    .pos-search-box {
        position: relative;
    }
    .pos-dropdown-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #ffffff;
        border: 1px solid var(--tokoku-border);
        border-radius: 12px;
        max-height: 320px;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    }
    .pos-item-row {
        transition: all 0.15s ease;
    }
    .pos-item-row:hover {
        background-color: var(--tokoku-primary-light);
        cursor: pointer;
        padding-left: 1.25rem !important;
    }
    .cart-table th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--tokoku-text-muted);
        border-bottom: 2px solid var(--tokoku-border);
    }
    .badge-stok-menipis {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
    }
    /* TOTAL DISPLAY BERGAYA POS PREMIUM */
    .total-display-card {
        background: linear-gradient(135deg, #072a23 0%, #0e7c67 100%);
        color: #ffffff;
        border-radius: 14px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 20px -3px rgba(14, 124, 103, 0.3);
    }
    .total-display-card::after {
        content: '';
        position: absolute;
        right: -30px;
        bottom: -30px;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
        border-radius: 50%;
    }
    .total-number-display {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2.25rem;
        letter-spacing: -0.03em;
    }
    /* QUICK CASH PILL BUTTONS */
    .quick-cash-btn {
        background: #ffffff;
        border: 1px solid var(--tokoku-border);
        color: #334155;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        transition: all 0.15s ease;
    }
    .quick-cash-btn:hover {
        background: var(--tokoku-primary-light);
        border-color: var(--tokoku-primary);
        color: var(--tokoku-primary);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- ALERT SINKRONISASI OFFLINE --}}
    <div id="offlineSyncNotice" class="alert alert-warning d-none d-flex align-items-center justify-content-between mb-3 shadow-sm border-0" role="alert" style="border-radius: 12px; background-color: #fef3c7; color: #92400e;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-cloud-arrow-up-fill fs-4 text-warning"></i>
            <div>
                <strong class="d-block">Ada Transaksi Tertunda di Memori Offline</strong>
                <span id="offlineCountText" class="small">0 transaksi menunggu sinkronisasi ke server.</span>
            </div>
        </div>
        <button id="btnSyncNow" class="btn btn-sm btn-dark px-3 fw-bold">
            <i class="bi bi-arrow-repeat me-1"></i> Sinkronkan Sekarang
        </button>
    </div>

    <div class="row g-3">
        {{-- KOLOM KIRI: SCANNER & KERANJANG BELANJA --}}
        <div class="col-lg-8">
            <div class="card bg-white p-3 p-md-4">
                
                {{-- INPUT PENCARIAN & SCAN BARCODE --}}
                <div class="pos-search-box mb-4">
                    <label for="posSearchInput" class="form-label fw-bold small text-muted" style="letter-spacing: 0.05em;">PENCARIAN CEPAT / SCANNER BARCODE</label>
                    <div class="input-group input-group-lg shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 px-3"><i class="bi bi-upc-scan fs-5" style="color: var(--tokoku-primary);"></i></span>
                        <input type="text" id="posSearchInput" class="form-control border-start-0 ps-1" placeholder="Pindai barcode atau ketik nama barang..." autocomplete="off" autofocus>
                        <button class="btn btn-white border border-start-0 px-3 text-muted" type="button" id="btnClearSearch"><i class="bi bi-x-circle-fill"></i></button>
                    </div>
                    <div id="posSearchResults" class="pos-dropdown-results d-none"></div>
                </div>

                {{-- TABEL KERANJANG BELANJA --}}
                <div class="table-responsive" style="min-height: 340px;">
                    <table class="table align-middle cart-table">
                        <thead>
                            <tr>
                                <th style="width: 38%;">Barang</th>
                                <th style="width: 20%;" class="text-end">Harga</th>
                                <th style="width: 18%;" class="text-center">Kuantitas</th>
                                <th style="width: 19%;" class="text-end">Subtotal</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody">
                            {{-- Rendered by JS --}}
                        </tbody>
                    </table>
                    <div id="cartEmptyState" class="text-center py-5 text-muted">
                        <div class="rounded-circle bg-light d-inline-flex p-3 mb-3 border">
                            <i class="bi bi-cart3 fs-1 text-secondary opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Keranjang Masih Kosong</h6>
                        <small>Arahkan scanner barcode ke kemasan atau ketik nama barang di kolom atas.</small>
                    </div>
                </div>

            </div>
        </div>

        {{-- KOLOM KANAN: DISPLAY TOTAL & CHECKOUT --}}
        <div class="col-lg-4">
            <div class="card bg-white p-3 p-md-4">
                
                {{-- DISPLAY DIGITAL TOTAL AKHIR --}}
                <div class="total-display-card p-3 p-md-4 mb-4">
                    <span class="d-block text-uppercase small fw-bold text-white-50" style="letter-spacing: 0.08em; font-size: 0.75rem;">Total Tagihan Kasir</span>
                    <h2 class="fw-bold mb-0 text-white total-number-display" id="displayTotalAkhir">Rp 0</h2>
                </div>

                {{-- PILIHAN METODE BAYAR --}}
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted" style="letter-spacing: 0.05em;">METODE PEMBAYARAN</label>
                    <div class="btn-group w-100 p-1 bg-light rounded-3 border" role="group">
                        <input type="radio" class="btn-check" name="metodeBayar" id="payTunai" value="tunai" checked>
                        <label class="btn btn-sm btn-outline-success border-0 rounded-2 py-2 fw-bold" for="payTunai">
                            <i class="bi bi-cash me-1"></i> Tunai
                        </label>

                        <input type="radio" class="btn-check" name="metodeBayar" id="payTransfer" value="transfer">
                        <label class="btn btn-sm btn-outline-success border-0 rounded-2 py-2 fw-bold" for="payTransfer">
                            <i class="bi bi-credit-card me-1"></i> Transfer
                        </label>

                        <input type="radio" class="btn-check" name="metodeBayar" id="payHutang" value="hutang">
                        <label class="btn btn-sm btn-outline-danger border-0 rounded-2 py-2 fw-bold" for="payHutang">
                            <i class="bi bi-journal-text me-1"></i> Kasbon
                        </label>
                    </div>
                </div>

                {{-- PILIH PELANGGAN JIKA KASBON --}}
                <div id="sectionPelangganKasbon" class="mb-3 d-none p-3 rounded-3 bg-danger-subtle border border-danger-subtle">
                    <label for="selectPelanggan" class="form-label fw-bold small text-danger">PILIH PELANGGAN KASBON *</label>
                    <select id="selectPelanggan" class="form-select bg-white">
                        <option value="">-- Pilih Nama Pelanggan --</option>
                        @foreach($daftarPelanggan as $pelanggan)
                            <option value="{{ $pelanggan->id_pelanggan }}" data-hutang="{{ $pelanggan->total_hutang }}">
                                {{ $pelanggan->nama }} (Hutang: Rp {{ number_format($pelanggan->total_hutang, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger d-block mt-1" style="font-size: 0.72rem;">* Tagihan akan otomatis masuk ke saldo utang pelanggan.</small>
                </div>

                {{-- DISKON --}}
                <div class="mb-3">
                    <label for="inputDiskon" class="form-label fw-semibold small text-muted">Potongan Diskon (Rp)</label>
                    <input type="number" id="inputDiskon" class="form-control" value="0" min="0">
                </div>

                {{-- UANG DITERIMA & KEMBALIAN (TUNAI) --}}
                <div id="sectionUangTunai" class="mb-4">
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="inputUangBayar" class="form-label fw-semibold small mb-0">Uang Diterima (Rp)</label>
                            <div class="d-flex gap-1" id="quickCashPills">
                                <button type="button" class="quick-cash-btn" data-amt="pas">Uang Pas</button>
                                <button type="button" class="quick-cash-btn" data-amt="50000">50k</button>
                                <button type="button" class="quick-cash-btn" data-amt="100000">100k</button>
                            </div>
                        </div>
                        <input type="number" id="inputUangBayar" class="form-control form-control-lg fw-bold" placeholder="0" style="font-family: 'JetBrains Mono', monospace;">
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light border">
                        <span class="small fw-semibold text-muted">Uang Kembalian:</span>
                        <span class="fw-bold fs-5 text-success" id="displayKembalian" style="font-family: 'JetBrains Mono', monospace;">Rp 0</span>
                    </div>
                </div>

                {{-- TOMBOL SUBMIT --}}
                <button id="btnBayarSubmit" class="btn btn-tokoku-primary btn-lg w-100 fw-bold py-3 shadow" disabled>
                    <i class="bi bi-check2-circle me-1"></i> SELESAIKAN TRANSAKSI
                </button>

            </div>
        </div>
    </div>
</div>

{{-- MODAL NOTA STRUK --}}
<div class="modal fade" id="modalNotaSuccess" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4 border-0 shadow-lg" style="border-radius: 16px;">
            <div class="mb-3">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex p-3">
                    <i class="bi bi-check-lg fs-1"></i>
                </div>
            </div>
            <h4 class="fw-bold mb-1">Transaksi Berhasil!</h4>
            <p class="text-muted small mb-3" id="modalNomorNota">Nomor Nota: -</p>
            <div class="p-3 bg-light rounded-3 border text-start mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Tagihan:</span>
                    <strong class="fs-6" id="modalTotalAkhir">Rp 0</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Kembalian Kasir:</span>
                    <strong class="text-success fs-5 fw-bold" id="modalKembalian">Rp 0</strong>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-secondary py-2 fw-semibold" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak Struk (Nota)
                </button>
                <button type="button" class="btn btn-tokoku-primary py-2 fw-bold" data-bs-dismiss="modal" id="btnNextTransaction">
                    <i class="bi bi-plus-lg me-1"></i> Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const STORAGE_KEY_CART = 'sitoko_pos_cart';
        const STORAGE_KEY_OFFLINE = 'sitoko_offline_orders';

        let cart = JSON.parse(localStorage.getItem(STORAGE_KEY_CART) || '[]');
        let offlineOrders = JSON.parse(localStorage.getItem(STORAGE_KEY_OFFLINE) || '[]');

        const searchInput = document.getElementById('posSearchInput');
        const searchResults = document.getElementById('posSearchResults');
        const btnClearSearch = document.getElementById('btnClearSearch');
        const cartTableBody = document.getElementById('cartTableBody');
        const cartEmptyState = document.getElementById('cartEmptyState');
        const displayTotalAkhir = document.getElementById('displayTotalAkhir');
        const inputDiskon = document.getElementById('inputDiskon');
        const inputUangBayar = document.getElementById('inputUangBayar');
        const displayKembalian = document.getElementById('displayKembalian');
        const btnBayarSubmit = document.getElementById('btnBayarSubmit');
        const sectionUangTunai = document.getElementById('sectionUangTunai');
        const sectionPelangganKasbon = document.getElementById('sectionPelangganKasbon');
        const selectPelanggan = document.getElementById('selectPelanggan');

        const offlineSyncNotice = document.getElementById('offlineSyncNotice');
        const offlineCountText = document.getElementById('offlineCountText');
        const btnSyncNow = document.getElementById('btnSyncNow');

        const modalNotaSuccess = new bootstrap.Modal(document.getElementById('modalNotaSuccess'));

        // QUICK CASH PILLS HANDLER
        document.querySelectorAll('.quick-cash-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const val = this.dataset.amt;
                const subtotal = cart.reduce((acc, curr) => acc + (curr.harga_jual * curr.jumlah), 0);
                const diskon = parseFloat(inputDiskon.value) || 0;
                const totalAkhir = Math.max(0, subtotal - diskon);

                if (val === 'pas') {
                    inputUangBayar.value = totalAkhir;
                } else {
                    inputUangBayar.value = parseFloat(val);
                }
                hitungTotal();
            });
        });

        // 1. CARI BARANG / AUTOCOMPLETE
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            if (!q) {
                searchResults.classList.add('d-none');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('pos.cari-barang') }}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.success && res.data.length > 0) {
                            renderSearchResults(res.data);
                        } else {
                            searchResults.innerHTML = '<div class="p-3 text-muted small text-center">Barang tidak ditemukan.</div>';
                            searchResults.classList.remove('d-none');
                        }
                    })
                    .catch(() => {
                        searchResults.classList.add('d-none');
                    });
            }, 200);
        });

        function renderSearchResults(items) {
            let html = '';
            items.forEach(item => {
                const isMenipis = item.stok_sekarang <= 5;
                html += `
                    <div class="pos-item-row p-3 border-bottom d-flex align-items-center justify-content-between" data-item='${JSON.stringify(item)}'>
                        <div>
                            <div class="fw-bold text-dark">${item.nama}</div>
                            <span class="text-muted small">Barcode: ${item.barcode || '-'}</span>
                            ${isMenipis ? '<span class="badge badge-stok-menipis ms-2">Stok Sisa: ' + item.stok_sekarang + '</span>' : '<span class="badge bg-light text-secondary border ms-2">Stok: ' + item.stok_sekarang + '</span>'}
                        </div>
                        <div class="text-end">
                            <span class="fw-bold fs-6" style="color: var(--tokoku-primary); font-family: 'JetBrains Mono', monospace;">Rp ${parseInt(item.harga_jual).toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                `;
            });
            searchResults.innerHTML = html;
            searchResults.classList.remove('d-none');

            document.querySelectorAll('.pos-item-row').forEach(row => {
                row.addEventListener('click', function () {
                    const item = JSON.parse(this.dataset.item);
                    addToCart(item);
                    searchResults.classList.add('d-none');
                    searchInput.value = '';
                    searchInput.focus();
                });
            });
        }

        btnClearSearch.addEventListener('click', () => {
            searchInput.value = '';
            searchResults.classList.add('d-none');
            searchInput.focus();
        });

        // 2. LOGIKA KERANJANG
        function addToCart(item) {
            const existing = cart.find(c => c.id_barang === item.id_barang);
            if (existing) {
                if (existing.jumlah + 1 > item.stok_sekarang) {
                    alert(`Stok tidak mencukupi! Maksimal stok: ${item.stok_sekarang}`);
                    return;
                }
                existing.jumlah += 1;
            } else {
                if (item.stok_sekarang < 1) {
                    alert('Barang ini sudah habis!');
                    return;
                }
                cart.push({
                    id_barang: item.id_barang,
                    nama: item.nama,
                    harga_jual: parseFloat(item.harga_jual),
                    stok_sekarang: item.stok_sekarang,
                    jumlah: 1
                });
            }
            saveCart();
            renderCart();
        }

        function saveCart() {
            localStorage.setItem(STORAGE_KEY_CART, JSON.stringify(cart));
        }

        function renderCart() {
            if (cart.length === 0) {
                cartTableBody.innerHTML = '';
                cartEmptyState.classList.remove('d-none');
                btnBayarSubmit.disabled = true;
                hitungTotal();
                return;
            }

            cartEmptyState.classList.add('d-none');
            btnBayarSubmit.disabled = false;

            let html = '';
            cart.forEach((item, index) => {
                const subtotal = item.harga_jual * item.jumlah;
                const isMenipis = item.stok_sekarang <= 5;
                html += `
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">${item.nama}</div>
                            ${isMenipis ? '<span class="badge badge-stok-menipis">Sisa: ' + item.stok_sekarang + '</span>' : ''}
                        </td>
                        <td class="text-end" style="font-family: 'JetBrains Mono', monospace;">Rp ${item.harga_jual.toLocaleString('id-ID')}</td>
                        <td class="text-center">
                            <div class="input-group input-group-sm mx-auto shadow-sm rounded-3 overflow-hidden" style="width: 105px;">
                                <button class="btn btn-light border btn-cart-min" data-index="${index}">-</button>
                                <input type="number" class="form-control text-center input-cart-qty px-1 fw-bold" data-index="${index}" value="${item.jumlah}" min="1" max="${item.stok_sekarang}">
                                <button class="btn btn-light border btn-cart-plus" data-index="${index}">+</button>
                            </div>
                        </td>
                        <td class="text-end fw-bold" style="color: var(--tokoku-primary); font-family: 'JetBrains Mono', monospace;">Rp ${subtotal.toLocaleString('id-ID')}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-link text-danger p-0 btn-cart-del" data-index="${index}"><i class="bi bi-trash3-fill"></i></button>
                        </td>
                    </tr>
                `;
            });

            cartTableBody.innerHTML = html;

            document.querySelectorAll('.btn-cart-min').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idx = this.dataset.index;
                    if (cart[idx].jumlah > 1) {
                        cart[idx].jumlah--;
                    } else {
                        cart.splice(idx, 1);
                    }
                    saveCart();
                    renderCart();
                });
            });

            document.querySelectorAll('.btn-cart-plus').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idx = this.dataset.index;
                    if (cart[idx].jumlah + 1 > cart[idx].stok_sekarang) {
                        alert(`Stok tidak mencukupi! Maksimal: ${cart[idx].stok_sekarang}`);
                        return;
                    }
                    cart[idx].jumlah++;
                    saveCart();
                    renderCart();
                });
            });

            document.querySelectorAll('.input-cart-qty').forEach(inp => {
                inp.addEventListener('change', function () {
                    const idx = this.dataset.index;
                    let val = parseInt(this.value) || 1;
                    if (val > cart[idx].stok_sekarang) {
                        val = cart[idx].stok_sekarang;
                        alert(`Stok maksimal: ${cart[idx].stok_sekarang}`);
                    }
                    cart[idx].jumlah = Math.max(1, val);
                    saveCart();
                    renderCart();
                });
            });

            document.querySelectorAll('.btn-cart-del').forEach(btn => {
                btn.addEventListener('click', function () {
                    cart.splice(this.dataset.index, 1);
                    saveCart();
                    renderCart();
                });
            });

            hitungTotal();
        }

        function hitungTotal() {
            const subtotal = cart.reduce((acc, curr) => acc + (curr.harga_jual * curr.jumlah), 0);
            const diskon = parseFloat(inputDiskon.value) || 0;
            const totalAkhir = Math.max(0, subtotal - diskon);

            displayTotalAkhir.textContent = 'Rp ' + totalAkhir.toLocaleString('id-ID');

            const uangBayar = parseFloat(inputUangBayar.value) || 0;
            const kembalian = Math.max(0, uangBayar - totalAkhir);
            displayKembalian.textContent = 'Rp ' + kembalian.toLocaleString('id-ID');
        }

        inputDiskon.addEventListener('input', hitungTotal);
        inputUangBayar.addEventListener('input', hitungTotal);

        document.querySelectorAll('input[name="metodeBayar"]').forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'hutang') {
                    sectionPelangganKasbon.classList.remove('d-none');
                    sectionUangTunai.classList.add('d-none');
                } else if (this.value === 'transfer') {
                    sectionPelangganKasbon.classList.add('d-none');
                    sectionUangTunai.classList.add('d-none');
                } else {
                    sectionPelangganKasbon.classList.add('d-none');
                    sectionUangTunai.classList.remove('d-none');
                }
            });
        });

        // 3. CHECKOUT
        btnBayarSubmit.addEventListener('click', function () {
            if (cart.length === 0) return;

            const metode = document.querySelector('input[name="metodeBayar"]:checked').value;
            const idPelanggan = selectPelanggan.value;

            if (metode === 'hutang' && !idPelanggan) {
                alert('Pilih nama pelanggan untuk transaksi Kasbon/Hutang!');
                selectPelanggan.focus();
                return;
            }

            const subtotal = cart.reduce((acc, curr) => acc + (curr.harga_jual * curr.jumlah), 0);
            const diskon = parseFloat(inputDiskon.value) || 0;
            const totalAkhir = Math.max(0, subtotal - diskon);
            const uangBayar = parseFloat(inputUangBayar.value) || 0;

            if (metode === 'tunai' && uangBayar < totalAkhir) {
                alert('Nominal uang yang diterima kurang dari total belanja!');
                inputUangBayar.focus();
                return;
            }

            const payload = {
                metode_bayar: metode,
                id_pelanggan: idPelanggan ? parseInt(idPelanggan) : null,
                diskon: diskon,
                bayar: uangBayar,
                items: cart.map(c => ({ id_barang: c.id_barang, jumlah: c.jumlah })),
                tanggal: new Date().toISOString().slice(0, 19).replace('T', ' ')
            };

            if (!navigator.onLine) {
                simpanOffline(payload, totalAkhir, uangBayar);
                return;
            }

            btnBayarSubmit.disabled = true;
            btnBayarSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

            fetch(`{{ route('pos.checkout') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                btnBayarSubmit.disabled = false;
                btnBayarSubmit.innerHTML = '<i class="bi bi-check2-circle me-1"></i> SELESAIKAN TRANSAKSI';

                if (res.success) {
                    showNotaSuccess(res.data.nomor_nota, res.data.total_akhir, res.data.kembalian);
                    resetCart();
                } else {
                    alert('Gagal memproses transaksi: ' + res.message);
                }
            })
            .catch(() => {
                simpanOffline(payload, totalAkhir, uangBayar);
                btnBayarSubmit.disabled = false;
                btnBayarSubmit.innerHTML = '<i class="bi bi-check2-circle me-1"></i> SELESAIKAN TRANSAKSI';
            });
        });

        function simpanOffline(payload, totalAkhir, uangBayar) {
            offlineOrders.push(payload);
            localStorage.setItem(STORAGE_KEY_OFFLINE, JSON.stringify(offlineOrders));
            updateOfflineBadge();
            const kembalian = Math.max(0, uangBayar - totalAkhir);
            showNotaSuccess('OFFLINE-' + Date.now().toString().slice(-4), totalAkhir, kembalian);
            resetCart();
            alert('Transaksi disimpan secara offline di browser. Akan otomatis disinkronkan saat online kembali.');
        }

        function showNotaSuccess(nomorNota, total, kembalian) {
            document.getElementById('modalNomorNota').textContent = 'Nomor Nota: ' + nomorNota;
            document.getElementById('modalTotalAkhir').textContent = 'Rp ' + parseInt(total).toLocaleString('id-ID');
            document.getElementById('modalKembalian').textContent = 'Rp ' + parseInt(kembalian).toLocaleString('id-ID');
            modalNotaSuccess.show();
        }

        function resetCart() {
            cart = [];
            saveCart();
            inputDiskon.value = 0;
            inputUangBayar.value = '';
            selectPelanggan.value = '';
            renderCart();
        }

        function updateOfflineBadge() {
            offlineOrders = JSON.parse(localStorage.getItem(STORAGE_KEY_OFFLINE) || '[]');
            if (offlineOrders.length > 0) {
                offlineSyncNotice.classList.remove('d-none');
                offlineCountText.textContent = `${offlineOrders.length} transaksi menunggu sinkronisasi ke server.`;
            } else {
                offlineSyncNotice.classList.add('d-none');
            }
        }

        function syncOfflineOrders() {
            if (offlineOrders.length === 0 || !navigator.onLine) return;

            btnSyncNow.disabled = true;
            btnSyncNow.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyinkronkan...';

            fetch(`{{ route('pos.sync-offline') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ transactions: offlineOrders })
            })
            .then(res => res.json())
            .then(res => {
                btnSyncNow.disabled = false;
                btnSyncNow.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Sinkronkan Sekarang';
                if (res.success && res.synced_count > 0) {
                    offlineOrders = [];
                    localStorage.setItem(STORAGE_KEY_OFFLINE, JSON.stringify([]));
                    updateOfflineBadge();
                    alert(res.message);
                }
            })
            .catch(() => {
                btnSyncNow.disabled = false;
                btnSyncNow.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Sinkronkan Sekarang';
            });
        }

        btnSyncNow.addEventListener('click', syncOfflineOrders);
        window.addEventListener('sitoko:network-online', syncOfflineOrders);
        window.addEventListener('online', syncOfflineOrders);

        renderCart();
        updateOfflineBadge();
    });
</script>
@endpush

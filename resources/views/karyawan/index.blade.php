@extends('layouts.app')

@section('title', 'Manajemen Karyawan')
@section('page-title', 'Manajemen Staf & Akun Pengguna')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Daftar Akun Pengguna</h1>
            <p class="text-muted small mb-0">Kelola hak akses Owner (Admin) dan Kasir operasional toko.</p>
        </div>
        <button class="btn btn-tokoku-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahKaryawan">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Akun
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 30%;">Nama Lengkap</th>
                            <th style="width: 25%;">Username</th>
                            <th style="width: 20%;">Peran (Role)</th>
                            <th style="width: 20%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($karyawan as $idx => $k)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold">{{ $k->nama }}</div>
                                    <small class="text-muted">{{ $k->nota_count }} transaksi diproses</small>
                                </td>
                                <td><code>{{ $k->username }}</code></td>
                                <td>
                                    @if($k->peran === 'admin')
                                        <span class="badge badge-role-admin">Owner / Admin</span>
                                    @else
                                        <span class="badge badge-role-kasir">Kasir Toko</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditKaryawan"
                                                data-id="{{ $k->id_karyawan }}"
                                                data-nama="{{ $k->nama }}"
                                                data-username="{{ $k->username }}"
                                                data-peran="{{ $k->peran }}"
                                                title="Edit Akun">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        @if(Auth::id() !== $k->id_karyawan)
                                            <form method="POST" action="{{ route('karyawan.destroy', $k->id_karyawan) }}" class="d-inline" onsubmit="return confirm('Hapus akun {{ $k->nama }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Akun">
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
        </div>
    </div>
</div>

{{-- MODAL TAMBAH KARYAWAN --}}
<div class="modal fade" id="modalTambahKaryawan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('karyawan.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Akun Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="namaTambah" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="namaTambah" name="nama" required placeholder="Contoh: Muhammad Budi">
                </div>
                <div class="mb-3">
                    <label for="usernameTambah" class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="usernameTambah" name="username" required placeholder="budi_kasir">
                </div>
                <div class="mb-3">
                    <label for="passwordTambah" class="form-label fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="passwordTambah" name="password" required minlength="6" placeholder="Minimal 6 karakter">
                </div>
                <div class="mb-3">
                    <label for="peranTambah" class="form-label fw-semibold">Hak Akses (Peran) <span class="text-danger">*</span></label>
                    <select class="form-select" id="peranTambah" name="peran" required>
                        <option value="kasir" selected>Kasir (Hanya POS & Kasbon, HPP disembunyikan)</option>
                        <option value="admin">Owner / Admin (Kendali penuh, Laba/HPP & AI)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-tokoku-primary fw-semibold">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT KARYAWAN --}}
<div class="modal fade" id="modalEditKaryawan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formEditKaryawan" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Akun Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="namaEdit" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="namaEdit" name="nama" required>
                </div>
                <div class="mb-3">
                    <label for="usernameEdit" class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="usernameEdit" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="passwordEdit" class="form-label fw-semibold">Ganti Kata Sandi (Opsional)</label>
                    <input type="password" class="form-control" id="passwordEdit" name="password" minlength="6" placeholder="Kosongkan jika tidak ingin mengubah sandi">
                </div>
                <div class="mb-3">
                    <label for="peranEdit" class="form-label fw-semibold">Hak Akses (Peran) <span class="text-danger">*</span></label>
                    <select class="form-select" id="peranEdit" name="peran" required>
                        <option value="kasir">Kasir (Hanya POS & Kasbon, HPP disembunyikan)</option>
                        <option value="admin">Owner / Admin (Kendali penuh, Laba/HPP & AI)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-tokoku-primary fw-semibold">Perbarui Akun</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEdit = document.getElementById('modalEditKaryawan');
        if (modalEdit) {
            modalEdit.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                const id = btn.getAttribute('data-id');

                document.getElementById('namaEdit').value = btn.getAttribute('data-nama');
                document.getElementById('usernameEdit').value = btn.getAttribute('data-username');
                document.getElementById('peranEdit').value = btn.getAttribute('data-peran');
                document.getElementById('passwordEdit').value = '';

                document.getElementById('formEditKaryawan').action = `/karyawan/${id}`;
            });
        }
    });
</script>
@endpush


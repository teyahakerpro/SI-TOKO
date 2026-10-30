{{--
    Halaman Login Tokoku
    Bootstrap 5.3 & Bootstrap Icons (CDN).
--}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Tokoku</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --tokoku-primary: #0e7c67;
            --tokoku-primary-dark: #0a5f4f;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: #1b2428;
        }

        /* Panel kiri: branding */
        .brand-panel {
            background: linear-gradient(145deg, var(--tokoku-primary) 0%, var(--tokoku-primary-dark) 100%);
            position: relative;
            overflow: hidden;
        }
        .brand-panel::before,
        .brand-panel::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .07);
        }
        .brand-panel::before { width: 380px; height: 380px; right: -140px; bottom: -140px; }
        .brand-panel::after  { width: 260px; height: 260px; left: -110px; top: -90px; }

        .logo-kotak {
            width: 48px;
            height: 48px;
            background: #fff;
            color: var(--tokoku-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .logo-kotak-sm {
            width: 40px;
            height: 40px;
            background: var(--tokoku-primary);
            color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .fitur-ikon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .auth-card { max-width: 420px; }

        .btn-tokoku {
            background-color: var(--tokoku-primary);
            border-color: var(--tokoku-primary);
            color: #fff;
        }
        .btn-tokoku:hover,
        .btn-tokoku:focus {
            background-color: var(--tokoku-primary-dark);
            border-color: var(--tokoku-primary-dark);
            color: #fff;
        }
        .btn-tokoku:disabled { opacity: .75; }

        .form-control:focus,
        .btn-outline-secondary:focus {
            border-color: var(--tokoku-primary);
            box-shadow: 0 0 0 .2rem rgba(14, 124, 103, .15);
        }
        .input-group-text { color: #6b7780; }
        .input-group:focus-within .input-group-text {
            border-color: var(--tokoku-primary);
            color: var(--tokoku-primary);
        }

        .form-check-input:checked {
            background-color: var(--tokoku-primary);
            border-color: var(--tokoku-primary);
        }
        .form-check-input:focus { box-shadow: 0 0 0 .2rem rgba(14, 124, 103, .15); }
    </style>
</head>
<body class="bg-white">

<div class="row g-0 min-vh-100">

    {{-- PANEL KIRI: branding (tampil di layar besar) --}}
    <aside class="col-lg-6 d-none d-lg-flex brand-panel text-white p-5 flex-column justify-content-between">
        <div class="d-flex align-items-center gap-3 position-relative">
            <div class="logo-kotak">T</div>
            <div class="lh-sm">
                <div class="fw-semibold fs-5">Tokoku</div>
                <small class="opacity-75">Sistem Informasi Terpadu</small>
            </div>
        </div>

        <div class="position-relative">
            <h2 class="fw-bold mb-3" style="font-size: 2.25rem; line-height: 1.2;">
                Kelola stok, kasir, dan kasbon dalam satu aplikasi.
            </h2>
            <p class="opacity-75 mb-4">
                Transaksi, stok, dan piutang pelanggan tercatat rapi dan real-time.
            </p>
            <ul class="list-unstyled mb-0 d-grid gap-3">
                <li class="d-flex align-items-center gap-3">
                    <span class="fitur-ikon"><i class="bi bi-check-lg"></i></span>
                    <span>Stok otomatis berkurang setiap transaksi</span>
                </li>
                <li class="d-flex align-items-center gap-3">
                    <span class="fitur-ikon"><i class="bi bi-check-lg"></i></span>
                    <span>Kasbon pelanggan tercatat dan mudah dipantau</span>
                </li>
                <li class="d-flex align-items-center gap-3">
                    <span class="fitur-ikon"><i class="bi bi-check-lg"></i></span>
                    <span>Rekap kas harian siap saat tutup toko</span>
                </li>
            </ul>
        </div>

        <small class="opacity-75 position-relative">
            &copy; {{ date('Y') }} Tokoku &middot; Akses khusus Owner dan Kasir
        </small>
    </aside>

    {{-- PANEL KANAN: form login --}}
    <main class="col-12 col-lg-6 d-flex align-items-center justify-content-center px-4 py-5 p-md-5">
        <div class="w-100 auth-card">

            {{-- Logo versi layar kecil --}}
            <div class="d-lg-none d-flex align-items-center gap-2 mb-4">
                <div class="logo-kotak-sm">T</div>
                <span class="fw-semibold fs-5">Tokoku</span>
            </div>

            <h1 class="h3 fw-bold mb-1">Selamat datang kembali</h1>
            <p class="text-secondary mb-4">Masuk menggunakan akun yang diberikan pemilik toko.</p>

            @if (session('status'))
                <div class="alert alert-success d-flex align-items-center gap-2 py-2" role="status">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-start gap-2 py-2" role="alert">
                    <i class="bi bi-exclamation-octagon-fill mt-1"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" id="formLogin">
                @csrf

                {{-- Nama pengguna --}}
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Nama pengguna</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            class="form-control @error('username') is-invalid @enderror"
                            placeholder="Masukkan nama pengguna"
                            autocomplete="username"
                            maxlength="255"
                            autofocus
                            required>
                    </div>
                </div>

                {{-- Kata sandi --}}
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Kata sandi</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            maxlength="255"
                            required>
                        <button class="btn btn-outline-secondary" type="button" id="tombolLihatSandi" aria-label="Tampilkan kata sandi">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text text-warning d-none" id="peringatanCaps">
                        <i class="bi bi-exclamation-triangle-fill"></i> Caps Lock sedang aktif.
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <span class="small text-secondary">Lupa kata sandi? Hubungi pemilik</span>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-tokoku btn-lg fw-semibold" id="tombolMasuk">
                        Masuk
                    </button>
                </div>
            </form>

            <p class="small text-secondary text-center mt-4 mb-0">
                <i class="bi bi-shield-lock me-1"></i>
                Akun Kasir hanya dapat mengakses modul transaksi.
            </p>
        </div>
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        const form = document.getElementById('formLogin');
        const tombolMasuk = document.getElementById('tombolMasuk');
        const inputSandi = document.getElementById('password');
        const tombolLihat = document.getElementById('tombolLihatSandi');
        const peringatanCaps = document.getElementById('peringatanCaps');
        const teksAwal = tombolMasuk.innerHTML;

        // Tampilkan / sembunyikan kata sandi
        tombolLihat.addEventListener('click', () => {
            const tampil = inputSandi.type === 'password';
            inputSandi.type = tampil ? 'text' : 'password';
            tombolLihat.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            tombolLihat.querySelector('i').className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
        });

        // Peringatan Caps Lock
        inputSandi.addEventListener('keyup', (event) => {
            peringatanCaps.classList.toggle('d-none', !event.getModifierState('CapsLock'));
        });

        // Indikator loading saat form dikirim
        form.addEventListener('submit', () => {
            tombolMasuk.disabled = true;
            tombolMasuk.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memproses...';
        });

        // Kembalikan tombol bila halaman dibuka lewat tombol Back
        window.addEventListener('pageshow', () => {
            tombolMasuk.disabled = false;
            tombolMasuk.innerHTML = teksAwal;
        });
    })();
</script>
</body>
</html>

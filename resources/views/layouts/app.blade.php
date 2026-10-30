{{--
    Layout Utama SITOKO (Sistem Informasi Tokoku)
    Peningkatan Desain: Modern SaaS / Elevated Glass & Soft Diffused Shadows
    Perbaikan: Fluid Ultra-wide Layout, Live Digital Clock & Quick Actions Navbar
--}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SITOKO') - Sistem Informasi Tokoku</title>

    <!-- Google Font: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --tokoku-primary: #0e7c67;
            --tokoku-primary-dark: #074e40;
            --tokoku-primary-light: #e6f6f2;
            --tokoku-primary-glow: rgba(14, 124, 103, 0.15);
            --tokoku-sidebar-bg: #072a23;
            --tokoku-sidebar-hover: rgba(255, 255, 255, 0.08);
            --tokoku-accent: #f59e0b;
            --tokoku-bg: #f8fafc;
            --tokoku-card: #ffffff;
            --tokoku-text: #0f172a;
            --tokoku-text-muted: #64748b;
            --tokoku-border: rgba(226, 232, 240, 0.85);
            --sidebar-width: 270px;
            --shadow-card: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.02);
            --shadow-card-hover: 0 20px 25px -5px rgba(14, 124, 103, 0.08), 0 10px 10px -5px rgba(14, 124, 103, 0.04);
            --shadow-btn: 0 4px 14px 0 rgba(14, 124, 103, 0.25);
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--tokoku-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(14, 124, 103, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.03) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--tokoku-text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* CARD ELEVATION */
        .card {
            border: 1px solid var(--tokoku-border) !important;
            box-shadow: var(--shadow-card) !important;
            border-radius: 14px !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            box-shadow: var(--shadow-card-hover) !important;
        }

        /* SIDEBAR MODERN DARK EMERALD */
        #sidebar-wrapper {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #072a23 0%, #051e19 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 1.5rem 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .brand-logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #10b981 0%, #0e7c67 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 1.35rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
        }

        .sidebar-nav {
            padding: 1.25rem 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex-grow: 1;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.4);
            padding: 0.85rem 0.85rem 0.4rem 0.85rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 0.95rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .sidebar-link i {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.5);
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background-color: var(--tokoku-sidebar-hover);
        }

        .sidebar-link:hover i {
            color: #34d399;
            transform: translateX(2px);
        }

        .sidebar-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(14, 124, 103, 0.25) 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .sidebar-link.active i {
            color: #34d399;
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(0, 0, 0, 0.15);
        }

        /* MAIN CONTENT FLUID FULL-WIDTH (ANTI RUANG KOSONG SAAT ZOOM-OUT) */
        #page-content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - var(--sidebar-width));
            max-width: 100%;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* TOP NAVBAR */
        .top-navbar {
            height: 72px;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--tokoku-border);
            padding: 0 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        /* WIDGET NAVBAR: LIVE CLOCK & BRANCH PILL */
        .navbar-clock-pill {
            background-color: #f1f5f9;
            border: 1px solid var(--tokoku-border);
            border-radius: 50rem;
            padding: 0.35rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-branch-pill {
            background-color: var(--tokoku-primary-light);
            border: 1px solid rgba(14, 124, 103, 0.2);
            color: var(--tokoku-primary);
            border-radius: 50rem;
            padding: 0.35rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* BUTTONS */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn-tokoku-primary {
            background: linear-gradient(135deg, var(--tokoku-primary) 0%, var(--tokoku-primary-dark) 100%);
            color: #ffffff;
            border: none;
            box-shadow: var(--shadow-btn);
        }

        .btn-tokoku-primary:hover,
        .btn-tokoku-primary:focus {
            background: linear-gradient(135deg, #10b981 0%, var(--tokoku-primary) 100%);
            box-shadow: 0 6px 18px 0 rgba(14, 124, 103, 0.35);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .badge-role-admin {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: 700;
            font-size: 0.7rem;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .badge-role-kasir {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            font-size: 0.7rem;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            border: 1px solid rgba(3, 105, 161, 0.2);
        }

        .network-status-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.4rem 0.85rem;
            border-radius: 50rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .network-status-online {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .network-status-offline {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            animation: pulse-red 1.5s infinite;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        .status-dot-online { background-color: #10b981; }
        .status-dot-offline { background-color: #ef4444; }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--tokoku-primary) !important;
            box-shadow: 0 0 0 0.22rem var(--tokoku-primary-glow) !important;
        }

        /* MOBILE RESPONSIVE DRAWER */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                transform: translateX(-100%);
            }
            #sidebar-wrapper.show {
                transform: translateX(0);
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.25);
            }
            #page-content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background-color: rgba(7, 42, 35, 0.5);
                backdrop-filter: blur(4px);
                z-index: 1025;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="d-flex" id="wrapper">

    {{-- SIDEBAR UTAMA --}}
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <div class="brand-logo-icon">T</div>
            <div class="lh-sm">
                <div class="fw-bold fs-5 text-white" style="letter-spacing: -0.02em;">SITOKO</div>
                <small style="font-size: 0.72rem; color: rgba(255, 255, 255, 0.5);">Sistem Informasi Tokoku</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Operasional Harian</div>
            
            <a href="{{ route('produk.index') }}" class="sidebar-link {{ request()->routeIs('produk.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                <span>Katalog Barang</span>
            </a>

            <a href="{{ route('pos.index') }}" class="sidebar-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="bi bi-calculator"></i>
                <span>Kasir (POS)</span>
            </a>

            <a href="{{ route('pelanggan.index') }}" class="sidebar-link {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Pelanggan & Kasbon</span>
            </a>

            @if(Auth::check() && Auth::user()->peran === 'admin')
                <div class="nav-section-title mt-3">Owner & Strategi</div>

                <a href="{{ route('laporan.index') }}" class="sidebar-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Laporan & Omzet</span>
                </a>

                <a href="{{ route('ai.index') }}" class="sidebar-link {{ request()->routeIs('ai.*') ? 'active' : '' }}">
                    <i class="bi bi-robot"></i>
                    <span>Asisten Cerdas (AI)</span>
                </a>

                <a href="{{ route('karyawan.index') }}" class="sidebar-link {{ request()->routeIs('karyawan.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i>
                    <span>Manajemen Karyawan</span>
                </a>
            @endif
        </nav>

        {{-- PROFIL KECIL BAWAH SIDEBAR --}}
        @if(Auth::check())
            <div class="sidebar-footer d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem; flex-shrink: 0;">
                    {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->username, 0, 1)) }}
                </div>
                <div class="text-truncate lh-sm">
                    <div class="text-white small fw-bold text-truncate">{{ Auth::user()->nama ?? Auth::user()->username }}</div>
                    <span style="font-size: 0.7rem; color: #34d399;">{{ Auth::user()->label_peran }}</span>
                </div>
            </div>
        @endif
    </aside>

    {{-- BACKDROP MOBILE --}}
    <div id="sidebar-backdrop" class="sidebar-backdrop d-none"></div>

    {{-- KONTEN UTAMA --}}
    <div id="page-content-wrapper">

        {{-- TOP NAVBAR --}}
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-lg-none btn-sm" id="btnToggleSidebar" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                
                <div>
                    <h5 class="mb-0 fw-bold" style="letter-spacing: -0.02em;">@yield('page-title', 'Dashboard')</h5>
                </div>

                {{-- WIDGET INFORMASI TOKO & JAM DIGITAL (MENGISI AREA KOSONG) --}}
                <div class="d-none d-xl-flex align-items-center gap-2 ms-3 ps-3 border-start">
                    <span class="navbar-branch-pill">
                        <i class="bi bi-shop"></i> Tokoku Pusat
                    </span>
                    <span class="navbar-clock-pill">
                        <i class="bi bi-clock-history text-secondary"></i>
                        <span id="liveClockText">Memuat waktu...</span>
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 gap-sm-3">
                {{-- QUICK ACTION MENU --}}
                <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-success d-none d-md-inline-flex align-items-center gap-1 rounded-pill px-3 fw-bold" title="Mulai Transaksi Baru">
                    <i class="bi bi-cart-plus-fill"></i>
                    <span>Kasir Baru</span>
                </a>

                {{-- STATUS JARINGAN --}}
                <div id="networkStatusBadge" class="network-status-badge network-status-online" title="Status koneksi ke server">
                    <span class="status-dot status-dot-online" id="networkStatusDot"></span>
                    <span id="networkStatusText">Online</span>
                </div>

                {{-- USER PROFILE DROPDOWN --}}
                @if(Auth::check())
                    <div class="dropdown">
                        <button class="btn btn-white d-flex align-items-center gap-2 border px-3 py-1 rounded-pill shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: #ffffff;">
                            <div class="text-start d-none d-sm-block lh-1">
                                <span class="{{ Auth::user()->peran === 'admin' ? 'badge-role-admin' : 'badge-role-kasir' }}">
                                    {{ Auth::user()->label_peran }}
                                </span>
                            </div>
                            <span class="fw-bold small ms-1">{{ Auth::user()->username }}</span>
                            <i class="bi bi-chevron-down small text-muted ms-1"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 12px; min-width: 200px;">
                            <li><h6 class="dropdown-header small text-muted">Akun: {{ Auth::user()->nama }}</h6></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 rounded-2 py-2">
                                        <i class="bi bi-box-arrow-right"></i>
                                        <span>Keluar (Logout)</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </header>

        {{-- AREA ISI HALAMAN (FLUID ULTRA-WIDE DENGAN PADDING PROPORSIONAL) --}}
        <main class="p-3 p-md-4 p-xl-5 flex-grow-1 w-100">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 12px; background-color: #ecfdf5; color: #065f46;">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 12px; background-color: #fef2f2; color: #991b1b;">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        {{-- FOOTER --}}
        <footer class="bg-white border-top py-3 px-4 text-center text-muted small" style="font-size: 0.8rem;">
            &copy; {{ date('Y') }} <strong>SITOKO</strong> &middot; Sistem Informasi Tokoku dengan performa Offline-First & AI Analytics.
        </footer>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle Sidebar Mobile
        const btnToggleSidebar = document.getElementById('btnToggleSidebar');
        const sidebarWrapper = document.getElementById('sidebar-wrapper');
        const sidebarBackdrop = document.getElementById('sidebar-backdrop');

        if (btnToggleSidebar && sidebarWrapper && sidebarBackdrop) {
            btnToggleSidebar.addEventListener('click', function () {
                sidebarWrapper.classList.toggle('show');
                sidebarBackdrop.classList.toggle('d-none');
            });

            sidebarBackdrop.addEventListener('click', function () {
                sidebarWrapper.classList.remove('show');
                sidebarBackdrop.classList.add('d-none');
            });
        }

        // LIVE DIGITAL CLOCK & CALENDAR
        const liveClockText = document.getElementById('liveClockText');
        function updateClock() {
            if (!liveClockText) return;
            const now = new Date();
            const opsiTanggal = { weekday: 'short', day: 'numeric', month: 'short' };
            const tanggalStr = now.toLocaleDateString('id-ID', opsiTanggal);
            const jamStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            liveClockText.textContent = `${tanggalStr} • ${jamStr} WIB`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Monitoring Status Jaringan Luring / Daring
        const networkBadge = document.getElementById('networkStatusBadge');
        const networkDot = document.getElementById('networkStatusDot');
        const networkText = document.getElementById('networkStatusText');

        function updateNetworkStatus() {
            if (navigator.onLine) {
                networkBadge.className = 'network-status-badge network-status-online';
                networkDot.className = 'status-dot status-dot-online';
                networkText.textContent = 'Online';
                window.dispatchEvent(new CustomEvent('sitoko:network-online'));
            } else {
                networkBadge.className = 'network-status-badge network-status-offline';
                networkDot.className = 'status-dot status-dot-offline';
                networkText.textContent = 'Mode Offline';
                window.dispatchEvent(new CustomEvent('sitoko:network-offline'));
            }
        }

        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);
        updateNetworkStatus();
    });
</script>

@stack('scripts')
</body>
</html>

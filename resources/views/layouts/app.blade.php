<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Laundry Manager</title>
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <i class="ph-fill ph-drop"></i>
            LaundryMgr
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="ph ph-squares-four"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('transaksi.index') }}"
                    class="{{ request()->routeIs('transaksi.*') ? 'active' : '' }}">
                    <i class="ph ph-receipt"></i> Transaksi
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pelanggan.index') }}"
                    class="{{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
                    <i class="ph ph-users"></i> Pelanggan
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pegawai.index') }}" class="{{ request()->routeIs('pegawai.*') ? 'active' : '' }}">
                    <i class="ph ph-user-list"></i> Pegawai
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('absensi.index') }}" class="{{ request()->routeIs('absensi.*') ? 'active' : '' }}">
                    <i class="ph ph-calendar-check"></i> Absensi
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <i class="ph ph-chart-line-up"></i> Laporan
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pengeluaran.index') }}"
                    class="{{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}">
                    <i class="ph ph-money"></i> Pengeluaran
                </a>
            </li>
        </ul>
        <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.05);">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#">
                        <i class="ph ph-user"></i> Profil
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" style="color: var(--danger);" onmouseover="this.style.backgroundColor='rgba(231, 76, 60, 0.1)'" onmouseout="this.style.backgroundColor='transparent'">
                        <i class="ph ph-sign-out"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-title">@yield('page_title', 'Dashboard')</div>
            <div class="topbar-actions">
                @yield('topbar_actions')

                <button class="notification-btn">
                    <i class="ph ph-bell"></i>
                    <span class="notification-badge"></span>
                </button>
                <div class="user-profile">
                    <div class="avatar">V</div>
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">Veri Septiriyanto</div>
                        <div style="font-size: 12px; color: var(--text-secondary)">Owner</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <main class="content">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    <script>
        // Simpan pilihan cabang agar tidak reset saat pindah halaman
        const branchSelector = document.getElementById('globalBranchSelector');
        if (branchSelector) {
            const savedBranch = localStorage.getItem('selectedBranch');
            if (savedBranch) {
                branchSelector.value = savedBranch;
            }

            branchSelector.addEventListener('change', function () {
                localStorage.setItem('selectedBranch', this.value);
                // Opsional: Muat ulang halaman untuk mensimulasikan pembaruan data
                window.location.reload();
            });
        }
    </script>
</body>

</html>
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
                <a href="{{ route('inventaris.index') }}"
                    class="{{ request()->routeIs('inventaris.*') ? 'active' : '' }}">
                    <i class="ph ph-package"></i> Inventaris
                </a>
            </li>

        </ul>
        <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.05);">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="{{ route('aduan.index') }}" class="{{ request()->routeIs('aduan.*') ? 'active' : '' }}">
                        <i class="ph ph-chat-circle-dots"></i> Pusat Bantuan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('profil.index') }}" class="{{ request()->routeIs('profil.*') ? 'active' : '' }}">
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
                <!-- Global Branch Selector -->
                <form id="branchForm" action="{{ route('set-branch') }}" method="POST" style="margin: 0;">
                    @csrf
                    <div style="display: flex; align-items: center; gap: 8px; background-color: rgba(255,255,255,0.05); padding: 4px 12px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="ph ph-storefront" style="color: var(--accent-green); font-size: 18px;"></i>
                        <select name="branch_id" id="globalBranchSelector" onchange="document.getElementById('branchForm').submit();" style="background: transparent; border: none; color: white; outline: none; font-weight: 500; font-size: 14px; cursor: pointer; appearance: none; padding-right: 16px;">
                            <option style="color: black" value="global" {{ session('activeBranch', 'global') === 'global' ? 'selected' : '' }}>Semua Cabang / Toko</option>
                            @if(isset($branches))
                                @foreach($branches as $id => $name)
                                    <option style="color: black" value="{{ $id }}" {{ session('activeBranch') === $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <i class="ph ph-caret-down" style="font-size: 12px; color: var(--text-secondary); margin-left: -12px; pointer-events: none;"></i>
                    </div>
                </form>

                @yield('topbar_actions')

                <div class="notification-wrapper" style="position: relative;">
                    <button class="notification-btn" id="notifBtn" onclick="toggleNotifPanel()">
                        <i class="ph ph-bell"></i>
                        @if(isset($notificationCount) && $notificationCount > 0)
                            <span class="notification-badge" style="display: flex;">{{ $notificationCount }}</span>
                        @else
                            <span class="notification-badge" style="display: none;"></span>
                        @endif
                    </button>

                    <!-- Notification Panel -->
                    <div id="notifPanel" style="display: none; position: absolute; top: 48px; right: 0; width: 360px; max-height: 400px; overflow-y: auto; background: var(--card-bg, #1a1f2e); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.4); z-index: 1000; padding: 0;">
                        <div style="padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); font-weight: 600; font-size: 15px; display: flex; justify-content: space-between; align-items: center;">
                            <span><i class="ph ph-bell-ringing"></i> Notifikasi</span>
                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: var(--accent-blue); font-size: 12px;">{{ $notificationCount ?? 0 }}</span>
                        </div>
                        @if(isset($notifications) && count($notifications) > 0)
                            @foreach($notifications as $notif)
                            <div style="padding: 12px 20px; border-bottom: 1px solid rgba(255,255,255,0.03); display: flex; gap: 12px; align-items: flex-start; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                                @php
                                    $iconColor = '#3B82F6';
                                    $iconBg = 'rgba(59, 130, 246, 0.15)';
                                    if ($notif['type'] === 'warning') { $iconColor = '#F59E0B'; $iconBg = 'rgba(245, 158, 11, 0.15)'; }
                                    elseif ($notif['type'] === 'danger') { $iconColor = '#EF4444'; $iconBg = 'rgba(239, 68, 68, 0.15)'; }
                                @endphp
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ $iconBg }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="ph {{ $notif['icon'] }}" style="color: {{ $iconColor }}; font-size: 18px;"></i>
                                </div>
                                <div>
                                    <div style="font-size: 13px; line-height: 1.4;">{{ $notif['message'] }}</div>
                                    @if(!empty($notif['time']))
                                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">{{ \Carbon\Carbon::parse($notif['time'])->diffForHumans() }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div style="padding: 32px; text-align: center; color: var(--text-secondary);">
                                <i class="ph ph-check-circle" style="font-size: 32px; display: block; margin-bottom: 8px; color: var(--accent-green);"></i>
                                Tidak ada notifikasi baru
                            </div>
                        @endif
                    </div>
                </div>

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
    function toggleNotifPanel() {
        const panel = document.getElementById('notifPanel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }
    // Close on outside click
    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector('.notification-wrapper');
        const panel = document.getElementById('notifPanel');
        if (wrapper && panel && !wrapper.contains(e.target)) {
            panel.style.display = 'none';
        }
    });
    </script>
</body>

</html>
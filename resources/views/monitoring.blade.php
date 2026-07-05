@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Hari Ini')

@section('topbar_actions')
<!-- Branch Selector -->
<div style="display: flex; align-items: center; gap: 8px; background-color: rgba(255,255,255,0.05); padding: 4px 12px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.1);">
    <i class="ph ph-storefront" style="color: var(--accent-green); font-size: 18px;"></i>
    <select id="globalBranchSelector" style="background: transparent; border: none; color: white; outline: none; font-weight: 500; font-size: 14px; cursor: pointer; appearance: none; padding-right: 16px;">
        <option style="color: black" value="all">Semua Cabang (A-J)</option>
        <option style="color: black" value="A">Cabang A (Pusat)</option>
        <option style="color: black" value="B">Cabang B (Melati)</option>
        <option style="color: black" value="C">Cabang C (Kenanga)</option>
    </select>
    <i class="ph ph-caret-down" style="font-size: 12px; color: var(--text-secondary); margin-left: -12px; pointer-events: none;"></i>
</div>
@endsection

@section('content')
<!-- Summary Cards -->
<div class="summary-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Pendapatan Hari Ini</div>
                <div class="stat-value">Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}</div>
            </div>
            <div class="stat-icon"><i class="ph ph-money"></i></div>
        </div>
        <div style="color: var(--accent-green); font-size: 14px; font-weight: 500;">
            <i class="ph ph-trend-up"></i> +15% dari kemarin
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Pendapatan Bulan Ini</div>
                <div class="stat-value">Rp {{ number_format($totalPendapatanBulanIni, 0, ',', '.') }}</div>
            </div>
            <div class="stat-icon"><i class="ph ph-wallet"></i></div>
        </div>
        <div style="color: var(--accent-green); font-size: 14px; font-weight: 500;">
            <i class="ph ph-trend-up"></i> +5% dari bulan lalu
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Total Cucian (Bulan Ini)</div>
                <div class="stat-value">{{ $totalCucian }}</div>
            </div>
            <div class="stat-icon"><i class="ph ph-t-shirt"></i></div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Chart Section -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Grafik Transaksi (7 Hari Terakhir)</h2>
            <select class="form-control" style="padding: 6px 12px; width: auto;" id="chartFilter" onchange="updateChartData()">
                <option style="color: black" value="harian">Harian</option>
                <option style="color: black" value="mingguan">Mingguan</option>
                <option style="color: black" value="bulanan">Bulanan</option>
            </select>
        </div>
        <div class="chart-scroll-container">
            <div style="height: 300px; min-width: 1200px;">
                <canvas id="transactionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Transactions List -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Transaksi Terbaru</h2>
            <a href="{{ route('transaksi.index') }}" style="color: var(--accent-green); text-decoration: none; font-size: 14px;">Lihat Semua</a>
        </div>
        <table style="font-size: 14px;">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if(!$connected)
                <tr>
                    <td colspan="2">
                        <div style="color: var(--accent-red); font-weight: 500;">
                            <i class="ph ph-warning"></i> Gagal terhubung ke Firebase: {{ $error }}
                        </div>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                            Pastikan file service-account.json sudah ada di storage/app/firebase_credentials.json dan project ID sudah diisi di .env
                        </div>
                    </td>
                </tr>
                @endif

                @forelse($recentTransactions as $tx)
                <tr>
                    <td>
                        <div style="font-weight: 600">{{ $tx['customerName'] ?? 'Pelanggan' }}</div>
                        <div style="color: var(--text-secondary); font-size: 12px;">Rp {{ number_format($tx['total'] ?? 0, 0, ',', '.') }}</div>
                    </td>
                    <td>
                        @php
                            $status = $tx['status'] ?? 'Pesanan Diterima';
                            $badgeClass = 'masuk';
                            if (str_contains($status, 'Selesai') || str_contains($status, 'Diambil')) $badgeClass = 'selesai';
                            elseif (str_contains($status, 'Dicuci') || str_contains($status, 'Diproses')) $badgeClass = 'proses';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                    </td>
                </tr>
                @empty
                @if($connected)
                <tr>
                    <td colspan="2" style="text-align: center; color: var(--text-secondary);">Belum ada transaksi</td>
                </tr>
                @endif
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@stack('scripts')
@push('scripts')
<script>
    // Konfigurasi Chart.js untuk Tema Gelap
    Chart.defaults.color = '#A3C6BC';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)';

    const ctx = document.getElementById('transactionChart').getContext('2d');
    
    // Data Dummy untuk filter (menggunakan Tanggal)
    const chartData = {
        harian: {
            // Label berupa Tanggal (15 Hari agar bisa di-scroll horizontal)
            labels: ['01 Jul', '02 Jul', '03 Jul', '04 Jul', '05 Jul', '06 Jul', '07 Jul', '08 Jul', '09 Jul', '10 Jul', '11 Jul', '12 Jul', '13 Jul', '14 Jul', '15 Jul'],
            datasets: [
                { 
                    label: 'Pendapatan (Rp)', 
                    data: [150000, 200000, 180000, 320000, 250000, 400000, 350000, 120000, 210000, 300000, 450000, 280000, 390000, 410000, 500000], 
                    backgroundColor: '#1DA076',
                    borderRadius: 4
                }
            ]
        },
        mingguan: {
            labels: ['Minggu 1 (Jul)', 'Minggu 2 (Jul)', 'Minggu 3 (Jul)', 'Minggu 4 (Jul)', 'Minggu 1 (Ags)', 'Minggu 2 (Ags)', 'Minggu 3 (Ags)', 'Minggu 4 (Ags)'],
            datasets: [
                { 
                    label: 'Pendapatan (Rp)', 
                    data: [1200000, 1500000, 1350000, 1800000, 1400000, 1600000, 1900000, 2100000], 
                    backgroundColor: '#1DA076',
                    borderRadius: 4
                }
            ]
        },
        bulanan: {
            labels: ['Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026', 'Mei 2026', 'Jun 2026', 'Jul 2026', 'Ags 2026', 'Sep 2026', 'Okt 2026', 'Nov 2026', 'Des 2026'],
            datasets: [
                { 
                    label: 'Pendapatan (Rp)', 
                    data: [4500000, 4200000, 5100000, 4800000, 5500000, 6000000, 7200000, 6800000, 7500000, 8100000, 7900000, 9000000], 
                    backgroundColor: '#1DA076',
                    borderRadius: 4
                }
            ]
        }
    };

    let myChart = new Chart(ctx, {
        type: 'bar',
        data: chartData.harian,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value/1000) + 'k';
                        }
                    }
                }
            }
        }
    });

    // Fungsi untuk memperbarui data saat filter diubah
    function updateChartData() {
        const filter = document.getElementById('chartFilter').value;
        myChart.data = chartData[filter];
        myChart.update();
    }
</script>
@endpush

<?php
    $firebaseProjectId = env('FIREBASE_PROJECT_ID', 'cucianlaundri');
?>
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Hari Ini')



@section('content')
<!-- Connection Status -->
<div id="firebaseConnectionAlert" style="display: none; background: rgba(231, 76, 60, 0.1); color: var(--danger); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
    <i class="ph ph-warning-circle"></i> Gagal terhubung ke Firebase Realtime. Pastikan API Key diisi dengan benar.
</div>

<!-- Status Order Hari Ini -->
<div class="summary-grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 24px;">
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Total Order Hari Ini</div>
                <div class="stat-value" style="color: var(--accent-blue);">{{ $orderStatusCounts['total'] }}</div>
            </div>
            <div class="stat-icon" style="background-color: rgba(59, 130, 246, 0.2); color: var(--accent-blue);"><i class="ph ph-clipboard-text"></i></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Sedang Diproses</div>
                <div class="stat-value" style="color: #E67E22;">{{ $orderStatusCounts['diproses'] }}</div>
            </div>
            <div class="stat-icon" style="background-color: rgba(230, 126, 34, 0.2); color: #E67E22;"><i class="ph ph-spinner"></i></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Selesai (Belum Diambil)</div>
                <div class="stat-value" style="color: var(--accent-green);">{{ $orderStatusCounts['selesai'] }}</div>
            </div>
            <div class="stat-icon"><i class="ph ph-check-circle"></i></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Sudah Diambil</div>
                <div class="stat-value" style="color: #8B5CF6;">{{ $orderStatusCounts['diambil'] }}</div>
            </div>
            <div class="stat-icon" style="background-color: rgba(139, 92, 246, 0.2); color: #8B5CF6;"><i class="ph ph-handshake"></i></div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Chart Section -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Grafik Transaksi</h2>
            <select class="form-control" style="padding: 6px 12px; width: auto;" id="chartFilter">
                <option style="color: black" value="harian">Harian (Bulan Ini)</option>
                <option style="color: black" value="bulanan">Bulanan (12 Bulan)</option>
            </select>
        </div>
        <div class="chart-scroll-container">
            <div style="height: 300px; min-width: 1000px;">
                <canvas id="transactionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Services Chart -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Layanan Terlaris (Top 5)</h2>
        </div>
        <div style="height: 300px; display: flex; justify-content: center; align-items: center;">
            <canvas id="servicesChart"></canvas>
        </div>
    </div>
</div>

<!-- Ranking Cabang -->
@if(count($branchRanking) > 1)
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h2 class="card-title"><i class="ph ph-trophy" style="color: #D4AF37;"></i> Ranking Performa Cabang (Bulan Ini)</h2>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 60px;">#</th>
                <th>Nama Cabang / Toko</th>
                <th>Jumlah Transaksi</th>
                <th>Total Pendapatan</th>
                <th>Kontribusi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAllIncome = array_sum(array_column($branchRanking, 'income'));
            @endphp
            @foreach($branchRanking as $index => $branch)
            <tr>
                <td>
                    @if($index === 0)
                        <span style="font-size: 20px;">🥇</span>
                    @elseif($index === 1)
                        <span style="font-size: 20px;">🥈</span>
                    @elseif($index === 2)
                        <span style="font-size: 20px;">🥉</span>
                    @else
                        <span style="font-weight: 600; color: var(--text-secondary);">{{ $index + 1 }}</span>
                    @endif
                </td>
                <td style="font-weight: 600;">{{ $branch['name'] }}</td>
                <td>{{ $branch['orders'] }} transaksi</td>
                <td style="font-weight: 600; color: var(--accent-green);">Rp {{ number_format($branch['income'], 0, ',', '.') }}</td>
                <td>
                    @php
                        $percent = $totalAllIncome > 0 ? round(($branch['income'] / $totalAllIncome) * 100, 1) : 0;
                    @endphp
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="flex: 1; background: rgba(255,255,255,0.05); border-radius: 10px; height: 8px; overflow: hidden;">
                            <div style="width: {{ $percent }}%; background: linear-gradient(90deg, #D4AF37, #14B8A6); height: 100%; border-radius: 10px; transition: width 0.5s ease;"></div>
                        </div>
                        <span style="font-size: 13px; font-weight: 600; min-width: 42px;">{{ $percent }}%</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection

@stack('scripts')
@push('scripts')
<!-- Konfigurasi Global dari PHP -->
<script>
    window.APP_CONFIG = {
        todayStr: "{{ $todayStr }}",
        thisMonthStr: "{{ $thisMonthStr }}",
        firstDayOfMonth: "{{ $firstDayOfMonth }}",
        twelveMonthsAgo: "{{ $twelveMonthsAgo }}",
        firebaseProjectId: "{{ $firebaseProjectId }}"
    };
</script>

<!-- Firebase JS SDK (Modular) -->
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
    import { getFirestore, doc, onSnapshot, collection, query, orderBy, limit, where, getDocs } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

    // TODO: Masukkan Firebase Web API Key Anda di sini
    const firebaseConfig = {
        apiKey: "MASUKKAN_API_KEY_ANDA_DI_SINI", 
        authDomain: `${window.APP_CONFIG.firebaseProjectId}.firebaseapp.com`,
        projectId: window.APP_CONFIG.firebaseProjectId,
    };

    let db;
    try {
        const app = initializeApp(firebaseConfig);
        db = getFirestore(app);
    } catch (error) {
        console.error("Firebase init error", error);
        document.getElementById('firebaseConnectionAlert').style.display = 'block';
    }

    // Chart.js Setup
    Chart.defaults.color = '#A3C6BC';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)';
    
    let transactionChart = new Chart(document.getElementById('transactionChart').getContext('2d'), {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return (value/1000) + 'k'; }, color: '#FFFFFF' } }, x: { ticks: { color: '#FFFFFF' } } },
            plugins: { legend: { labels: { color: '#FFFFFF' } } }
        }
    });

    let servicesChart = new Chart(document.getElementById('servicesChart').getContext('2d'), {
        type: 'doughnut',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { color: '#FFFFFF', padding: 20 } } }
        }
    });

    // Variabel state untuk menyimpan data chart
    let globalChartData = {
        harian: { labels: [], datasets: [{ label: 'Pendapatan', data: [], backgroundColor: '#D4AF37', borderRadius: 6 }] },
        bulanan: { labels: [], datasets: [{ label: 'Pendapatan', data: [], backgroundColor: '#14B8A6', borderRadius: 6 }] }
    };

    // Fungsi utilitas untuk format rupiah
    const formatRp = (angka) => 'Rp ' + new Intl.NumberFormat('id-ID').format(angka || 0);

    // Filter Chart
    document.getElementById('chartFilter').addEventListener('change', function(e) {
        const filter = e.target.value;
        transactionChart.data = globalChartData[filter];
        transactionChart.update();
    });

    let activeBranch = document.getElementById('globalBranchSelector').value;
    
    // Simpan unsubscribe function agar listener lama bisa dibunuh saat ganti cabang
    let unsubDaily = null;
    let unsubMonthly = null;
    let unsubOrders = null;

    function listenRealtimeData() {
        if (!db) return;

        // Bunuh listener lama jika ada (agar tidak double/bertabrakan)
        if (unsubDaily) unsubDaily();
        if (unsubMonthly) unsubMonthly();
        if (unsubOrders) unsubOrders();

        // 1. Listen Daily Summary Hari Ini
        const dailyDocRef = doc(db, 'dashboard_summary_daily', `${activeBranch}_${window.APP_CONFIG.todayStr}`);
        unsubDaily = onSnapshot(dailyDocRef, (docSnap) => {
            // Data harian tidak ditampilkan lagi di summary card
        });

        // 2. Listen Monthly Summary Bulan Ini
        const monthlyDocRef = doc(db, 'dashboard_summary_monthly', `${activeBranch}_${window.APP_CONFIG.thisMonthStr}`);
        unsubMonthly = onSnapshot(monthlyDocRef, (docSnap) => {
            if (docSnap.exists()) {
                const data = docSnap.data();
                if (document.getElementById('valTotalPiutang')) {
                    document.getElementById('valTotalPiutang').innerText = formatRp(data.finance?.totalPiutang);
                }

                // Update Services Chart
                const services = data.servicesCount || {};
                const sortedServices = Object.entries(services).sort((a,b) => b[1] - a[1]).slice(0,5);
                
                servicesChart.data = {
                    labels: sortedServices.length > 0 ? sortedServices.map(s => s[0]) : ['Belum ada data'],
                    datasets: [{
                        data: sortedServices.length > 0 ? sortedServices.map(s => s[1]) : [1],
                        backgroundColor: ['#D4AF37', '#14B8A6', '#3B82F6', '#8B5CF6', '#F43F5E'],
                        borderWidth: 0
                    }]
                };
                servicesChart.update();
            } else {
                if (document.getElementById('valTotalPiutang')) {
                    document.getElementById('valTotalPiutang').innerText = "Rp 0";
                }
                servicesChart.data = { labels: ['Belum ada data'], datasets: [{ data: [1], backgroundColor: ['#ccc'] }] };
                servicesChart.update();
            }
        });

        // 3. Ambil data Harian dan Bulanan historis untuk Chart (Sekali ambil, tidak perlu realtime berlebihan)
        loadChartDataHistoris();


    }

    async function loadChartDataHistoris() {
        if (!db) return;
        
        try {
            // Load Harian (Bulan Ini)
            const qDaily = query(collection(db, 'dashboard_summary_daily'), 
                where('branchId', '==', activeBranch), 
                where('date', '>=', window.APP_CONFIG.firstDayOfMonth));
            
            const snapDaily = await getDocs(qDaily);
            let dailyMap = {};
            snapDaily.forEach(doc => { dailyMap[doc.data().date] = doc.data().finance?.totalIncome || 0; });

            // Bentuk Array Harian berurutan untuk seluruh hari di bulan ini (1-30/31)
            let tempLabelsHarian = [];
            let tempHarianData = [];
            const today = new Date();
            const year = today.getFullYear();
            const month = today.getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            for (let i = 1; i <= daysInMonth; i++) {
                const dayStr = String(i).padStart(2, '0');
                const monthStr = String(month + 1).padStart(2, '0');
                const strDate = `${year}-${monthStr}-${dayStr}`;
                
                tempLabelsHarian.push(i.toString()); // Menampilkan 1, 2, ..., 31
                tempHarianData.push(dailyMap[strDate] || 0);
            }
            globalChartData.harian.labels = tempLabelsHarian;
            globalChartData.harian.datasets[0].data = tempHarianData;

            // Load Bulanan (12 Bulan)
            const qMonthly = query(collection(db, 'dashboard_summary_monthly'), 
                where('branchId', '==', activeBranch), 
                where('month', '>=', window.APP_CONFIG.twelveMonthsAgo));
                
            const snapMonthly = await getDocs(qMonthly);
            let monthlyMap = {};
            snapMonthly.forEach(doc => { monthlyMap[doc.data().month] = doc.data().finance?.totalIncome || 0; });

            let tempLabelsBulanan = [];
            let tempBulananData = [];
            for (let i = 11; i >= 0; i--) {
                const m = new Date(today.getFullYear(), today.getMonth() - i, 1);
                const strMonth = m.toISOString().slice(0,7); // YYYY-MM
                tempLabelsBulanan.push(m.toLocaleDateString('id-ID', {month:'short', year:'numeric'}));
                tempBulananData.push(monthlyMap[strMonth] || 0);
            }
            globalChartData.bulanan.labels = tempLabelsBulanan;
            globalChartData.bulanan.datasets[0].data = tempBulananData;

            // Set default view ke Harian
            if (document.getElementById('chartFilter').value === 'harian') {
                transactionChart.data = globalChartData.harian;
                transactionChart.update();
            }

        } catch (error) {
            console.error("Gagal load history chart", error);
        }
    }

    // Jalankan realtime listener
    listenRealtimeData();

    // Event listener untuk ganti cabang
    document.getElementById('globalBranchSelector').addEventListener('change', function(e) {
        activeBranch = e.target.value;
        listenRealtimeData();
    });

</script>
@endpush

@extends('layouts.app')

@section('title', 'Laporan & Keuangan')
@section('page_title', 'Laporan Keuangan & Pengeluaran')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Filter Laporan (Pendapatan & Pengeluaran)</h2>
    </div>
    
    <div class="filter-bar" style="background-color: rgba(255,255,255,0.02); padding: 24px; border-radius: 12px; margin-bottom: 32px;">
        <form action="{{ route('laporan.index') }}" method="GET" style="display: flex; gap: 16px; flex-wrap: wrap; margin: 0; align-items: flex-end;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="color: var(--text-secondary); font-size: 14px;">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date', \Carbon\Carbon::today()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="color: var(--text-secondary); font-size: 14px;">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 42px;"><i class="ph ph-funnel"></i> Terapkan Filter</button>
                <button type="button" class="btn btn-outline" style="color: #2ECC71; border-color: #2ECC71; height: 42px;" onclick="window.print()"><i class="ph ph-printer"></i> Cetak</button>
                <button type="button" class="btn btn-outline" style="color: #3B82F6; border-color: #3B82F6; height: 42px;" onclick="exportToExcel()"><i class="ph ph-file-xls"></i> Export Excel</button>
            </div>
        </form>
    </div>

    @php
        $totalPengeluaran = 0;
        foreach($expenses as $exp) {
            $totalPengeluaran += ($exp['amount'] ?? 0);
        }
        $totalPendapatan = 0;
        foreach($incomes ?? [] as $inc) {
            $totalPendapatan += ($inc['totalIncome'] ?? 0);
        }
        $labaBersih = $totalPendapatan - $totalPengeluaran;
    @endphp

    <div class="summary-grid" style="margin-bottom: 32px;">
        <div class="stat-card" style="border-left: 4px solid var(--accent-green);">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Total Pendapatan</div>
                    <div class="stat-value" style="color: var(--accent-green);">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card" style="border-left: 4px solid var(--accent-red);">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Total Pengeluaran</div>
                    <div class="stat-value" style="color: var(--accent-red);">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid var(--accent-blue);">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Laba Bersih</div>
                    <div class="stat-value" style="color: var(--accent-blue);">Rp {{ number_format($labaBersih, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL PENDAPATAN -->
    <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i class="ph ph-trend-up" style="color: var(--accent-green);"></i> Rincian Pendapatan (Harian)</h3>
    <table class="table" style="margin-bottom: 40px;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jumlah Transaksi</th>
                <th>Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomes ?? [] as $inc)
            <tr>
                <td>{{ \Carbon\Carbon::parse($inc['date'])->format('d M Y') }}</td>
                <td>{{ $inc['totalOrders'] }} Transaksi</td>
                <td style="color: var(--accent-green); font-weight: 600;">Rp {{ number_format($inc['totalIncome'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: var(--text-secondary); padding: 1rem;">Belum ada data pendapatan pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TABEL PENGELUARAN -->
    <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i class="ph ph-trend-down" style="color: var(--accent-red);"></i> Rincian Pengeluaran</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Pengeluaran</th>
                <th>Kategori</th>
                <th>Jumlah (Rp)</th>
                <th>Pegawai</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($connected) && !$connected)
            <tr>
                <td colspan="5">
                    <div style="color: var(--accent-red); font-weight: 500;">
                        <i class="ph ph-warning"></i> Gagal terhubung ke Firebase: {{ $error }}
                    </div>
                </td>
            </tr>
            @endif

            @forelse($expenses as $exp)
            <tr>
                <td>{{ isset($exp['createdAt']) ? \Carbon\Carbon::parse($exp['createdAt'])->format('d M Y') : '-' }}</td>
                <td>{{ $exp['title'] ?? '-' }}</td>
                <td>
                    @php
                        $category = $exp['category'] ?? 'Lainnya';
                        $badgeStyle = 'background: rgba(149, 165, 166, 0.2); color: #95A5A6';
                        
                        $catLower = strtolower($category);
                        if (str_contains($catLower, 'bahan baku') || str_contains($catLower, 'sabun') || str_contains($catLower, 'pewangi')) {
                            $badgeStyle = 'background: rgba(230, 126, 34, 0.2); color: #E67E22';
                        } elseif (str_contains($catLower, 'operasional') || str_contains($catLower, 'listrik') || str_contains($catLower, 'air')) {
                            $badgeStyle = 'background: rgba(231, 76, 60, 0.2); color: #E74C3C';
                        }
                    @endphp
                    <span class="badge" style="{{ $badgeStyle }}">{{ $category }}</span>
                </td>
                <td style="color: var(--accent-red); font-weight: 600;">Rp {{ number_format($exp['amount'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ $exp['pegawai'] ?? '-' }}</td>
            </tr>
            @empty
            @if(isset($connected) && $connected)
            <tr>
                <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 1rem;">Belum ada data pengeluaran pada periode ini</td>
            </tr>
            @endif
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
function exportToExcel() {
    let csv = 'LAPORAN KEUANGAN LAUNDRY\n';
    csv += 'Periode: ' + document.querySelector('input[name=start_date]').value + ' s/d ' + document.querySelector('input[name=end_date]').value + '\n\n';

    // Pendapatan
    csv += 'RINCIAN PENDAPATAN\n';
    csv += 'Tanggal,Jumlah Transaksi,Total Pendapatan\n';
    const incomeRows = document.querySelectorAll('table:nth-of-type(1) tbody tr');
    incomeRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 3) {
            csv += '"' + cells[0].textContent.trim() + '","' + cells[1].textContent.trim() + '","' + cells[2].textContent.trim() + '"\n';
        }
    });

    csv += '\nRINCIAN PENGELUARAN\n';
    csv += 'Tanggal,Nama Pengeluaran,Kategori,Jumlah,Pegawai\n';
    const expenseRows = document.querySelectorAll('table:nth-of-type(2) tbody tr');
    expenseRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 5) {
            csv += '"' + cells[0].textContent.trim() + '","' + cells[1].textContent.trim() + '","' + cells[2].textContent.trim() + '","' + cells[3].textContent.trim() + '","' + cells[4].textContent.trim() + '"\n';
        }
    });

    // Download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    const startDate = document.querySelector('input[name=start_date]').value;
    const endDate = document.querySelector('input[name=end_date]').value;
    link.download = 'Laporan_Keuangan_' + startDate + '_' + endDate + '.csv';
    link.click();
}
</script>

<style>
@media print {
    .sidebar, .topbar, .filter-bar, .summary-grid, .notification-wrapper {
        display: none !important;
    }
    .main-wrapper {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
        background: white !important;
        color: black !important;
    }
    table { color: black !important; }
    th, td { border-color: #ddd !important; }
    h3 { color: black !important; }
    .stat-card { background: #f9f9f9 !important; color: black !important; }
    .stat-label, .stat-value { color: black !important; }
}
</style>
@endpush

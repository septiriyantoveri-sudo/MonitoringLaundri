@extends('layouts.app')

@section('title', 'Data Pelanggan')
@section('page_title', 'Data Pelanggan')

@section('content')
<div class="summary-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
    @php
        $totalCustomers = count($customers ?? []);
        $totalPiutang = 0;
        $totalRevenue = 0;
        foreach ($customers ?? [] as $c) {
            $totalPiutang += ($c['totalPiutang'] ?? 0);
            $totalRevenue += ($c['totalSpent'] ?? 0);
        }
    @endphp
    <div class="stat-card" style="border-left: 4px solid var(--accent-blue);">
        <div class="stat-header">
            <div>
                <div class="stat-label">Total Pelanggan</div>
                <div class="stat-value" style="color: var(--accent-blue);">{{ $totalCustomers }}</div>
            </div>
            <div class="stat-icon" style="background-color: rgba(59, 130, 246, 0.2); color: var(--accent-blue);"><i class="ph ph-users"></i></div>
        </div>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--accent-green);">
        <div class="stat-header">
            <div>
                <div class="stat-label">Total Pendapatan dari Pelanggan</div>
                <div class="stat-value" style="color: var(--accent-green); font-size: 20px;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="stat-icon"><i class="ph ph-money"></i></div>
        </div>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--warning);">
        <div class="stat-header">
            <div>
                <div class="stat-label">Total Piutang Pelanggan</div>
                <div class="stat-value" style="color: var(--warning); font-size: 20px;">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
            </div>
            <div class="stat-icon" style="background-color: rgba(241, 196, 15, 0.2); color: var(--warning);"><i class="ph ph-warning-circle"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 16px;">
        <h2 class="card-title">Daftar Pelanggan</h2>
        <div class="filter-bar" style="margin-bottom: 0;">
            <input type="text" id="searchCustomer" class="form-control" placeholder="Cari Nama / No HP..." onkeyup="filterCustomers()">
        </div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Pelanggan</th>
                <th>No HP / WhatsApp</th>
                <th>Alamat</th>
                <th>Total Order</th>
                <th>Total Belanja</th>
                <th>Piutang</th>
                <th>Terakhir Order</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($connected) && !$connected)
            <tr>
                <td colspan="8">
                    <div style="color: var(--accent-red); font-weight: 500;">
                        <i class="ph ph-warning"></i> Gagal terhubung ke Firebase: {{ $error }}
                    </div>
                </td>
            </tr>
            @endif

            @forelse($customers ?? [] as $index => $c)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <div style="font-weight: 600;">{{ $c['name'] ?? '-' }}</div>
                </td>
                <td>
                    @if(!empty($c['phone']))
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $c['phone']) }}" target="_blank" style="color: var(--accent-green); text-decoration: none;">
                            <i class="ph ph-whatsapp-logo"></i> {{ $c['phone'] }}
                        </a>
                    @else
                        <span style="color: var(--text-secondary);">-</span>
                    @endif
                </td>
                <td>{{ $c['address'] ?: '-' }}</td>
                <td>
                    @php
                        $orderCount = $c['totalOrders'] ?? 0;
                        $loyaltyColor = 'rgba(149, 165, 166, 0.2)';
                        $loyaltyText = '#95A5A6';
                        $loyaltyLabel = '';
                        if ($orderCount >= 20) {
                            $loyaltyColor = 'rgba(212, 175, 55, 0.2)';
                            $loyaltyText = '#D4AF37';
                            $loyaltyLabel = '👑 VIP';
                        } elseif ($orderCount >= 10) {
                            $loyaltyColor = 'rgba(29, 160, 118, 0.2)';
                            $loyaltyText = 'var(--accent-green)';
                            $loyaltyLabel = '⭐ Setia';
                        } elseif ($orderCount >= 5) {
                            $loyaltyColor = 'rgba(59, 130, 246, 0.2)';
                            $loyaltyText = 'var(--accent-blue)';
                            $loyaltyLabel = '🔄 Reguler';
                        }
                    @endphp
                    <span class="badge" style="background: {{ $loyaltyColor }}; color: {{ $loyaltyText }}">{{ $orderCount }}x Order</span>
                    @if($loyaltyLabel)
                        <div style="font-size: 11px; margin-top: 4px; color: {{ $loyaltyText }};">{{ $loyaltyLabel }}</div>
                    @endif
                </td>
                <td style="font-weight: 600;">Rp {{ number_format($c['totalSpent'] ?? 0, 0, ',', '.') }}</td>
                <td>
                    @if(($c['totalPiutang'] ?? 0) > 0)
                        <span style="color: var(--danger); font-weight: 600;">Rp {{ number_format($c['totalPiutang'], 0, ',', '.') }}</span>
                    @else
                        <span style="color: var(--accent-green);">Lunas ✓</span>
                    @endif
                </td>
                <td>
                    @if(!empty($c['lastOrder']))
                        {{ \Carbon\Carbon::parse($c['lastOrder'])->format('d M Y') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            @if(isset($connected) && $connected)
            <tr>
                <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 2rem;">Belum ada data pelanggan</td>
            </tr>
            @endif
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
function filterCustomers() {
    let input = document.getElementById('searchCustomer').value.toLowerCase();
    let table = document.querySelector('.table');
    let rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        let name = row.cells[1]?.textContent?.toLowerCase() || '';
        let phone = row.cells[2]?.textContent?.toLowerCase() || '';
        if (name.includes(input) || phone.includes(input)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endpush

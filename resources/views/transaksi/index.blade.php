@extends('layouts.app')

@section('title', 'Daftar Transaksi')
@section('page_title', 'Daftar Transaksi')

@section('content')
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <h2 class="card-title" style="margin: 0;">Transaksi Tanggal {{ \Carbon\Carbon::parse(request('date', \Carbon\Carbon::today()->format('Y-m-d')))->format('d M Y') }}</h2>
        <div class="filter-bar" style="margin-bottom: 0; display: flex; gap: 1rem; align-items: center;">
            <form action="{{ route('transaksi.index') }}" method="GET" style="margin: 0; display: flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.05); padding: 4px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                <i class="ph ph-calendar-blank" style="color: var(--accent-blue);"></i>
                <input type="date" name="date" value="{{ request('date', \Carbon\Carbon::today()->format('Y-m-d')) }}" onchange="this.form.submit()" style="background: transparent; border: none; color: white; outline: none; font-family: inherit; font-size: 0.9rem; cursor: pointer;">
            </form>
            <input type="text" class="form-control" placeholder="Cari ID/Nama..." style="max-width: 200px;">
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID Nota</th>
                <th>Tgl Masuk</th>
                <th>Pelanggan</th>
                <th>Layanan</th>
                <th>Total</th>
                <th>Pegawai</th>
                <th>Status</th>
                <th>Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @if(!$connected)
            <tr>
                <td colspan="8">
                    <div style="color: var(--accent-red); font-weight: 500;">
                        <i class="ph ph-warning"></i> Gagal terhubung ke Firebase: {{ $error }}
                    </div>
                </td>
            </tr>
            @endif

            @forelse($transactions as $tx)
            <tr>
                <td>{{ $tx['id'] ?? '-' }}</td>
                <td>{{ isset($tx['createdAt']) ? \Carbon\Carbon::parse($tx['createdAt'])->format('d M Y') : '-' }}</td>
                <td>{{ $tx['customerName'] ?? 'Pelanggan' }}</td>
                <td>{{ $tx['service'] ?? '-' }}</td>
                <td>Rp {{ number_format($tx['total'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ $tx['pegawai'] ?? '-' }}</td>
                <td>
                    @php
                        $status = $tx['status'] ?? 'Pesanan Diterima';
                        $badgeClass = 'masuk';
                        if (str_contains($status, 'Selesai') || str_contains($status, 'Diambil')) $badgeClass = 'selesai';
                        elseif (str_contains($status, 'Dicuci') || str_contains($status, 'Diproses')) $badgeClass = 'proses';
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                </td>
                <td>
                    @php
                        $isPaid = $tx['isPaid'] ?? false;
                        $paymentMethod = $tx['paymentMethod'] ?? '-';
                        $paymentColor = $isPaid ? 'var(--success)' : 'var(--danger)';
                        $paymentText = $isPaid ? 'Lunas' : 'Belum Bayar';
                    @endphp
                    <div style="font-weight: 600; color: {{ $paymentColor }}">{{ $paymentText }}</div>
                    <div style="font-size: 12px; color: var(--text-secondary);">{{ $paymentMethod }}</div>
                </td>
            </tr>
            @empty
            @if($connected)
            <tr>
                <td colspan="8" style="text-align: center; color: var(--text-secondary);">Belum ada transaksi</td>
            </tr>
            @endif
            @endforelse
        </tbody>
    </table>
</div>
@endsection

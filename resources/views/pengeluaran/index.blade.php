@extends('layouts.app')

@section('title', 'Data Pengeluaran')
@section('page_title', 'Pengeluaran Cabang')

@section('content')
<div class="dashboard-grid" style="grid-template-columns: 1fr;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Daftar Pengeluaran Laundry</h2>
            <div class="filter-bar" style="margin-bottom: 0;">
                <input type="text" class="form-control" placeholder="Cari Nama Pengeluaran...">
            </div>
        </div>
        
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
                    <td style="font-weight: 600;">Rp {{ number_format($exp['amount'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $exp['pegawai'] ?? '-' }}</td>
                </tr>
                @empty
                @if(isset($connected) && $connected)
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-secondary);">Belum ada data pengeluaran</td>
                </tr>
                @endif
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

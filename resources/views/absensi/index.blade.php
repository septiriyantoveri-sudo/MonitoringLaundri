@extends('layouts.app')

@section('title', 'Data Absensi')
@section('page_title', 'Absensi Pegawai')

@section('content')
<div class="dashboard-grid" style="grid-template-columns: 1fr;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Laporan Kehadiran Pegawai</h2>
            <div class="filter-bar" style="margin-bottom: 0;">
                <input type="text" class="form-control" placeholder="Cari Nama Pegawai...">
            </div>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Pegawai</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($connected) && !$connected)
                <tr>
                    <td colspan="6">
                        <div style="color: var(--accent-red); font-weight: 500;">
                            <i class="ph ph-warning"></i> Gagal terhubung ke Firebase: {{ $error }}
                        </div>
                    </td>
                </tr>
                @endif

                @forelse($absensi as $ab)
                <tr>
                    <td>{{ isset($ab['date']) ? \Carbon\Carbon::parse($ab['date'])->format('d M Y') : (isset($ab['createdAt']) ? \Carbon\Carbon::parse($ab['createdAt'])->format('d M Y') : '-') }}</td>
                    <td><div style="font-weight: 600">{{ $ab['pegawai'] ?? $ab['name'] ?? $ab['nama'] ?? '-' }}</div></td>
                    <td>{{ $ab['timeIn'] ?? $ab['jam_masuk'] ?? '-' }}</td>
                    <td>{{ $ab['timeOut'] ?? $ab['jam_keluar'] ?? '-' }}</td>
                    <td>
                        @php
                            $status = $ab['status'] ?? 'Hadir';
                            $badgeStyle = 'background: rgba(29, 160, 118, 0.2); color: var(--accent-green)'; // default green
                            
                            $statusLower = strtolower($status);
                            if (str_contains($statusLower, 'izin') || str_contains($statusLower, 'sakit')) {
                                $badgeStyle = 'background: rgba(230, 126, 34, 0.2); color: #E67E22'; // orange
                            } elseif (str_contains($statusLower, 'alpa') || str_contains($statusLower, 'absen')) {
                                $badgeStyle = 'background: rgba(231, 76, 60, 0.2); color: #E74C3C'; // red
                            }
                        @endphp
                        <span class="badge" style="{{ $badgeStyle }}">{{ ucfirst($status) }}</span>
                    </td>
                    <td>{{ $ab['keterangan'] ?? $ab['note'] ?? '-' }}</td>
                </tr>
                @empty
                @if(isset($connected) && $connected)
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-secondary);">Belum ada data absensi</td>
                </tr>
                @endif
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

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
                    <th>Lokasi</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($connected) && !$connected)
                <tr>
                    <td colspan="7">
                        <div style="color: var(--accent-red); font-weight: 500;">
                            <i class="ph ph-warning"></i> Gagal terhubung ke Firebase: {{ $error }}
                        </div>
                    </td>
                </tr>
                @endif

                @forelse($absensi as $ab)
                <tr>
                    <td>
                        @php
                            $tanggal = $ab['date'] ?? $ab['createdAt'] ?? $ab['timestamp'] ?? null;
                        @endphp
                        {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M Y') : '-' }}
                    </td>
                    <td><div style="font-weight: 600">{{ $ab['pegawai'] ?? $ab['name'] ?? $ab['nama'] ?? '-' }}</div></td>
                    <td>
                        @if(isset($ab['jam_masuk']))
                            {{ $ab['jam_masuk'] }}
                        @elseif(isset($ab['timeIn']))
                            {{ $ab['timeIn'] }}
                        @elseif(isset($ab['timestamp']) && strtolower($ab['status'] ?? '') == 'masuk')
                            {{ \Carbon\Carbon::parse($ab['timestamp'])->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if(isset($ab['jam_keluar']))
                            {{ $ab['jam_keluar'] }}
                        @elseif(isset($ab['timeOut']))
                            {{ $ab['timeOut'] }}
                        @elseif(isset($ab['timestamp']) && strtolower($ab['status'] ?? '') == 'keluar')
                            {{ \Carbon\Carbon::parse($ab['timestamp'])->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
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
                    <td>
                        @if(isset($ab['keterangan']))
                            {{ $ab['keterangan'] }}
                        @elseif(isset($ab['note']))
                            {{ $ab['note'] }}
                        @elseif(isset($ab['latitude']) && isset($ab['longitude']))
                            <div style="font-size: 0.9em; line-height: 1.4;">
                                Jarak: {{ isset($ab['distance']) ? round($ab['distance']) . 'm' : '-' }} 
                                ({{ isset($ab['isInRange']) && $ab['isInRange'] ? 'Dalam Radius' : 'Luar Radius' }})
                                <br>
                                <a href="https://maps.google.com/?q={{ $ab['latitude'] }},{{ $ab['longitude'] }}" target="_blank" style="color: var(--primary-color); text-decoration: underline;">Buka Maps</a>
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if(isset($ab['photoPath']) && $ab['photoPath'] != '')
                            @if(Str::startsWith($ab['photoPath'], 'http'))
                                <a href="{{ $ab['photoPath'] }}" target="_blank" title="Lihat Foto Full">
                                    <img src="{{ $ab['photoPath'] }}" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(0,0,0,0.1);">
                                </a>
                            @else
                                <span style="font-size: 0.85em; color: var(--text-secondary); cursor: help;" title="{{ $ab['photoPath'] }}">Tersimpan di HP</span>
                            @endif
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                @if(isset($connected) && $connected)
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-secondary);">Belum ada data absensi</td>
                </tr>
                @endif
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

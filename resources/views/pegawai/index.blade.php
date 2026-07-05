@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('page_title', 'Data Pegawai')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Manajemen Pegawai Laundry</h2>
        <div class="filter-bar" style="margin-bottom: 0;">
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Nama Pegawai</th>
                <th>Posisi</th>
                <th>Alamat</th>
                <th>Status</th>
                <th>No. Telepon</th>
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

            @forelse($pegawai as $p)
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        @php
                            $nameParts = explode(' ', $p['name'] ?? 'U');
                            $initials = strtoupper(substr($nameParts[0], 0, 1));
                            if (count($nameParts) > 1) $initials .= strtoupper(substr($nameParts[1], 0, 1));
                        @endphp
                        <div class="avatar" style="width: 32px; height: 32px; font-size: 12px;">{{ $initials }}</div>
                        <div>
                            <div style="font-weight: 600">{{ $p['name'] ?? '-' }}</div>
                            <div style="font-size: 12px; color: var(--text-secondary);">{{ $p['email'] ?? '' }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $p['position'] ?? '-' }}</td>
                <td>{{ $p['address'] ?? '-' }}</td>
                <td><span class="badge masuk">Aktif</span></td>
                <td>{{ $p['phoneNumber'] ?? '-' }}</td>
            </tr>
            @empty
            @if(isset($connected) && $connected)
            <tr>
                <td colspan="5" style="text-align: center; color: var(--text-secondary);">Belum ada data pegawai</td>
            </tr>
            @endif
            @endforelse
        </tbody>
    </table>
</div>
@endsection

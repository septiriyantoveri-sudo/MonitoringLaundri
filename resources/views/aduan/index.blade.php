@extends('layouts.app')

@section('title', 'Pusat Bantuan & Aduan')
@section('page_title', 'Pusat Bantuan & Aduan')

@section('content')
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <h2 class="card-title" style="margin: 0;">Daftar Pesan Aduan dari Aplikasi</h2>
        <div class="filter-bar" style="margin-bottom: 0;">
            <input type="text" class="form-control" placeholder="Cari nama pegawai/pesan..." style="max-width: 250px;">
        </div>
    </div>
    
    <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(29, 160, 118, 0.1); border-left: 4px solid var(--accent-green); border-radius: 4px;">
        <i class="ph ph-info"></i> <strong>Informasi Developer:</strong> Untuk mengirim data ke halaman ini, aplikasi mobile Anda (Kotlin/Flutter) harus melakukan <i>insert document</i> ke collection Firestore bernama <code>aduan</code>.
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Tanggal</th>
                <th width="15%">Pengirim (Pegawai)</th>
                <th width="15%">Cabang/Toko</th>
                <th width="40%">Isi Pesan Aduan</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @if(!$connected)
            <tr>
                <td colspan="5">
                    <div style="color: var(--accent-red); font-weight: 500;">
                        <i class="ph ph-warning"></i> Gagal terhubung ke Firebase: {{ $error }}
                    </div>
                </td>
            </tr>
            @endif

            @forelse($aduanList as $aduan)
            <tr>
                <td>{{ isset($aduan['createdAt']) ? \Carbon\Carbon::parse($aduan['createdAt'])->format('d M Y H:i') : '-' }}</td>
                <td><div style="font-weight: 600">{{ $aduan['senderName'] ?? 'Anonim' }}</div></td>
                <td>{{ $aduan['branchName'] ?? '-' }}</td>
                <td>
                    <div style="color: var(--text-primary); line-height: 1.5;">
                        {{ $aduan['message'] ?? 'Tidak ada pesan.' }}
                    </div>
                </td>
                <td>
                    @php
                        $status = strtolower($aduan['status'] ?? 'pending');
                    @endphp
                    @if($status == 'selesai' || $status == 'resolved')
                        <span class="badge selesai">Diselesaikan</span>
                    @else
                        <span class="badge proses">Menunggu</span>
                    @endif
                </td>
            </tr>
            @empty
            @if($connected)
            <tr>
                <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 3rem 1rem;">
                    <i class="ph ph-check-circle" style="font-size: 3rem; color: var(--success); margin-bottom: 1rem; opacity: 0.5;"></i>
                    <br>
                    Belum ada pesan aduan. Semua sistem berjalan lancar!
                </td>
            </tr>
            @endif
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Laporan & Export')
@section('page_title', 'Laporan Keuangan')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Cetak Laporan Pendapatan</h2>
    </div>
    
    <div class="filter-bar" style="background-color: rgba(255,255,255,0.02); padding: 24px; border-radius: 12px; margin-bottom: 32px;">
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <label style="color: var(--text-secondary); font-size: 14px;">Tanggal Mulai</label>
            <input type="date" class="form-control" value="2026-07-01">
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <label style="color: var(--text-secondary); font-size: 14px;">Tanggal Akhir</label>
            <input type="date" class="form-control" value="2026-07-05">
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <label style="color: var(--text-secondary); font-size: 14px;">Cabang</label>
            <select class="form-control">
                <option style="color: black" value="all">Semua Cabang</option>
                <option style="color: black" value="A">Cabang A</option>
                <option style="color: black" value="B">Cabang B</option>
                <option style="color: black" value="C">Cabang C</option>
            </select>
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px; justify-content: flex-end;">
            <button class="btn btn-primary" style="height: 42px;"><i class="ph ph-funnel"></i> Filter</button>
        </div>
    </div>

    <div style="display: flex; gap: 16px; margin-bottom: 24px;">
        <button class="btn btn-outline" style="color: #E74C3C; border-color: #E74C3C;"><i class="ph ph-file-pdf"></i> Export ke PDF</button>
        <button class="btn btn-outline" style="color: #2ECC71; border-color: #2ECC71;"><i class="ph ph-file-xls"></i> Export ke Excel</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jumlah Transaksi</th>
                <th>Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>05 Jul 2026</td>
                <td>12 Transaksi</td>
                <td style="color: var(--accent-green); font-weight: 600;">Rp 1.250.000</td>
            </tr>
            <tr>
                <td>04 Jul 2026</td>
                <td>8 Transaksi</td>
                <td style="color: var(--accent-green); font-weight: 600;">Rp 950.000</td>
            </tr>
            <tr style="background-color: rgba(255,255,255,0.05); font-weight: 700;">
                <td colspan="2" style="text-align: right;">Total Pendapatan:</td>
                <td style="color: var(--accent-green); font-size: 18px;">Rp 2.200.000</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

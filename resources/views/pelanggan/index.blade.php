@extends('layouts.app')

@section('title', 'Data Pelanggan')
@section('page_title', 'Data Pelanggan')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Pelanggan Setia</h2>
        <div class="filter-bar" style="margin-bottom: 0;">
            <input type="text" class="form-control" placeholder="Cari Nama/No HP...">
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Nama Pelanggan</th>
                <th>No HP / WhatsApp</th>
                <th>Alamat</th>
                <th>Cabang</th>
                <th>Total Order</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><div style="font-weight: 600">Budi Santoso</div></td>
                <td>0812-3456-7890</td>
                <td>Jl. Melati No. 12</td>
                <td>Cabang A</td>
                <td><span class="badge" style="background: rgba(29, 160, 118, 0.2); color: var(--accent-green)">15x Order</span></td>
            </tr>
            <tr>
                <td><div style="font-weight: 600">Siti Aminah</div></td>
                <td>0856-7890-1234</td>
                <td>Jl. Kenanga No. 8</td>
                <td>Cabang C</td>
                <td><span class="badge" style="background: rgba(29, 160, 118, 0.2); color: var(--accent-green)">8x Order</span></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

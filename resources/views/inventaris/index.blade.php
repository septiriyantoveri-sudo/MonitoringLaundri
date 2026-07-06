@extends('layouts.app')

@section('title', 'Manajemen Inventaris')
@section('page_title', 'Inventaris & Stok')

@section('content')
<div class="dashboard-grid" style="grid-template-columns: 1fr;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">Daftar Stok Barang</h2>
        </div>
        
        @if(session('success'))
            <div style="background: rgba(46, 204, 113, 0.1); color: #2ecc71; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                {{ session('error') }}
            </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Sisa Stok</th>
                    <th>Satuan</th>
                    <th>Aksi</th>
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

                @forelse($items as $item)
                <tr>
                    <td style="font-weight: 500;">{{ $item['name'] ?? '-' }}</td>
                    <td>
                        <span class="badge" style="background: rgba(52, 152, 219, 0.2); color: #2980b9;">
                            {{ $item['category'] ?? '-' }}
                        </span>
                    </td>
                    <td>
                        @php
                            $stock = $item['stock'] ?? 0;
                            $stockColor = $stock <= 5 ? '#e74c3c' : 'var(--text-primary)';
                        @endphp
                        <span style="font-weight: 600; color: {{ $stockColor }};">
                            {{ $stock }}
                        </span>
                    </td>
                    <td>{{ $item['unit'] ?? '-' }}</td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-outline" style="padding: 4px 8px; font-size: 0.85rem; border-color: #f39c12; color: #f39c12;" 
                                onclick="openRestockModal('{{ $item['id'] }}', '{{ $item['name'] }}', '{{ $item['unit'] }}')">
                                <i class="ph ph-package"></i> Restock
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                @if(isset($connected) && $connected)
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-secondary);">Belum ada data barang</td>
                </tr>
                @endif
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<!-- Restock Modal -->
<div id="restockModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 2rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0;">Restock <span id="restockItemName" style="color: var(--accent-blue);"></span></h3>
            <button type="button" onclick="closeModals()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-secondary);">&times;</button>
        </div>
        <form id="restockForm" method="POST">
            @csrf
            <div style="background: rgba(243, 156, 18, 0.1); border-left: 4px solid #f39c12; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0 8px 8px 0;">
                <p style="margin: 0; font-size: 0.9rem; color: var(--text-secondary);">
                    Catatan: Mengisi formulir ini akan <strong>menambah stok</strong> inventaris sekaligus <strong>mencatat pengeluaran otomatis</strong> di menu Pengeluaran.
                </p>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-secondary);">
                    Jumlah Dibeli <span id="restockUnitLabel" style="opacity: 0.7;"></span>
                </label>
                <input type="number" step="0.01" name="amount" class="form-control" required min="0.1" placeholder="Misal: 5">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-secondary);">Total Biaya Pembelian (Rp)</label>
                <input type="number" name="total_cost" class="form-control" required min="0" placeholder="Misal: 150000">
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <button type="button" class="btn btn-outline" onclick="closeModals()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #f39c12; border-color: #f39c12; color: white;">
                    Proses Restock
                </button>
            </div>
        </form>
    </div>
</div>

<script>

    function openRestockModal(id, name, unit) {
        document.getElementById('restockForm').action = `/inventaris/${id}/restock`;
        document.getElementById('restockItemName').textContent = name;
        document.getElementById('restockUnitLabel').textContent = '(' + unit + ')';
        document.getElementById('restockModal').style.display = 'flex';
    }

    function closeModals() {
        document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
    }

    // Close modal if clicked outside card
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModals();
        }
    }
</script>
@endsection

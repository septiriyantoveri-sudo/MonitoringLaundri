<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class InventarisController extends Controller
{
    public function index()
    {
        try {
            $firestore = app('firebase.firestore')->database();
            $inventoryRef = $firestore->collection('inventory')->documents();
            
            $items = [];
            foreach ($inventoryRef as $doc) {
                if ($doc->exists()) {
                    $item = $doc->data();
                    $item['id'] = $doc->id();
                    $items[] = $item;
                }
            }

            $connected = true;
            $error = null;
        } catch (\Exception $e) {
            $items = [];
            $connected = false;
            $error = $e->getMessage();
        }

        return view('inventaris.index', compact('items', 'connected', 'error'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'unit' => 'required|string',
            'stock' => 'required|numeric'
        ]);

        try {
            $firestore = app('firebase.firestore')->database();
            $firestore->collection('inventory')->add([
                'name' => $request->name,
                'category' => $request->category,
                'unit' => $request->unit,
                'stock' => (float)$request->stock,
                'createdAt' => Carbon::now()->toIso8601String(),
                'updatedAt' => Carbon::now()->toIso8601String(),
            ]);
            return redirect()->route('inventaris.index')->with('success', 'Barang berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan barang: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'unit' => 'required|string',
        ]);

        try {
            $firestore = app('firebase.firestore')->database();
            $firestore->collection('inventory')->document($id)->set([
                'name' => $request->name,
                'category' => $request->category,
                'unit' => $request->unit,
                'updatedAt' => Carbon::now()->toIso8601String(),
            ], ['merge' => true]);
            
            return redirect()->route('inventaris.index')->with('success', 'Barang berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate barang: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $firestore = app('firebase.firestore')->database();
            $firestore->collection('inventory')->document($id)->delete();
            return redirect()->route('inventaris.index')->with('success', 'Barang berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    public function restock(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.1',
            'total_cost' => 'required|numeric|min:0'
        ]);

        try {
            $firestore = app('firebase.firestore')->database();
            $docRef = $firestore->collection('inventory')->document($id);
            $doc = $docRef->snapshot();
            
            if (!$doc->exists()) {
                return redirect()->back()->with('error', 'Barang tidak ditemukan');
            }

            $currentData = $doc->data();
            $newStock = ($currentData['stock'] ?? 0) + (float)$request->amount;

            // 1. Update stok di koleksi inventory
            $docRef->set([
                'stock' => $newStock,
                'lastRestockAt' => Carbon::now()->toIso8601String(),
                'updatedAt' => Carbon::now()->toIso8601String(),
            ], ['merge' => true]);

            // 2. Tambah catatan ke global expenses
            if ($request->total_cost > 0) {
                $firestore->collection('expenses')->add([
                    'title' => 'Restock: ' . ($currentData['name'] ?? 'Barang'),
                    'category' => 'Bahan Baku', // Otomatis masuk kategori bahan baku/inventaris
                    'amount' => (int)$request->total_cost,
                    'pegawai' => 'Admin (Sistem)',
                    'createdAt' => Carbon::now()->toIso8601String(),
                ]);
            }

            return redirect()->route('inventaris.index')->with('success', 'Restock berhasil dan pengeluaran telah dicatat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan restock: ' . $e->getMessage());
        }
    }
}

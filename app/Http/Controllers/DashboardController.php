<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Parameter tanggal dilempar ke view agar Javascript tahu 
        // ID dokumen mana yang harus dibaca dari Firestore
        
        $todayStr = Carbon::today()->format('Y-m-d');
        $thisMonthStr = Carbon::today()->format('Y-m');

        // Untuk chart harian (bulan ini)
        $firstDayOfMonth = Carbon::today()->startOfMonth()->format('Y-m-d');
        
        // Untuk chart bulanan (12 bulan terakhir)
        $twelveMonthsAgo = Carbon::today()->startOfMonth()->subMonths(11)->format('Y-m');

        // Ambil daftar user sebagai daftar toko/cabang
        $branches = [];
        $branchRanking = [];
        $orderStatusCounts = ['diproses' => 0, 'selesai' => 0, 'diambil' => 0, 'total' => 0];
        $notifications = [];

        try {
            $firestore = app('firebase.firestore')->database();
            $usersRef = $firestore->collection('users')->documents();
            $branchIds = [];
            foreach ($usersRef as $userDoc) {
                if ($userDoc->exists()) {
                    $userData = $userDoc->data();
                    $branches[$userDoc->id()] = $userData['name'] ?? 'Toko ' . substr($userDoc->id(), 0, 5);
                    $branchIds[] = $userDoc->id();
                }
            }

            // --- RANKING CABANG (bulan ini) ---
            foreach ($branchIds as $bId) {
                $monthlyDocId = $bId . '_' . $thisMonthStr;
                $monthlyDoc = $firestore->collection('dashboard_summary_monthly')->document($monthlyDocId)->snapshot();
                
                $income = 0;
                $orders = 0;
                if ($monthlyDoc->exists()) {
                    $data = $monthlyDoc->data();
                    $income = $data['finance']['totalIncome'] ?? 0;
                    $orders = $data['operations']['totalOrders'] ?? 0;
                }
                
                $branchRanking[] = [
                    'id' => $bId,
                    'name' => $branches[$bId] ?? $bId,
                    'income' => $income,
                    'orders' => $orders,
                ];
            }
            
            // Sort by income descending
            usort($branchRanking, function ($a, $b) {
                return $b['income'] - $a['income'];
            });

            // --- STATUS ORDER HARI INI ---
            $activeBranch = session('activeBranch', 'global');
            $ordersQuery = $firestore->collection('orders');
            if ($activeBranch !== 'global') {
                $ordersQuery = $ordersQuery->where('branchId', '==', $activeBranch);
            }
            $allOrders = $ordersQuery->documents();

            foreach ($allOrders as $orderDoc) {
                if (!$orderDoc->exists()) continue;
                $order = $orderDoc->data();
                $createdAt = $order['createdAt'] ?? '';
                
                // Hanya hitung order hari ini
                if (strpos($createdAt, $todayStr) !== 0) continue;

                $orderStatusCounts['total'] += 1;
                $status = strtolower($order['status'] ?? '');
                
                if (str_contains($status, 'diambil') || str_contains($status, 'picked') || str_contains($status, 'completed')) {
                    $orderStatusCounts['diambil'] += 1;
                } elseif (str_contains($status, 'selesai') || str_contains($status, 'done') || str_contains($status, 'ready')) {
                    $orderStatusCounts['selesai'] += 1;
                } else {
                    $orderStatusCounts['diproses'] += 1;
                }
            }

            // --- NOTIFIKASI ---
            // 1. Stok menipis (dari inventaris)
            try {
                $inventoryRef = $firestore->collection('inventory')->documents();
                foreach ($inventoryRef as $invDoc) {
                    if (!$invDoc->exists()) continue;
                    $inv = $invDoc->data();
                    $stock = $inv['stock'] ?? 0;
                    if ($stock <= 5 && $stock > 0) {
                        $notifications[] = [
                            'type' => 'warning',
                            'icon' => 'ph-package',
                            'message' => 'Stok "' . ($inv['name'] ?? 'Barang') . '" tinggal ' . $stock . ' ' . ($inv['unit'] ?? 'unit'),
                            'time' => $inv['updatedAt'] ?? '',
                        ];
                    } elseif ($stock <= 0) {
                        $notifications[] = [
                            'type' => 'danger',
                            'icon' => 'ph-warning',
                            'message' => 'Stok "' . ($inv['name'] ?? 'Barang') . '" HABIS!',
                            'time' => $inv['updatedAt'] ?? '',
                        ];
                    }
                }
            } catch (\Exception $e) {}

            // 2. Aduan baru (belum direspon)
            try {
                $aduanRef = $firestore->collection('aduan')->documents();
                $aduanCount = 0;
                foreach ($aduanRef as $aduanDoc) {
                    if (!$aduanDoc->exists()) continue;
                    $aduan = $aduanDoc->data();
                    $status = strtolower($aduan['status'] ?? 'baru');
                    if ($status === 'baru' || $status === 'pending') {
                        $aduanCount++;
                    }
                }
                if ($aduanCount > 0) {
                    $notifications[] = [
                        'type' => 'info',
                        'icon' => 'ph-chat-circle-dots',
                        'message' => $aduanCount . ' aduan pelanggan belum direspon',
                        'time' => '',
                    ];
                }
            } catch (\Exception $e) {}

            // 3. Piutang tinggi
            try {
                $monthlyGlobalDoc = $firestore->collection('dashboard_summary_monthly')
                    ->document('global_' . $thisMonthStr)->snapshot();
                if ($monthlyGlobalDoc->exists()) {
                    $piutang = $monthlyGlobalDoc->data()['finance']['totalPiutang'] ?? 0;
                    if ($piutang > 500000) {
                        $notifications[] = [
                            'type' => 'warning',
                            'icon' => 'ph-warning-circle',
                            'message' => 'Total piutang bulan ini: Rp ' . number_format($piutang, 0, ',', '.'),
                            'time' => '',
                        ];
                    }
                }
            } catch (\Exception $e) {}

        } catch (\Exception $e) {
            // Abaikan jika error koneksi
        }

        $notificationCount = count($notifications);

        // Render view dengan data
        return view('monitoring', compact(
            'todayStr', 
            'thisMonthStr', 
            'firstDayOfMonth', 
            'twelveMonthsAgo',
            'branches',
            'branchRanking',
            'orderStatusCounts',
            'notifications',
            'notificationCount'
        ));
    }
}


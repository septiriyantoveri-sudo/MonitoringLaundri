<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $firestore = app('firebase.firestore')->database();
            
            // Ambil semua user (pegawai/cabang)
            $usersRef = $firestore->collection('users')->documents();
            
            $recentTransactions = [];
            $allOrders = [];
            $totalPendapatanHariIni = 0;
            $totalPendapatanBulanIni = 0;
            $totalCucian = 0;
            $totalPegawai = 0;

            foreach ($usersRef as $userDoc) {
                if (!$userDoc->exists()) continue;
                $totalPegawai++;
                $userData = $userDoc->data();
                $userId = $userDoc->id();

                // Ambil semua orders dari subcollection tiap user
                try {
                    $ordersRef = $firestore->collection('users')
                        ->document($userId)
                        ->collection('orders')
                        ->documents();

                    foreach ($ordersRef as $orderDoc) {
                        if (!$orderDoc->exists()) continue;
                        
                        $order = $orderDoc->data();
                        $order['id'] = $orderDoc->id();
                        $order['pegawai'] = $userData['name'] ?? '-';
                        
                        $totalCucian++;
                        $price = $order['total'] ?? 0;
                        $totalPendapatanBulanIni += $price;

                        // Cek apakah transaksi hari ini
                        $createdAt = $order['createdAt'] ?? null;
                        if ($createdAt) {
                            try {
                                $date = Carbon::parse($createdAt);
                                if ($date->isToday()) {
                                    $totalPendapatanHariIni += $price;
                                }
                            } catch (\Exception $e) {
                                // skip
                            }
                        }

                        $allOrders[] = $order;
                    }
                } catch (\Exception $e) {
                    // Subcollection orders mungkin belum ada untuk user ini
                }
            }

            // Sort by createdAt descending dan ambil 5 terbaru
            usort($allOrders, function ($a, $b) {
                $dateA = $a['createdAt'] ?? '';
                $dateB = $b['createdAt'] ?? '';
                return strcmp($dateB, $dateA);
            });

            $recentTransactions = array_slice($allOrders, 0, 5);

            $connected = true;
            $error = null;

        } catch (\Exception $e) {
            $recentTransactions = [];
            $totalPendapatanHariIni = 0;
            $totalPendapatanBulanIni = 0;
            $totalCucian = 0;
            $totalPegawai = 0;
            $connected = false;
            $error = $e->getMessage();
        }

        return view('monitoring', compact(
            'recentTransactions', 
            'totalPendapatanHariIni', 
            'totalPendapatanBulanIni', 
            'totalCucian',
            'totalPegawai',
            'connected',
            'error'
        ));
    }
}

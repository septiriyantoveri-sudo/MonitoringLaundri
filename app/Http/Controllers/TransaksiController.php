<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index()
    {
        try {
            $firestore = app('firebase.firestore')->database();
            
            $transactions = [];
            
            // Baca dari global collection 'orders' dengan limit untuk performa
            // Sebaiknya ditambah orderBy createdAt descending jika index sudah ada
            $ordersRef = $firestore->collection('orders')
                // ->orderBy('createdAt', 'DESC') // Uncomment jika sudah buat index di Firestore
                ->limit(50)
                ->documents();

            foreach ($ordersRef as $doc) {
                if (!$doc->exists()) continue;
                
                $order = $doc->data();
                $order['id'] = $doc->id();
                $order['pegawai'] = $order['cashierName'] ?? $order['pegawai'] ?? '-';
                $transactions[] = $order;
            }

            // Fallback (jika data belum dimigrasi ke global orders, baca 10 terakhir per user dari sub-collection)
            // Ini bisa dihapus jika aplikasi kasir sudah 100% menggunakan global orders
            if (empty($transactions)) {
                $usersRef = $firestore->collection('users')->documents();
                foreach ($usersRef as $userDoc) {
                    if (!$userDoc->exists()) continue;
                    $userData = $userDoc->data();
                    $userId = $userDoc->id();

                    try {
                        $oldOrdersRef = $firestore->collection('users')
                            ->document($userId)
                            ->collection('orders')
                            ->limit(5) // Limit agar tidak terlalu lambat
                            ->documents();

                        foreach ($oldOrdersRef as $oldOrderDoc) {
                            if (!$oldOrderDoc->exists()) continue;
                            
                            $order = $oldOrderDoc->data();
                            $order['id'] = $oldOrderDoc->id();
                            $order['pegawai'] = $userData['name'] ?? '-';
                            $transactions[] = $order;
                        }
                    } catch (\Exception $e) {
                        // skip
                    }
                }
            }

            // Sort by createdAt descending
            usort($transactions, function ($a, $b) {
                $dateA = $a['createdAt'] ?? '';
                $dateB = $b['createdAt'] ?? '';
                return strcmp($dateB, $dateA);
            });

            $connected = true;
            $error = null;
        } catch (\Exception $e) {
            $transactions = [];
            $connected = false;
            $error = $e->getMessage();
        }

        return view('transaksi.index', compact('transactions', 'connected', 'error'));
    }
}

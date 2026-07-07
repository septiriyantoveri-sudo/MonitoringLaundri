<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $firestore = app('firebase.firestore')->database();
            
            $transactions = [];
            
            $activeBranch = session('activeBranch', 'global');
            $selectedDate = $request->input('date', \Carbon\Carbon::today()->format('Y-m-d'));
            
            // Baca dari global collection 'orders'
            $ordersQuery = $firestore->collection('orders');
            if ($activeBranch !== 'global') {
                $ordersQuery = $ordersQuery->where('branchId', '==', $activeBranch);
            }
            // Ambil semua data (tanpa limit) lalu filter di PHP berdasarkan tanggal
            $ordersRef = $ordersQuery->documents();

            foreach ($ordersRef as $doc) {
                if (!$doc->exists()) continue;
                
                $order = $doc->data();
                $createdAt = $order['createdAt'] ?? '';
                
                // Filter berdasarkan tanggal (hari) yang dipilih
                if (strpos($createdAt, $selectedDate) !== 0) {
                    continue;
                }

                $order['id'] = $doc->id();
                $order['pegawai'] = $order['cashierName'] ?? $order['pegawai'] ?? '-';
                $transactions[] = $order;
            }

            // Fallback (jika data belum dimigrasi ke global orders)
            if (empty($transactions)) {
                if ($activeBranch !== 'global') {
                    $userDoc = $firestore->collection('users')->document($activeBranch)->snapshot();
                    $usersToProcess = $userDoc->exists() ? [$userDoc] : [];
                } else {
                    $usersToProcess = $firestore->collection('users')->documents();
                }

                foreach ($usersToProcess as $userDoc) {
                    if (!$userDoc->exists()) continue;
                    $userData = $userDoc->data();
                    $userId = $userDoc->id();

                    try {
                        $oldOrdersRef = $firestore->collection('users')
                            ->document($userId)
                            ->collection('orders')
                            ->documents();

                        foreach ($oldOrdersRef as $oldOrderDoc) {
                            if (!$oldOrderDoc->exists()) continue;
                            
                            $order = $oldOrderDoc->data();
                            $createdAt = $order['createdAt'] ?? '';
                            
                            // Filter berdasarkan tanggal
                            if (strpos($createdAt, $selectedDate) !== 0) {
                                continue;
                            }

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

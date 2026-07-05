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
            $usersRef = $firestore->collection('users')->documents();

            foreach ($usersRef as $userDoc) {
                if (!$userDoc->exists()) continue;
                $userData = $userDoc->data();
                $userId = $userDoc->id();

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
                        $transactions[] = $order;
                    }
                } catch (\Exception $e) {
                    // skip
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

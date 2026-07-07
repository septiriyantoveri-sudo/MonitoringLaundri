<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        try {
            $firestore = app('firebase.firestore')->database();
            
            $activeBranch = session('activeBranch', 'global');
            $customerMap = []; // key = nama pelanggan (lowercase), value = data

            // Ambil dari global orders
            $ordersQuery = $firestore->collection('orders');
            if ($activeBranch !== 'global') {
                $ordersQuery = $ordersQuery->where('branchId', '==', $activeBranch);
            }
            $ordersRef = $ordersQuery->documents();

            foreach ($ordersRef as $doc) {
                if (!$doc->exists()) continue;
                $order = $doc->data();
                
                $name = $order['customerName'] ?? $order['nama'] ?? $order['namaPelanggan'] ?? null;
                if (!$name || trim($name) === '') continue;

                $key = strtolower(trim($name));
                $phone = $order['customerPhone'] ?? $order['noHp'] ?? $order['phone'] ?? '';
                $address = $order['customerAddress'] ?? $order['alamat'] ?? '';
                $total = $order['total'] ?? 0;
                $isPaid = $order['isPaid'] ?? false;
                $createdAt = $order['createdAt'] ?? '';
                $branchId = $order['branchId'] ?? '';

                if (!isset($customerMap[$key])) {
                    $customerMap[$key] = [
                        'name' => $name,
                        'phone' => $phone,
                        'address' => $address,
                        'totalOrders' => 0,
                        'totalSpent' => 0,
                        'totalPiutang' => 0,
                        'lastOrder' => $createdAt,
                        'branchId' => $branchId,
                    ];
                }

                $customerMap[$key]['totalOrders'] += 1;
                $customerMap[$key]['totalSpent'] += $total;
                if (!$isPaid) {
                    $customerMap[$key]['totalPiutang'] += $total;
                }

                // Update data terbaru
                if ($createdAt > ($customerMap[$key]['lastOrder'] ?? '')) {
                    $customerMap[$key]['lastOrder'] = $createdAt;
                    if ($phone) $customerMap[$key]['phone'] = $phone;
                    if ($address) $customerMap[$key]['address'] = $address;
                }
            }

            // Fallback: cek sub-collection orders per user
            if (empty($customerMap)) {
                if ($activeBranch !== 'global') {
                    $userDoc = $firestore->collection('users')->document($activeBranch)->snapshot();
                    $usersToProcess = $userDoc->exists() ? [$userDoc] : [];
                } else {
                    $usersToProcess = $firestore->collection('users')->documents();
                }

                foreach ($usersToProcess as $userDoc) {
                    if (!$userDoc->exists()) continue;
                    $userId = $userDoc->id();

                    try {
                        $oldOrdersRef = $firestore->collection('users')
                            ->document($userId)
                            ->collection('orders')
                            ->documents();

                        foreach ($oldOrdersRef as $oldDoc) {
                            if (!$oldDoc->exists()) continue;
                            $order = $oldDoc->data();
                            
                            $name = $order['customerName'] ?? $order['nama'] ?? $order['namaPelanggan'] ?? null;
                            if (!$name || trim($name) === '') continue;

                            $key = strtolower(trim($name));
                            $phone = $order['customerPhone'] ?? $order['noHp'] ?? $order['phone'] ?? '';
                            $address = $order['customerAddress'] ?? $order['alamat'] ?? '';
                            $total = $order['total'] ?? 0;
                            $isPaid = $order['isPaid'] ?? false;
                            $createdAt = $order['createdAt'] ?? '';

                            if (!isset($customerMap[$key])) {
                                $customerMap[$key] = [
                                    'name' => $name,
                                    'phone' => $phone,
                                    'address' => $address,
                                    'totalOrders' => 0,
                                    'totalSpent' => 0,
                                    'totalPiutang' => 0,
                                    'lastOrder' => $createdAt,
                                    'branchId' => $userId,
                                ];
                            }

                            $customerMap[$key]['totalOrders'] += 1;
                            $customerMap[$key]['totalSpent'] += $total;
                            if (!$isPaid) {
                                $customerMap[$key]['totalPiutang'] += $total;
                            }

                            if ($createdAt > ($customerMap[$key]['lastOrder'] ?? '')) {
                                $customerMap[$key]['lastOrder'] = $createdAt;
                                if ($phone) $customerMap[$key]['phone'] = $phone;
                                if ($address) $customerMap[$key]['address'] = $address;
                            }
                        }
                    } catch (\Exception $e) {
                        // skip
                    }
                }
            }

            // Sort by totalOrders descending (pelanggan paling sering order di atas)
            $customers = array_values($customerMap);
            usort($customers, function ($a, $b) {
                return $b['totalOrders'] - $a['totalOrders'];
            });

            $connected = true;
            $error = null;
        } catch (\Exception $e) {
            $customers = [];
            $connected = false;
            $error = $e->getMessage();
        }

        return view('pelanggan.index', compact('customers', 'connected', 'error'));
    }
}

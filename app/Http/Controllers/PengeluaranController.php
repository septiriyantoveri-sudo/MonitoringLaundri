<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        try {
            $firestore = app('firebase.firestore')->database();
            
            $expenses = [];
            $incomes = [];
            $activeBranch = session('activeBranch', 'global');
            $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
            
            // --- AMBIL DATA PENGELUARAN ---
            if ($activeBranch !== 'global') {
                $userDoc = $firestore->collection('users')->document($activeBranch)->snapshot();
                if ($userDoc->exists()) {
                    $usersToProcess = [$userDoc];
                } else {
                    $usersToProcess = [];
                }
            } else {
                $usersToProcess = $firestore->collection('users')->documents();
            }

            foreach ($usersToProcess as $userDoc) {
                if (!$userDoc->exists()) continue;
                $userData = $userDoc->data();
                $userId = $userDoc->id();

                try {
                    $expensesRef = $firestore->collection('users')
                        ->document($userId)
                        ->collection('expenses')
                        ->documents();

                    foreach ($expensesRef as $expDoc) {
                        if (!$expDoc->exists()) continue;
                        
                        $exp = $expDoc->data();
                        
                        // Filter Date
                        $createdAt = $exp['createdAt'] ?? '';
                        $dateOnly = substr($createdAt, 0, 10);
                        if ($dateOnly < $startDate || $dateOnly > $endDate) {
                            continue;
                        }

                        $exp['id'] = $expDoc->id();
                        $exp['pegawai'] = $userData['name'] ?? '-';
                        $expenses[] = $exp;
                    }
                } catch (\Exception $e) {
                    // skip
                }
            }

            // Baca juga dari koleksi global expenses
            try {
                $globalExpQuery = $firestore->collection('expenses');
                if ($activeBranch !== 'global') {
                    $globalExpQuery = $globalExpQuery->where('branchId', '==', $activeBranch);
                }
                $globalExpensesRef = $globalExpQuery->documents();
                
                foreach ($globalExpensesRef as $expDoc) {
                    if (!$expDoc->exists()) continue;
                    
                    $exp = $expDoc->data();
                    
                    // Filter Date
                    $createdAt = $exp['createdAt'] ?? '';
                    $dateOnly = substr($createdAt, 0, 10);
                    if ($dateOnly < $startDate || $dateOnly > $endDate) {
                        continue;
                    }

                    $exp['id'] = $expDoc->id();
                    $exp['pegawai'] = $exp['pegawai'] ?? 'Admin (Sistem)';
                    $expenses[] = $exp;
                }
            } catch (\Exception $e) {
                // skip
            }

            usort($expenses, function ($a, $b) {
                $dateA = $a['createdAt'] ?? '';
                $dateB = $b['createdAt'] ?? '';
                return strcmp($dateB, $dateA);
            });

            // --- AMBIL DATA PENDAPATAN (DARI SUMMARY DAILY) ---
            try {
                $dailySummaryQuery = $firestore->collection('dashboard_summary_daily');
                if ($activeBranch !== 'global') {
                    $dailySummaryQuery = $dailySummaryQuery->where('branchId', '==', $activeBranch);
                }
                $dailySummaryRef = $dailySummaryQuery->documents();
                
                $incomeMap = [];
                foreach ($dailySummaryRef as $doc) {
                    if (!$doc->exists()) continue;
                    $data = $doc->data();
                    
                    $dateKey = $data['date'] ?? '';
                    if ($dateKey < $startDate || $dateKey > $endDate) {
                        continue;
                    }
                    
                    if (!isset($incomeMap[$dateKey])) {
                        $incomeMap[$dateKey] = [
                            'date' => $dateKey,
                            'totalIncome' => 0,
                            'totalOrders' => 0
                        ];
                    }
                    
                    $incomeMap[$dateKey]['totalIncome'] += ($data['finance']['totalIncome'] ?? 0);
                    $incomeMap[$dateKey]['totalOrders'] += ($data['operations']['totalOrders'] ?? 0);
                }

                $incomes = array_values($incomeMap);
                usort($incomes, function ($a, $b) {
                    return strcmp($b['date'] ?? '', $a['date'] ?? '');
                });

            } catch (\Exception $e) {
                // skip
            }

            $connected = true;
            $error = null;
        } catch (\Exception $e) {
            $expenses = [];
            $incomes = [];
            $connected = false;
            $error = $e->getMessage();
        }

        return view('laporan.index', compact('expenses', 'incomes', 'connected', 'error'));
    }
}

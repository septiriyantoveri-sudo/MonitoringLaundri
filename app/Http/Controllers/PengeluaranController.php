<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PengeluaranController extends Controller
{
    public function index()
    {
        try {
            $firestore = app('firebase.firestore')->database();
            
            $expenses = [];
            $activeBranch = session('activeBranch', 'global');
            
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
                        $exp['id'] = $expDoc->id();
                        $exp['pegawai'] = $userData['name'] ?? '-';
                        $expenses[] = $exp;
                    }
                } catch (\Exception $e) {
                    // skip
                }
            }

            // Baca juga dari koleksi global expenses (misalnya untuk restock inventaris)
            try {
                $globalExpQuery = $firestore->collection('expenses');
                if ($activeBranch !== 'global') {
                    $globalExpQuery = $globalExpQuery->where('branchId', '==', $activeBranch);
                }
                $globalExpensesRef = $globalExpQuery->documents();
                
                foreach ($globalExpensesRef as $expDoc) {
                    if (!$expDoc->exists()) continue;
                    
                    $exp = $expDoc->data();
                    $exp['id'] = $expDoc->id();
                    $exp['pegawai'] = $exp['pegawai'] ?? 'Admin (Sistem)';
                    $expenses[] = $exp;
                }
            } catch (\Exception $e) {
                // skip
            }

            // Sort by createdAt descending
            usort($expenses, function ($a, $b) {
                $dateA = $a['createdAt'] ?? '';
                $dateB = $b['createdAt'] ?? '';
                return strcmp($dateB, $dateA);
            });

            $connected = true;
            $error = null;
        } catch (\Exception $e) {
            $expenses = [];
            $connected = false;
            $error = $e->getMessage();
        }

        return view('pengeluaran.index', compact('expenses', 'connected', 'error'));
    }
}

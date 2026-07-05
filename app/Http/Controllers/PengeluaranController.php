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
            $usersRef = $firestore->collection('users')->documents();

            foreach ($usersRef as $userDoc) {
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

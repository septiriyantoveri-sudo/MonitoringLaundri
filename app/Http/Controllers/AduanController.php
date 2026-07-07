<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AduanController extends Controller
{
    public function index()
    {
        try {
            $firestore = app('firebase.firestore')->database();
            
            $aduanList = [];
            
            // Baca filter cabang dari session
            $activeBranch = session('activeBranch', 'global');
            
            // Baca koleksi 'aduan' dari Firestore
            $aduanQuery = $firestore->collection('aduan');
            
            // Jika tidak global, filter berdasarkan branchId
            if ($activeBranch !== 'global') {
                $aduanQuery = $aduanQuery->where('branchId', '==', $activeBranch);
            }
            
            $aduanRef = $aduanQuery->documents();

            foreach ($aduanRef as $doc) {
                if (!$doc->exists()) continue;
                
                $data = $doc->data();
                $data['id'] = $doc->id();
                $aduanList[] = $data;
            }

            // Urutkan dari yang terbaru ke terlama berdasarkan 'createdAt'
            usort($aduanList, function ($a, $b) {
                $dateA = $a['createdAt'] ?? '';
                $dateB = $b['createdAt'] ?? '';
                return strcmp($dateB, $dateA); // descending
            });

            $connected = true;
            $error = null;
        } catch (\Exception $e) {
            $aduanList = [];
            $connected = false;
            $error = $e->getMessage();
        }

        return view('aduan.index', compact('aduanList', 'connected', 'error'));
    }
}

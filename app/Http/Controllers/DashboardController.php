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

        // Untuk chart harian (15 hari terakhir)
        $fifteenDaysAgo = Carbon::today()->subDays(14)->format('Y-m-d');
        
        // Untuk chart bulanan (12 bulan terakhir)
        $twelveMonthsAgo = Carbon::today()->startOfMonth()->subMonths(11)->format('Y-m');

        // Render view dengan data kosong (akan diisi oleh JS di klien)
        return view('monitoring', compact(
            'todayStr', 
            'thisMonthStr', 
            'fifteenDaysAgo', 
            'twelveMonthsAgo'
        ));
    }
}


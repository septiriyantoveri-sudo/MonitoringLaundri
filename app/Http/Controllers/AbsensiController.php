<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index()
    {
        try {
            $firestore = app('firebase.firestore')->database();
            
            $absensi = [];
            
            // Coba ambil dari koleksi 'attendance' (global)
            try {
                $attendanceRef = $firestore->collection('attendance')->documents();
                foreach ($attendanceRef as $doc) {
                    if (!$doc->exists()) continue;
                    
                    $data = $doc->data();
                    $data['id'] = $doc->id();
                    $absensi[] = $data;
                }
            } catch (\Exception $e) {
                // skip
            }

            // Jika kosong, mungkin disimpan di subcollection users/{userId}/attendance
            if (empty($absensi)) {
                $usersRef = $firestore->collection('users')->documents();
                foreach ($usersRef as $userDoc) {
                    if (!$userDoc->exists()) continue;
                    $userData = $userDoc->data();
                    $userId = $userDoc->id();

                    try {
                        $attRef = $firestore->collection('users')
                            ->document($userId)
                            ->collection('attendance')
                            ->documents();

                        foreach ($attRef as $attDoc) {
                            if (!$attDoc->exists()) continue;
                            
                            $data = $attDoc->data();
                            $data['id'] = $attDoc->id();
                            $data['pegawai'] = $userData['name'] ?? '-';
                            $absensi[] = $data;
                        }
                    } catch (\Exception $e) {
                        // skip
                    }
                }
            }

            // Sort by tanggal (createdAt atau date atau timestamp)
            usort($absensi, function ($a, $b) {
                $dateA = $a['createdAt'] ?? $a['date'] ?? $a['timestamp'] ?? '';
                $dateB = $b['createdAt'] ?? $b['date'] ?? $b['timestamp'] ?? '';
                return strcmp($dateB, $dateA);
            });

            $connected = true;
            $error = null;
        } catch (\Exception $e) {
            $absensi = [];
            $connected = false;
            $error = $e->getMessage();
        }

        return view('absensi.index', compact('absensi', 'connected', 'error'));
    }
}

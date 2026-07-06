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
            $todayHadir = 0;
            $todayIzin = 0;
            $todaySakit = 0;
            $todayAlpa = 0;
            
            // Ambil dari koleksi 'attendance' (global) dengan limit untuk mencegah out of memory
            // Sebaiknya ditambah orderBy date descending jika ada index
            $attendanceRef = $firestore->collection('attendance')
                // ->orderBy('date', 'DESC') // Uncomment jika index sudah dibuat di firebase
                ->limit(100)
                ->documents();

            foreach ($attendanceRef as $doc) {
                if (!$doc->exists()) continue;
                
                $data = $doc->data();
                $data['id'] = $doc->id();
                $absensi[] = $data;
            }

            // Fallback (jika data belum dimigrasi ke global attendance, baca 50 terakhir dari sub-collection)
            // Ini bisa dihapus jika aplikasi kasir sudah 100% menggunakan global attendance
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
                            ->limit(10) // Limit per user agar tidak berat
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

            // Calculate summaries for today
            foreach ($absensi as $ab) {
                $tanggal = $ab['date'] ?? $ab['createdAt'] ?? $ab['timestamp'] ?? null;
                if ($tanggal) {
                    try {
                        if (Carbon::parse($tanggal)->isToday()) {
                            $status = strtolower($ab['status'] ?? 'hadir');
                            if (str_contains($status, 'izin')) {
                                $todayIzin++;
                            } elseif (str_contains($status, 'sakit')) {
                                $todaySakit++;
                            } elseif (str_contains($status, 'alpa') || str_contains($status, 'absen')) {
                                $todayAlpa++;
                            } else {
                                $todayHadir++;
                            }
                        }
                    } catch (\Exception $e) {
                        // ignore parse error
                    }
                }
            }

            $connected = true;
            $error = null;
        } catch (\Exception $e) {
            $absensi = [];
            $todayHadir = 0;
            $todayIzin = 0;
            $todaySakit = 0;
            $todayAlpa = 0;
            $connected = false;
            $error = $e->getMessage();
        }

        return view('absensi.index', compact('absensi', 'todayHadir', 'todayIzin', 'todaySakit', 'todayAlpa', 'connected', 'error'));
    }
}

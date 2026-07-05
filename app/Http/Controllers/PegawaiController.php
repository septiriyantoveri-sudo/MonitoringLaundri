<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index()
    {
        try {
            $firestore = app('firebase.firestore')->database();
            
            $pegawai = [];
            try {
                $usersRef = $firestore->collection('users')->documents();
                
                foreach ($usersRef as $document) {
                    if ($document->exists()) {
                        $pegawai[] = array_merge(['id' => $document->id()], $document->data());
                    }
                }
            } catch (\Exception $e) {
                // Koleksi users mungkin belum ada
            }

            $connected = true;
            $error = null;
        } catch (\Exception $e) {
            $pegawai = [];
            $connected = false;
            $error = $e->getMessage();
        }

        return view('pegawai.index', compact('pegawai', 'connected', 'error'));
    }
}

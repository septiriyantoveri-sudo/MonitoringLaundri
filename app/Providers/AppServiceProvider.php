<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $branches = \Illuminate\Support\Facades\Cache::remember('global_branches', 300, function () {
                $branchesData = [];
                try {
                    $firestore = app('firebase.firestore')->database();
                    $usersRef = $firestore->collection('users')->documents();
                    foreach ($usersRef as $userDoc) {
                        if ($userDoc->exists()) {
                            $userData = $userDoc->data();
                            $branchesData[$userDoc->id()] = $userData['name'] ?? 'Toko ' . substr($userDoc->id(), 0, 5);
                        }
                    }
                } catch (\Exception $e) {
                    // skip
                }
                return $branchesData;
            });
            $view->with('branches', $branches);

            // Notifikasi global (cache 60 detik agar tidak berat)
            if (!$view->offsetExists('notifications')) {
                $notifData = \Illuminate\Support\Facades\Cache::remember('global_notifications', 60, function () {
                    $notifications = [];
                    try {
                        $firestore = app('firebase.firestore')->database();
                        
                        // Stok menipis
                        $inventoryRef = $firestore->collection('inventory')->documents();
                        foreach ($inventoryRef as $invDoc) {
                            if (!$invDoc->exists()) continue;
                            $inv = $invDoc->data();
                            $stock = $inv['stock'] ?? 0;
                            if ($stock <= 5 && $stock > 0) {
                                $notifications[] = [
                                    'type' => 'warning', 'icon' => 'ph-package',
                                    'message' => 'Stok "' . ($inv['name'] ?? 'Barang') . '" tinggal ' . $stock . ' ' . ($inv['unit'] ?? 'unit'),
                                    'time' => $inv['updatedAt'] ?? '',
                                ];
                            } elseif ($stock <= 0) {
                                $notifications[] = [
                                    'type' => 'danger', 'icon' => 'ph-warning',
                                    'message' => 'Stok "' . ($inv['name'] ?? 'Barang') . '" HABIS!',
                                    'time' => $inv['updatedAt'] ?? '',
                                ];
                            }
                        }

                        // Aduan baru
                        $aduanRef = $firestore->collection('aduan')->documents();
                        $aduanCount = 0;
                        foreach ($aduanRef as $aduanDoc) {
                            if (!$aduanDoc->exists()) continue;
                            $aduan = $aduanDoc->data();
                            $status = strtolower($aduan['status'] ?? 'baru');
                            if ($status === 'baru' || $status === 'pending') $aduanCount++;
                        }
                        if ($aduanCount > 0) {
                            $notifications[] = [
                                'type' => 'info', 'icon' => 'ph-chat-circle-dots',
                                'message' => $aduanCount . ' aduan pelanggan belum direspon', 'time' => '',
                            ];
                        }
                    } catch (\Exception $e) {}
                    return $notifications;
                });

                $view->with('notifications', $notifData);
                $view->with('notificationCount', count($notifData));
            }
        });
    }
}

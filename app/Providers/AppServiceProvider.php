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
        });
    }
}

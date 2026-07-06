<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class MigrateFirebaseData extends Command
{
    protected $signature = 'firebase:migrate';
    protected $description = 'Migrate old orders from sub-collections to global orders and generate daily/monthly summaries';

    public function handle()
    {
        $this->info('Starting Firebase Data Migration...');
        try {
            $firestore = app('firebase.firestore')->database();
            
            // 1. Ambil semua users
            $usersRef = $firestore->collection('users')->documents();
            
            $globalSummaryDaily = []; // Format: 'global_YYYY-MM-DD' => data
            $globalSummaryMonthly = []; // Format: 'global_YYYY-MM' => data

            $totalMigrated = 0;

            foreach ($usersRef as $userDoc) {
                if (!$userDoc->exists()) continue;
                $userData = $userDoc->data();
                $userId = $userDoc->id();
                $branchId = $userData['branchId'] ?? 'branch_default'; // Default jika tidak ada
                $cashierName = $userData['name'] ?? 'Unknown';

                $this->info("Processing user: {$userId} ({$cashierName})");

                try {
                    $ordersRef = $firestore->collection('users')
                        ->document($userId)
                        ->collection('orders')
                        ->documents();

                    foreach ($ordersRef as $orderDoc) {
                        if (!$orderDoc->exists()) continue;
                        
                        $orderData = $orderDoc->data();
                        $orderId = $orderDoc->id();
                        $price = $orderData['total'] ?? 0;
                        
                        // Hitung piutang
                        $statusPembayaran = strtolower($orderData['paymentStatus'] ?? $orderData['statusPembayaran'] ?? 'belum lunas');
                        $isPaid = $orderData['isPaid'] ?? false;
                        $piutang = 0;
                        if ($statusPembayaran == 'belum lunas' && !$isPaid) {
                            $piutang = $price;
                        }

                        // Waktu transaksi
                        $createdAt = $orderData['createdAt'] ?? null;
                        if (!$createdAt) continue; // Skip jika tidak ada tanggal

                        try {
                            $date = Carbon::parse($createdAt);
                            $dateKeyDaily = $date->format('Y-m-d');
                            $dateKeyMonthly = $date->format('Y-m');
                            
                            $summaryDailyId = "global_{$dateKeyDaily}";
                            $summaryMonthlyId = "global_{$dateKeyMonthly}";

                            // Update Daily
                            if (!isset($globalSummaryDaily[$summaryDailyId])) {
                                $globalSummaryDaily[$summaryDailyId] = [
                                    'date' => $dateKeyDaily,
                                    'branchId' => 'global',
                                    'finance' => ['totalIncome' => 0, 'totalPiutang' => 0],
                                    'operations' => ['totalOrders' => 0],
                                    'servicesCount' => [],
                                    'updatedAt' => Carbon::now()->toIso8601String()
                                ];
                            }

                            $globalSummaryDaily[$summaryDailyId]['finance']['totalIncome'] += $price;
                            $globalSummaryDaily[$summaryDailyId]['finance']['totalPiutang'] += $piutang;
                            $globalSummaryDaily[$summaryDailyId]['operations']['totalOrders'] += 1;

                            // Update Monthly
                            if (!isset($globalSummaryMonthly[$summaryMonthlyId])) {
                                $globalSummaryMonthly[$summaryMonthlyId] = [
                                    'month' => $dateKeyMonthly,
                                    'branchId' => 'global',
                                    'finance' => ['totalIncome' => 0, 'totalPiutang' => 0],
                                    'operations' => ['totalOrders' => 0],
                                    'servicesCount' => [],
                                    'updatedAt' => Carbon::now()->toIso8601String()
                                ];
                            }

                            $globalSummaryMonthly[$summaryMonthlyId]['finance']['totalIncome'] += $price;
                            $globalSummaryMonthly[$summaryMonthlyId]['finance']['totalPiutang'] += $piutang;
                            $globalSummaryMonthly[$summaryMonthlyId]['operations']['totalOrders'] += 1;

                            // Hitung Layanan
                            if (isset($orderData['items']) && is_array($orderData['items'])) {
                                foreach ($orderData['items'] as $item) {
                                    $namaItem = $item['name'] ?? $item['layanan'] ?? 'Lainnya';
                                    $qty = $item['qty'] ?? 1;
                                    
                                    // Daily
                                    $globalSummaryDaily[$summaryDailyId]['servicesCount'][$namaItem] = 
                                        ($globalSummaryDaily[$summaryDailyId]['servicesCount'][$namaItem] ?? 0) + $qty;
                                        
                                    // Monthly
                                    $globalSummaryMonthly[$summaryMonthlyId]['servicesCount'][$namaItem] = 
                                        ($globalSummaryMonthly[$summaryMonthlyId]['servicesCount'][$namaItem] ?? 0) + $qty;
                                }
                            } elseif (isset($orderData['layanan'])) {
                                $namaItem = $orderData['layanan'];
                                // Daily
                                $globalSummaryDaily[$summaryDailyId]['servicesCount'][$namaItem] = 
                                    ($globalSummaryDaily[$summaryDailyId]['servicesCount'][$namaItem] ?? 0) + 1;
                                // Monthly
                                $globalSummaryMonthly[$summaryMonthlyId]['servicesCount'][$namaItem] = 
                                    ($globalSummaryMonthly[$summaryMonthlyId]['servicesCount'][$namaItem] ?? 0) + 1;
                            }

                            // Tulis ke Global Orders
                            $orderData['branchId'] = $branchId;
                            $orderData['cashierId'] = $userId;
                            $orderData['cashierName'] = $cashierName;
                            
                            $firestore->collection('orders')->document($orderId)->set($orderData);
                            $totalMigrated++;

                        } catch (\Exception $e) {
                            $this->error("Failed to parse date for order {$orderId}: " . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("Failed reading orders for {$userId}");
                }
            }

            // Tulis Aggregates
            $this->info("Writing Aggregates...");
            $batchWrites = 0;

            foreach ($globalSummaryDaily as $id => $data) {
                $firestore->collection('dashboard_summary_daily')->document($id)->set($data);
                $batchWrites++;
            }

            foreach ($globalSummaryMonthly as $id => $data) {
                $firestore->collection('dashboard_summary_monthly')->document($id)->set($data);
                $batchWrites++;
            }

            $this->info("Migration completed successfully!");
            $this->info("Total Orders Migrated: {$totalMigrated}");
            $this->info("Total Summary Documents Created: {$batchWrites}");

        } catch (\Exception $e) {
            $this->error('Migration Failed: ' . $e->getMessage());
        }
    }
}

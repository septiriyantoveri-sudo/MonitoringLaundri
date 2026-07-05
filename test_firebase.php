<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $factory = app('firebase')->getFactory();
    $rtdb = app('firebase.database');
    
    $reference = $rtdb->getReference('/');
    $snapshot = $reference->getSnapshot();
    $value = $snapshot->getValue();
    
    echo "=== REALTIME DATABASE ROOT ===\n";
    if (is_array($value)) {
        foreach (array_keys($value) as $key) {
            echo "Key: " . $key . "\n";
            echo json_encode(array_slice($value[$key], 0, 2, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        }
    } else {
        echo "Root is not an array, value: " . print_r($value, true) . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

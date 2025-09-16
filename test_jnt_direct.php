<?php

use App\Services\Shipping\JntService;

$service = new JntService();
$result = $service->tariffInquiry([
    'sendSiteCode' => 'JAKARTA',
    'destAreaCode' => 'KALIDERES', 
    'weight' => 2
]);

echo "=== J&T Tariff Test Result ===\n";
echo json_encode($result, JSON_PRETTY_PRINT);
echo "\n";

#!/usr/bin/env powershell
# J&T Tariff Testing Script
# Usage: .\test_jnt_tariff.ps1

Write-Host "Testing J&T Tariff Endpoint..." -ForegroundColor Green

# Test 1: Basic tariff inquiry
Write-Host "`nTest 1: Jakarta to Bandung (1.5kg)" -ForegroundColor Yellow
$response1 = Invoke-WebRequest -Uri "http://127.0.0.1:8092/api/shipping/jnt/tariff" -Method POST -Headers @{"Content-Type"="application/json"; "Accept"="application/json"} -Body '{"sendSiteCode": "JKT01", "destAreaCode": "BDG01", "weight": 1.5}'
Write-Host "Status: $($response1.StatusCode)"
Write-Host "Response: $($response1.Content)" 

# Test 2: Different weight
Write-Host "`nTest 2: Jakarta to Surabaya (2.0kg)" -ForegroundColor Yellow  
$response2 = Invoke-WebRequest -Uri "http://127.0.0.1:8092/api/shipping/jnt/tariff" -Method POST -Headers @{"Content-Type"="application/json"; "Accept"="application/json"} -Body '{"sendSiteCode": "JKT01", "destAreaCode": "SBY01", "weight": 2.0}'
Write-Host "Status: $($response2.StatusCode)"
Write-Host "Response: $($response2.Content)"

# Test 3: Validation error (missing required field)
Write-Host "`nTest 3: Missing sendSiteCode (should fail)" -ForegroundColor Red
try {
    $response3 = Invoke-WebRequest -Uri "http://127.0.0.1:8092/api/shipping/jnt/tariff" -Method POST -Headers @{"Content-Type"="application/json"; "Accept"="application/json"} -Body '{"destAreaCode": "BDG01", "weight": 1.5}'
    Write-Host "Status: $($response3.StatusCode)"
    Write-Host "Response: $($response3.Content)"
} catch {
    Write-Host "Error (Expected): $($_.Exception.Message)"
}

Write-Host "`nTesting Complete!" -ForegroundColor Green
Write-Host "Note: Currently using MOCK mode (JNT_MOCK=true in .env)" -ForegroundColor Blue
Write-Host "Note: To test with real J&T API, set JNT_MOCK=false and provide working JNT_BASE_URL + JNT_SIGN_KEY" -ForegroundColor Blue

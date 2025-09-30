# Test Banner API Endpoints
Write-Host "=== Testing Banner API Endpoints ===" -ForegroundColor Green
Write-Host ""

$baseUrl = "http://127.0.0.1:9001"

# Test 1: Get all banners (public endpoint)
Write-Host "1. Testing GET /api/banners" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/banners" -Method GET -Headers @{'Accept'='application/json'}
    Write-Host "✅ Success!" -ForegroundColor Green
    Write-Host "Response: $($response | ConvertTo-Json -Depth 2)" -ForegroundColor Cyan
} catch {
    Write-Host "❌ Failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# Test 2: Test alive endpoint
Write-Host "2. Testing GET /api/test-alive" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/test-alive" -Method GET -Headers @{'Accept'='application/json'}
    Write-Host "✅ Success!" -ForegroundColor Green
    Write-Host "Response: $($response | ConvertTo-Json)" -ForegroundColor Cyan
} catch {
    Write-Host "❌ Failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Admin Panel URLs ===" -ForegroundColor Yellow
Write-Host "Admin Dashboard: $baseUrl/simple-admin" -ForegroundColor Cyan
Write-Host "Banner Management: $baseUrl/simple-admin/banners" -ForegroundColor Cyan

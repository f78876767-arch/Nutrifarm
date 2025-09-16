Write-Host "Testing J&T Tariff API..." -ForegroundColor Green

try {
    $response = Invoke-RestMethod -Uri "http://127.0.0.1:8092/api/shipping/jnt/tariff" -Method POST -Body '{"sendSiteCode":"JAKARTA","destAreaCode":"KALIDERES","weight":2}' -ContentType "application/json"
    
    Write-Host "=== SUCCESS ===" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 4
    
    Write-Host "`nAnalysis:" -ForegroundColor Yellow
    if ($response.is_success -eq "true") {
        Write-Host "✅ Tariff check successful" -ForegroundColor Green
        if ($response.content -and $response.content.tariff) {
            $tariff = $response.content.tariff[0]
            Write-Host "💰 Fee: IDR $($tariff.totalFee)" -ForegroundColor Cyan
            Write-Host "🚚 Service: $($tariff.service)" -ForegroundColor Cyan
            Write-Host "⏱️ ETD: $($tariff.etd) days" -ForegroundColor Cyan
        }
    } else {
        Write-Host "❌ Tariff check failed: $($response.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "=== ERROR ===" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    
    if ($_.Exception.Response) {
        Write-Host "Status: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
    }
}

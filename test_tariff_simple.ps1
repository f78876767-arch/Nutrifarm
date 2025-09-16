$response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/shipping/jnt/tariff" -Method POST -Body '{"sendSiteCode":"JAKARTA","destAreaCode":"KALIDERES","weight":2}' -ContentType "application/json"
$response | ConvertTo-Json -Depth 4

# Email Template Changer Script
# Gunakan script ini untuk mengganti template email verification

Write-Host "=== Nutrifarm Email Template Changer ===" -ForegroundColor Green
Write-Host ""

# Path ke template saat ini
$currentTemplate = "c:\Users\SEI20\Nutrifarm-arch\resources\views\emails\verification-code.blade.php"
$backupTemplate = "c:\Users\SEI20\Nutrifarm-arch\resources\views\emails\verification-code.blade.php.backup"

# Path ke template baru yang tersedia
$templatesDir = "c:\Users\SEI20\Nutrifarm-arch\email-template-examples"

Write-Host "Template yang tersedia:" -ForegroundColor Yellow
Write-Host "1. Nutrifarm Branded (RECOMMENDED)" -ForegroundColor Green
Write-Host "2. Modern Minimal" -ForegroundColor Cyan
Write-Host "3. Dark Theme" -ForegroundColor Cyan  
Write-Host "4. Mobile-First Card Style" -ForegroundColor Cyan
Write-Host "5. Corporate Professional" -ForegroundColor Cyan
Write-Host "6. Restore Original Template" -ForegroundColor Cyan
Write-Host ""

$choice = Read-Host "Pilih template (1-6)"

# Backup template saat ini jika belum ada backup
if (-not (Test-Path $backupTemplate)) {
    Write-Host "Membuat backup template saat ini..." -ForegroundColor Yellow
    Copy-Item $currentTemplate $backupTemplate
}

switch ($choice) {
    "1" {
        Write-Host "Menerapkan template Nutrifarm Branded..." -ForegroundColor Green
        Copy-Item "$templatesDir\nutrifarm-branded.blade.php" $currentTemplate -Force
        Write-Host "✅ Template Nutrifarm Branded berhasil diterapkan!" -ForegroundColor Green
    }
    "2" {
        Write-Host "Menerapkan template Modern Minimal..." -ForegroundColor Green
        Copy-Item "$templatesDir\modern-minimal.blade.php" $currentTemplate -Force
        Write-Host "✅ Template Modern Minimal berhasil diterapkan!" -ForegroundColor Green
    }
    "3" {
        Write-Host "Menerapkan template Dark Theme..." -ForegroundColor Green
        Copy-Item "$templatesDir\dark-theme.blade.php" $currentTemplate -Force
        Write-Host "✅ Template Dark Theme berhasil diterapkan!" -ForegroundColor Green
    }
    "4" {
        Write-Host "Menerapkan template Mobile-First Card..." -ForegroundColor Green
        Copy-Item "$templatesDir\mobile-card.blade.php" $currentTemplate -Force
        Write-Host "✅ Template Mobile-First Card berhasil diterapkan!" -ForegroundColor Green
    }
    "5" {
        Write-Host "Menerapkan template Corporate Professional..." -ForegroundColor Green
        Copy-Item "$templatesDir\corporate-professional.blade.php" $currentTemplate -Force
        Write-Host "✅ Template Corporate Professional berhasil diterapkan!" -ForegroundColor Green
    }
    "6" {
        if (Test-Path $backupTemplate) {
            Write-Host "Mengembalikan template original..." -ForegroundColor Yellow
            Copy-Item $backupTemplate $currentTemplate -Force
            Write-Host "✅ Template original berhasil dikembalikan!" -ForegroundColor Green
        } else {
            Write-Host "❌ Backup template tidak ditemukan!" -ForegroundColor Red
        }
    }
    default {
        Write-Host "❌ Pilihan tidak valid!" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "=== Cara Test Template Baru ===" -ForegroundColor Yellow
Write-Host "1. Jalankan Laravel server: php artisan serve" -ForegroundColor White
Write-Host "2. Test kirim email verification dari mobile app" -ForegroundColor White
Write-Host "3. Cek email yang diterima dengan design baru" -ForegroundColor White
Write-Host ""
Write-Host "=== Untuk Kembali ke Template Lama ===" -ForegroundColor Yellow
Write-Host "Jalankan script ini lagi dan pilih opsi 6" -ForegroundColor White
Write-Host ""

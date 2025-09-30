# 📧 Email Template Customization Guide

## Overview
Panduan lengkap untuk mengubah format dan layout email verification code di Nutrifarm system.

## 🎨 Template Yang Tersedia

### 1. **Nutrifarm Branded** ⭐ (RECOMMENDED)
- **Design**: Custom branding dengan tema organic/green
- **Features**: 
  - Hero section dengan gradient hijau
  - Icon daun dan branding Nutrifarm yang prominent
  - Preview fitur-fitur app (organic vegetables, delivery, etc.)
  - Responsive design untuk mobile & desktop
- **Best For**: Production use, brand consistency

### 2. **Modern Minimal**
- **Design**: Clean, minimalist dengan focus pada functionality
- **Features**:
  - Simple gradient background
  - Large, prominent verification code
  - Mobile-first responsive design
  - Modern typography
- **Best For**: Users yang suka design simpel dan clean

### 3. **Dark Theme**
- **Design**: Dark mode dengan accent green
- **Features**:
  - Black/dark gray background
  - Glowing verification code effect
  - Premium look & feel
  - Grain texture overlay
- **Best For**: Modern apps, night-time usage

### 4. **Mobile-First Card Style**
- **Design**: Multiple cards layout seperti mobile app UI
- **Features**:
  - Card-based layout
  - App icon style branding
  - Compact design for mobile screens
  - Progressive disclosure of information
- **Best For**: Mobile-heavy user base

### 5. **Corporate Professional**
- **Design**: Business/enterprise style
- **Features**:
  - Clean header with company info
  - Step-by-step verification instructions
  - Professional typography
  - Formal tone and layout
- **Best For**: B2B applications, enterprise use

## 🚀 Cara Mengganti Template

### Method 1: Menggunakan Script (RECOMMENDED)
```powershell
# Jalankan script di PowerShell
.\change-email-template.ps1

# Pilih template yang diinginkan (1-6)
# Script akan otomatis backup template lama
```

### Method 2: Manual Copy-Paste
```powershell
# 1. Backup template saat ini
Copy-Item "resources\views\emails\verification-code.blade.php" "resources\views\emails\verification-code.blade.php.backup"

# 2. Copy template baru
Copy-Item "email-template-examples\nutrifarm-branded.blade.php" "resources\views\emails\verification-code.blade.php" -Force
```

## 🛠️ Customization Lebih Lanjut

### Mengubah Warna Brand
Edit bagian CSS di template yang dipilih:

```css
/* Ganti warna hijau default dengan warna brand Anda */
.primary-color { color: #4CAF50; }    /* Ganti dengan warna utama */
.secondary-color { color: #2E7D32; }  /* Ganti dengan warna sekunder */
.accent-color { color: #81C784; }     /* Ganti dengan warna accent */
```

### Mengubah Typography
```css
/* Ganti font family */
body {
    font-family: 'Your-Custom-Font', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Ganti ukuran verification code */
.verification-code {
    font-size: 42px;     /* Ubah sesuai keinginan */
    letter-spacing: 12px; /* Ubah jarak antar huruf */
}
```

### Menambah Logo/Image
```html
<!-- Tambahkan di bagian header -->
<img src="https://your-domain.com/logo.png" alt="Nutrifarm Logo" style="width: 120px; height: auto;">
```

### Mengubah Pesan/Text
Edit bagian HTML:
```html
<!-- Ganti welcome message -->
<h2 class="welcome-title">Custom Welcome Message!</h2>
<p class="welcome-text">
    Your custom description here...
</p>

<!-- Ganti footer message -->
<p class="footer-text">
    Custom footer message from Your Company Team
</p>
```

## 📱 Testing Template Baru

### 1. Test via Laravel Artisan
```bash
# Start Laravel development server
php artisan serve

# Test email dari mobile app atau API
curl -X POST http://127.0.0.1:8000/api/send-verification-email \
     -H "Content-Type: application/json" \
     -d '{"email":"your-test@email.com"}'
```

### 2. Preview Template di Browser
```bash
# Create test route (tambahkan di routes/web.php)
Route::get('/preview-email', function () {
    return view('emails.verification-code', ['code' => '123456']);
});

# Akses http://127.0.0.1:8000/preview-email
```

### 3. Test dengan MailHog (Local Testing)
```bash
# Install MailHog untuk menangkap email secara lokal
# Set .env MAIL_DRIVER=smtp, MAIL_HOST=127.0.0.1, MAIL_PORT=1025
# Akses http://127.0.0.1:8025 untuk melihat email
```

## 🔧 Troubleshooting

### Template Tidak Berubah?
```bash
# Clear Laravel cache
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Restart Laravel server
php artisan serve
```

### Email Tidak Terkirim?
1. Cek konfigurasi SMTP di `.env`
2. Pastikan MAIL_FROM_ADDRESS dan MAIL_FROM_NAME sudah benar
3. Test koneksi SMTP secara manual
4. Cek Laravel logs: `storage/logs/laravel.log`

### Template Tidak Responsive?
1. Test di berbagai ukuran layar
2. Gunakan browser dev tools untuk mobile simulation
3. Tambahkan media queries untuk breakpoint yang dibutuhkan

## 📊 Template Comparison

| Feature | Current | Nutrifarm Branded | Modern Minimal | Dark Theme | Mobile Card | Corporate |
|---------|---------|-------------------|----------------|------------|-------------|-----------|
| Brand Integration | ⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐ | ⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ |
| Mobile Responsive | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| Visual Appeal | ⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| Professional Look | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| User Engagement | ⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |

## 🎯 Recommendations

### For Production Use:
**Pilih: Nutrifarm Branded** - Memberikan brand consistency dan user engagement terbaik

### For Development/Testing:
**Pilih: Modern Minimal** - Simple, fast loading, easy to customize

### For Premium Apps:
**Pilih: Dark Theme** - Modern, premium look untuk high-end applications

### For Mobile-First Apps:
**Pilih: Mobile Card** - Optimized untuk mobile user experience

### For B2B/Enterprise:
**Pilih: Corporate Professional** - Formal, trustworthy appearance

## 📝 Notes
- Semua template sudah include responsive design
- Variable `{{ $code }}` akan otomatis diganti dengan verification code
- Template menggunakan inline CSS untuk kompatibilitas email client
- Backup template original selalu dibuat otomatis
- Text content bisa diubah sesuai kebutuhan di file .blade.php

## 🔗 Files Location
- **Current Template**: `resources/views/emails/verification-code.blade.php`
- **Template Examples**: `email-template-examples/`
- **Change Script**: `change-email-template.ps1`
- **Mailable Class**: `app/Mail/VerificationCodeMail.php`

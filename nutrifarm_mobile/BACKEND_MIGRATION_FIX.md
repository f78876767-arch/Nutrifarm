# Backend Database Column Fix

## 🐛 New Issue: Missing Database Columns

**Error**: `Column not found: 1054 Unknown column 'payment_method' in 'field list'`

**Root Cause**: Tabel `orders` tidak memiliki beberapa kolom yang dibutuhkan.

## 📊 Analysis dari Error Log

SQL yang gagal:
```sql
insert into `orders` (
    `user_id`, 
    `total`, 
    `status`, 
    `shipping_method`, 
    `payment_method`,     -- ❌ MISSING
    `payment_status`,     -- ❌ MISSING  
    `external_id`,        -- ❌ MISSING
    `updated_at`, 
    `created_at`
) values (9, 309000, pending, regular, xendit, pending, nutrifarm-order-689cbf8aa593b, ...)
```

## 🛠️ Laravel Migration Needed

Buat migration baru untuk menambahkan kolom yang hilang:

```bash
php artisan make:migration add_missing_columns_to_orders_table
```

**Migration content:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add missing columns
            $table->string('payment_method')->default('manual')->after('status');
            $table->string('payment_status')->default('pending')->after('payment_method');
            $table->string('external_id')->unique()->nullable()->after('payment_status');
            $table->string('xendit_invoice_id')->nullable()->after('external_id');
            $table->text('xendit_invoice_url')->nullable()->after('xendit_invoice_id');
            $table->timestamp('paid_at')->nullable()->after('xendit_invoice_url');
            $table->string('shipping_method')->default('regular')->after('paid_at');
            $table->text('notes')->nullable()->after('shipping_method');
            $table->text('delivery_address')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_status', 
                'external_id',
                'xendit_invoice_id',
                'xendit_invoice_url',
                'paid_at',
                'shipping_method',
                'notes',
                'delivery_address'
            ]);
        });
    }
};
```

## 🚀 Quick Fix Commands

Jalankan di backend Laravel:

```bash
# 1. Buat migration
php artisan make:migration add_missing_columns_to_orders_table

# 2. Edit file migration (copy content di atas)

# 3. Jalankan migration
php artisan migrate

# 4. Verify columns exist
php artisan tinker
>>> Schema::getColumnListing('orders')
```

## 📋 Expected Columns After Migration

Setelah migration, tabel `orders` harus punya:
- ✅ `id`
- ✅ `user_id` 
- ✅ `total`
- ✅ `status`
- ✅ `payment_method` ← **NEW**
- ✅ `payment_status` ← **NEW**
- ✅ `external_id` ← **NEW**
- ✅ `xendit_invoice_id` ← **NEW**
- ✅ `xendit_invoice_url` ← **NEW**
- ✅ `paid_at` ← **NEW**
- ✅ `shipping_method` ← **NEW**
- ✅ `notes` ← **NEW**
- ✅ `delivery_address` ← **NEW**
- ✅ `created_at`
- ✅ `updated_at`

## 🔍 Verify Order Model

Pastikan juga model `Order.php` punya fillable fields:

```php
class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'external_id',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'paid_at',
        'shipping_method',
        'notes',
        'delivery_address',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'total' => 'decimal:2',
    ];
}
```

## ⚡ Immediate Action

**Jalankan migration ini di backend Laravel, lalu test checkout lagi!**

Setelah migration selesai, Flutter checkout seharusnya berhasil membuat order. 🚀

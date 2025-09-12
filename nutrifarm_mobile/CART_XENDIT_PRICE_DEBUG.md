# Cart vs Xendit Price Debug Guide

## 🚨 Current Issue:
- **Cart shows:** Rp 133.000
- **Xendit shows:** Rp 130.500
- **Difference:** Rp -2.500 (Xendit lower)

## 🔍 Possible Causes:

### 1. **Discount Application Difference**
Cart menggunakan `item.effectivePrice` yang sudah termasuk diskon, tapi backend mungkin tidak menggunakan harga yang dikirim dari frontend.

### 2. **Backend Recalculation**
Backend mungkin melakukan recalculate harga berdasarkan `product_id` dan `variant_id` dari database, mengabaikan `price` yang dikirim frontend.

### 3. **Rounding Issues**
Perbedaan pembulatan antara frontend dan backend calculations.

## 🐛 Debug Steps:

### Step 1: Check Frontend Cart Items
```dart
// Add this debug in checkout_service.dart after orderItems creation:
for (final item in orderItems) {
  print('🔍 ITEM DEBUG: ${item['product_id']} -> price: ${item['price']}, variant_id: ${item['variant_id']}, qty: ${item['quantity']}');
  final totalItemPrice = (item['price'] as double) * (item['quantity'] as int);
  print('🔍 ITEM TOTAL: ${totalItemPrice}');
}

final frontendTotal = orderItems.fold<double>(0, (sum, item) => sum + ((item['price'] as double) * (item['quantity'] as int)));
print('🔍 FRONTEND TOTAL CALCULATED: ${frontendTotal}');
print('🔍 FRONTEND CART SUBTOTAL: ${cartTotal}');
```

### Step 2: Check Backend Response
```dart
// Add this debug in checkout_service.dart after order creation:
print('🔍 BACKEND RESPONSE: ${response}');
if (response['order'] != null) {
  print('🔍 BACKEND ORDER TOTAL: ${response['order']['total_amount']}');
}
```

### Step 3: Check Xendit Invoice
```dart
// Add this in _handleOrderResponse method:
if (response['invoice'] != null) {
  print('🔍 XENDIT INVOICE AMOUNT: ${response['invoice']['amount']}');
}
```

## 🔧 Potential Fixes:

### Fix 1: Force Backend to Use Frontend Prices
Backend `OrderController` harus menggunakan price dari request, bukan recalculate:

```php
// In backend OrderController
foreach ($request->items as $item) {
    // ❌ Don't recalculate
    // $price = Product::find($item['product_id'])->price;
    
    // ✅ Use price from frontend
    $price = $item['price'];
}
```

### Fix 2: Add Price Validation
```php
// Backend validation
$calculatedPrice = Product::find($item['product_id'])->getEffectivePrice($item['variant_id']);
if (abs($calculatedPrice - $item['price']) > 0.01) {
    throw new Exception("Price mismatch detected");
}
```

## 🧪 Test Commands:

1. **Debug Cart Contents:**
```bash
# Add items to cart and check console output
```

2. **Debug Checkout Process:**
```bash
# Go through checkout and check all debug prints
```

3. **Check Backend Logs:**
```bash
# Check Laravel logs for price calculations
```

## ⚠️ Next Actions:

1. **Add debug prints** to checkout process
2. **Run checkout** with debug enabled
3. **Check backend** price calculation logic
4. **Compare** frontend vs backend totals
5. **Fix** price calculation mismatch

---
**Priority:** CRITICAL - Payment amounts must match cart totals exactly.

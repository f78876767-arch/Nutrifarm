# Fix: Variant Pricing Issue in Cart/Checkout - URGENT

## 🐛 Issue: Wrong Price in Xendit Payment

**Problem**: Cart menampilkan produk Madu 500ml (Rp 50.000), tapi di Xendit terbayar Rp 70.000 (harga Madu 1L default).

**Root Cause**: Checkout service tidak mengirim `variant_id` dan `effective price` ke backend, sehingga backend menggunakan harga default product.

## ✅ Fix Implemented

### 1. **Updated Order Items Creation**
```dart
// BEFORE (❌ Wrong)
final orderItems = cartService.items.map((item) => {
  'product_id': item.product.id,
  'quantity': item.quantity,
  'price': item.product.discountPrice ?? item.product.price, // ❌ Default price
  'variant_id': null, // ❌ No variant
}).toList();

// AFTER (✅ Fixed)
final orderItems = cartService.items.map((item) => {
  'product_id': item.product.id,
  'quantity': item.quantity,
  'price': item.effectivePrice, // ✅ Variant-aware price
  'variant_id': item.selectedVariant?.id, // ✅ Send variant ID
}).toList();
```

### 2. **Updated Cart Sync Functions**
Fixed both cart sync fallback methods to also include variant_id:
```dart
// BEFORE
'variant_id': null,

// AFTER
'variant_id': item.selectedVariant?.id,
```

## 🔍 How It Works Now

### Frontend Cart Item Structure:
```dart
class CartItem {
  final Product product;
  final Variant? selectedVariant;
  int quantity;
  
  // Get price considering variant
  double get effectivePrice {
    if (selectedVariant != null) {
      return selectedVariant!.effectivePrice;
    }
    return product.discountPrice ?? product.price;
  }
}
```

### Data Sent to Backend:
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 1,
      "price": 50000,  // ✅ Correct variant price
      "variant_id": 2  // ✅ 500ml variant ID
    }
  ]
}
```

## 🧪 Testing Steps

1. **Add variant product to cart**:
   - Pilih Madu 500ml (Rp 50.000)
   - Verify cart shows correct price

2. **Go to checkout**:
   - Check console logs: `🛒 Order items prepared`
   - Should show variant_id and correct price

3. **Complete payment**:
   - Xendit invoice should show Rp 50.000
   - Not Rp 70.000 (default 1L price)

## 📊 Debug Information

### Console Logs to Watch:
```
🛒 Order items prepared: [
  {
    product_id: 1, 
    quantity: 1, 
    price: 50000.0,     // ✅ Should be variant price
    variant_id: 2       // ✅ Should be variant ID, not null
  }
]
```

### Backend Requirements:
Backend OrderController should handle:
- `items[].variant_id` for variant-specific processing
- `items[].price` as authoritative price (don't recalculate from product.price)

## 🚀 Expected Result

After this fix:
- ✅ Cart total: Rp 50.000 (Madu 500ml)
- ✅ Xendit invoice: Rp 50.000 (same amount)
- ✅ Backend order: variant_id=2, price=50000

**Test sekarang untuk memastikan harga di cart dan Xendit sudah matching!** 🎯

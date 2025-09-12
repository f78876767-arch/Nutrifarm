# AI Agent Backend Fix Prompt - Price Recalculation Issue

## 🤖 **Prompt for Backend AI Agent:**

```
CRITICAL BUG FIX NEEDED: Backend is recalculating product prices instead of using frontend-provided prices, causing payment amount mismatch.

ISSUE CONTEXT:
- Flutter mobile app sends order with items and calculated prices
- Backend receives correct prices but recalculates them from database
- This causes cart total (133,000 IDR) to not match Xendit payment total (130,500 IDR)

EVIDENCE FROM DEBUG LOGS:
Frontend sends: {product_id: 7, quantity: 1, price: 83000.0, variant_id: 28}
Backend saves: {product_id: 7, variant_id: 28, quantity: 1, price: "80500.00"}

The backend is IGNORING the price: 83000.0 from frontend and recalculating to 80500.00 from database.

REQUIRED FIX:
In the OrderController (or wherever order creation happens), change the order item creation logic to USE the price provided by the frontend instead of recalculating from Product/Variant models.

CURRENT WRONG CODE (likely):
foreach ($request->items as $item) {
    $product = Product::find($item['product_id']);
    $variant = Variant::find($item['variant_id']);
    $price = $variant ? $variant->price : $product->price; // ❌ WRONG - recalculates
    
    OrderProduct::create([
        'price' => $price // ❌ Uses recalculated price
    ]);
}

CORRECT CODE NEEDED:
foreach ($request->items as $item) {
    OrderProduct::create([
        'product_id' => $item['product_id'],
        'variant_id' => $item['variant_id'],
        'quantity' => $item['quantity'],
        'price' => $item['price'], // ✅ Use frontend-provided price
    ]);
}

VALIDATION (optional but recommended):
Add price validation to ensure frontend isn't sending malicious prices:
$expectedPrice = $this->calculateVariantPrice($item['product_id'], $item['variant_id']);
if (abs($item['price'] - $expectedPrice) > 1) {
    throw new Exception('Price validation failed');
}

EXPECTED RESULT AFTER FIX:
- Frontend cart: 133,000 IDR
- Backend order total: 133,000 IDR  
- Xendit payment: 133,000 IDR (all amounts match)

URGENCY: CRITICAL - Payment discrepancies affect customer trust and transaction accuracy.

Please locate the order creation endpoint and fix the price calculation logic to trust frontend-provided prices.
```

## 📋 **Additional Context Files to Share:**

### Current Project Structure Context:
```
Laravel Backend Structure (likely):
- app/Http/Controllers/OrderController.php (main fix needed here)
- app/Models/Order.php
- app/Models/OrderProduct.php  
- app/Models/Product.php
- app/Models/Variant.php
- routes/api.php (POST /orders endpoint)

Database Tables:
- orders (id, user_id, total, status, payment_method, etc.)
- order_products (id, order_id, product_id, variant_id, quantity, price)
- products (id, name, price, discount_price, etc.)
- variants (id, product_id, name, price, etc.)
```

### API Request Format:
```json
POST /orders
{
  "shipping_method": "regular",
  "payment_method": "xendit",  
  "delivery_address": "Alamat default",
  "items": [
    {
      "product_id": 6,
      "quantity": 1,
      "price": 50000.0,
      "variant_id": 17
    },
    {
      "product_id": 7, 
      "quantity": 1,
      "price": 83000.0,
      "variant_id": 28
    }
  ]
}
```

### Expected Response Format:
```json
{
  "order": {
    "id": 11,
    "user_id": 9,
    "total": 133000,  // ✅ Should match frontend total
    "status": "pending",
    "order_products": [
      {
        "product_id": 6,
        "variant_id": 17, 
        "quantity": 1,
        "price": "50000.00"  // ✅ Should match frontend price
      },
      {
        "product_id": 7,
        "variant_id": 28,
        "quantity": 1, 
        "price": "83000.00"  // ✅ Should match frontend price (not 80500!)
      }
    ],
    "xendit_invoice_url": "https://..."
  }
}
```

## 🔍 **Key Investigation Points:**

1. **Find the order creation logic** - likely in `OrderController@store` or similar
2. **Look for price calculation** - anywhere that calculates price from Product/Variant models
3. **Check total calculation** - make sure order total sums the frontend-provided prices
4. **Verify Xendit integration** - ensure the correct total is sent to Xendit

## 🎯 **Success Criteria:**
After fix, the debug output should show:
- Backend order total: 133000 (not 130500)
- Order product price: 83000.00 (not 80500.00)  
- Xendit invoice amount: 133000 (not 130500)

**Copy this entire prompt to your backend AI agent for immediate context and fix instructions.** 🤖

# Backend Cart Table Issue Fix

## 🐛 Issue: Backend Looking for Wrong Cart Table

**Error**: `No query results for model [App\\Models\\Cart]`  
**Root Cause**: Your Laravel backend OrderController is trying to query the `Cart` model, but your cart data is stored in the `cart_items` table.

## 🔍 Database Structure Analysis

You mentioned that products are saved in `cart_items` table, not `carts`. This suggests:

- ✅ **Table exists**: `cart_items`  
- ❌ **Model issue**: Backend is looking for `Cart` model instead of `CartItem` or similar

## 🛠️ Backend Fixes Needed

### 1. **Check Your Cart Model Reference**

In your Laravel OrderController, you're probably doing something like:
```php
// ❌ WRONG - if you don't have a Cart model
$cart = Cart::where('user_id', auth()->id())->first();

// ✅ CORRECT - should be CartItem or CartProduct
$cartItems = CartItem::where('user_id', auth()->id())->get();
// OR
$cartItems = CartProduct::where('user_id', auth()->id())->get();
```

### 2. **Update OrderController@store Method**

```php
public function store(Request $request)
{
    $user = auth()->user();
    
    // Option 1: Accept items from request (RECOMMENDED)
    if ($request->has('items') && !empty($request->items)) {
        $orderItems = collect($request->items);
        $totalAmount = $orderItems->sum(function($item) {
            return $item['quantity'] * $item['price'];
        });
    } 
    // Option 2: Get from cart_items table
    else {
        // ✅ Use the correct model name (CartItem, CartProduct, etc.)
        $cartItems = CartItem::where('user_id', $user->id)
            ->with('product')  // Ensure you have the relationship
            ->get();
            
        // OR if your model is named CartProduct:
        // $cartItems = CartProduct::where('user_id', $user->id)
        //     ->with('product')
        //     ->get();
            
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }
        
        $totalAmount = $cartItems->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        $orderItems = $cartItems->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'variant_id' => $item->variant_id ?? null,
            ];
        });
    }
    
    // Generate external_id
    $externalId = 'nutrifarm-order-' . uniqid();
    
    // Create order
    $order = Order::create([
        'user_id' => $user->id,
        'external_id' => $externalId,
        'total_amount' => $totalAmount,
        'status' => 'pending',
        'payment_status' => 'pending',
        'shipping_method' => $request->shipping_method ?? 'regular',
        'payment_method' => $request->payment_method ?? 'xendit',
        'notes' => $request->notes,
        'delivery_address' => $request->delivery_address,
    ]);
    
    // Create order items
    foreach ($orderItems as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'total' => $item['quantity'] * $item['price'],
            'variant_id' => $item['variant_id'] ?? null,
        ]);
    }
    
    // Create Xendit invoice
    $xenditResponse = app(XenditService::class)->createInvoice([
        'external_id' => $externalId,
        'payer_email' => $user->email,
        'description' => "Nutrifarm Order #{$order->id}",
        'amount' => $totalAmount,
    ]);
    
    // Update order with Xendit details
    $order->update([
        'xendit_invoice_id' => $xenditResponse['id'],
        'xendit_invoice_url' => $xenditResponse['invoice_url'],
    ]);
    
    // Clear cart after successful order creation (only if using database cart)
    if (!$request->has('items')) {
        CartItem::where('user_id', $user->id)->delete();
        // OR: CartProduct::where('user_id', $user->id)->delete();
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Order created successfully',
        'data' => [
            'order' => $order->fresh(),
            'invoice' => [
                'id' => $xenditResponse['id'],
                'invoice_url' => $xenditResponse['invoice_url'],
                'external_id' => $externalId,
            ]
        ],
        'redirect_url' => $xenditResponse['invoice_url']
    ]);
}
```

### 3. **Check Your Cart Model Names**

Look in your `app/Models/` directory and find the correct cart model:
- `CartItem.php`
- `CartProduct.php`  
- `UserCart.php`
- Or whatever you named it

### 4. **Update Cart API Endpoints**

Make sure your cart API endpoints use the same model:

```php
// In CartController or wherever you handle cart
public function index()
{
    $cartItems = CartItem::where('user_id', auth()->id())
        ->with('product')
        ->get();
        
    return response()->json([
        'success' => true,
        'data' => [
            'items' => $cartItems,
            'total' => $cartItems->sum(function($item) {
                return $item->quantity * $item->product->price;
            })
        ]
    ]);
}
```

## 🧪 Quick Test

1. **Check your cart model name**:
   ```bash
   ls app/Models/ | grep -i cart
   ```

2. **Test the updated OrderController** with items passed directly

3. **Check database**:
   ```sql
   SELECT * FROM cart_items WHERE user_id = YOUR_USER_ID;
   ```

## 📋 Action Items

1. ✅ **Identify correct cart model name** (CartItem, CartProduct, etc.)
2. ✅ **Update OrderController to use correct model**
3. ✅ **Add support for direct items in request**
4. ✅ **Test both cart scenarios** (database cart + direct items)
5. ✅ **Update cart clearing logic** to use correct model

The key fix is using the correct model name that matches your `cart_items` table structure!

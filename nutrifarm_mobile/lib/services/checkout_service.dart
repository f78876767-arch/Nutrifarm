import 'package:flutter/foundation.dart';
import 'package:url_launcher/url_launcher.dart';
import 'api_service.dart';
import 'cart_service.dart';
import '../models/order.dart';
import 'event_bus.dart';

class CheckoutService extends ChangeNotifier {
  static final CheckoutService _instance = CheckoutService._internal();
  factory CheckoutService() => _instance;
  CheckoutService._internal();

  bool _isLoading = false;
  String? _error;
  Map<String, dynamic>? _checkoutData;

  // Shipping disabled
  double? get shippingAmount => null;
  String get shippingMethodLabel => 'regular';

  bool get isLoading => _isLoading;
  String? get error => _error;
  Map<String, dynamic>? get checkoutData => _checkoutData;

  Future<bool> processCheckout({
    String? notes,
    String? deliveryAddress,
    required String userEmail,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      print('🛒 Starting order creation with Xendit checkout...');
      
      // Get cart total from CartService
      final cartService = CartService();
      final cartTotal = cartService.subtotal;
      final itemCount = cartService.totalQuantity;
      
      print('🛒 Cart Debug Info:');
      print('   - Total: Rp ${cartTotal}');
      print('   - Item count: $itemCount');
      print('   - Cart items: ${cartService.items.length}');
      
      if (cartTotal <= 0) {
        throw Exception('Cart is empty or total is invalid');
      }
      
      if (itemCount <= 0) {
        throw Exception('No items in cart');
      }
      
      // Prepare order items from frontend cart
      final orderItems = cartService.items.map((item) => {
        'product_id': item.product.id,
        'quantity': item.quantity,
        'price': item.effectivePrice, // Use variant price if selected
        'variant_id': item.selectedVariant?.id, // Send variant ID if selected
      }).toList();
      
      print('🛒 Order items prepared: $orderItems');
      
      // 🔍 DEBUG: Detailed price breakdown
      print('🔍 === PRICE DEBUG START ===');
      print('🔍 CART ITEMS COUNT: ${cartService.items.length}');
      
      for (int i = 0; i < cartService.items.length; i++) {
        final cartItem = cartService.items[i];
        print('🔍 CART ITEM $i: ${cartItem.product.name} (ID: ${cartItem.product.id})');
        print('  - Variant: ${cartItem.selectedVariant?.displayName ?? "None"} (ID: ${cartItem.selectedVariant?.id})');
        print('  - Quantity: ${cartItem.quantity}');
        print('  - Effective Price: Rp ${cartItem.effectivePrice.toStringAsFixed(0)}');
        print('  - Product Original Price: Rp ${cartItem.product.price.toStringAsFixed(0)}');
        print('  - Product Discount Price: ${cartItem.product.discountPrice?.toStringAsFixed(0) ?? "None"}');
        print('  - Total Price: Rp ${cartItem.totalPrice.toStringAsFixed(0)}');
      }
      
      for (int i = 0; i < orderItems.length; i++) {
        final item = orderItems[i];
        print('🔍 ORDER ITEM $i: product_id=${item['product_id']}, price=${item['price']}, variant_id=${item['variant_id']}, qty=${item['quantity']}');
        final totalItemPrice = (item['price'] as double) * (item['quantity'] as int);
        print('  - ITEM TOTAL: Rp ${totalItemPrice.toStringAsFixed(0)}');
      }
      
      final frontendCalculatedTotal = orderItems.fold<double>(0, (sum, item) => sum + ((item['price'] as double) * (item['quantity'] as int)));
      print('🔍 FRONTEND CALCULATED TOTAL: Rp ${frontendCalculatedTotal.toStringAsFixed(0)}');
      print('🔍 CART SERVICE SUBTOTAL: Rp ${cartTotal.toStringAsFixed(0)}');
      print('🔍 === PRICE DEBUG END ===');
      
      // Try to create order with items passed directly first
      try {
        print('🛍️ Creating order with direct item data...');
        final response = await ApiService.createOrder(
          shippingMethod: shippingMethodLabel,
          shippingAmount: shippingAmount,
          paymentMethod: 'xendit',
          notes: notes,
          deliveryAddress: deliveryAddress,
          orderItems: orderItems,
        );
        
        _checkoutData = response;
        print('✅ Order created with direct items: $response');
        
        // Emit event so history shows entry immediately as pending
        try {
          final orderJson = (response['order'] ?? response['data'] ?? response) as Map<String, dynamic>;
          // Build minimal Order object with pending status if not provided
          final createdAt = DateTime.now();
          final items = cartService.items
              .map((ci) => OrderProduct(
                    id: 0,
                    orderId: 0,
                    productId: ci.product.id,
                    variantId: ci.selectedVariant?.id,
                    quantity: ci.quantity,
                    price: ci.effectivePrice,
                    createdAt: createdAt,
                    updatedAt: createdAt,
                    product: ci.product,
                    variant: ci.selectedVariant,
                  ))
              .toList();

          final optimisticOrder = Order(
            id: (orderJson['id'] is int)
                ? orderJson['id'] as int
                : int.tryParse(orderJson['id']?.toString() ?? '') ?? DateTime.now().millisecondsSinceEpoch,
            externalId: orderJson['external_id']?.toString(),
            total: frontendCalculatedTotal,
            status: (orderJson['status']?.toString() ?? 'pending'),
            paymentStatus: (orderJson['payment_status']?.toString() ?? 'pending'),
            paidAt: null,
            createdAt: createdAt,
            xenditInvoiceUrl: orderJson['xendit_invoice_url']?.toString() ?? response['invoice']?['invoice_url']?.toString() ?? response['invoice_url']?.toString(),
            invoicePdfUrl: orderJson['invoice_pdf_url']?.toString(),
            receiptPdfUrl: orderJson['receipt_pdf_url']?.toString(),
            orderProducts: items,
          );
          AppEventBus.I.emitOrderCreated(optimisticOrder);
        } catch (ee) {
          print('⚠️ Failed to emit OrderCreated event: $ee');
        }
        
        // 🔍 DEBUG: Check backend response pricing
        print('🔍 === BACKEND RESPONSE DEBUG ===');
        if (response['order'] != null) {
          final order = response['order'];
          print('🔍 BACKEND ORDER TOTAL: ${order['total']}');
          print('🔍 BACKEND ORDER STATUS: ${order['status']}');
          print('🔍 BACKEND ORDER PRODUCTS COUNT: ${order['order_products']?.length ?? 0}');
          
          if (order['order_products'] != null) {
            final orderProducts = order['order_products'] as List;
            for (int i = 0; i < orderProducts.length; i++) {
              final orderProduct = orderProducts[i];
              print('🔍 BACKEND PRODUCT $i:');
              print('  - Product ID: ${orderProduct['product_id']}');
              print('  - Variant ID: ${orderProduct['variant_id']}');
              print('  - Quantity: ${orderProduct['quantity']}');
              print('  - Price: ${orderProduct['price']}');
              if (orderProduct['product'] != null) {
                final product = orderProduct['product'];
                print('  - Product Name: ${product['name']}');
                print('  - Product Base Price: ${product['price']}');
                print('  - Product Discount Price: ${product['discount_price']}');
              }
            }
          }
        }
        if (response['total_amount'] != null) {
          print('🔍 BACKEND TOTAL AMOUNT: ${response['total_amount']}');
        }
        if (response['amount'] != null) {
          print('🔍 BACKEND AMOUNT: ${response['amount']}');
        }
        print('🔍 === END BACKEND RESPONSE DEBUG ===');
        
        // Handle success and invoice URL
        return await _handleOrderResponse(response);
        
      } catch (e) {
        print('⚠️ Direct order creation failed: $e');
        
        // Check specific error types
        final errorMsg = e.toString().toLowerCase();
        if (errorMsg.contains('no query results') || 
            errorMsg.contains('cart') ||
            errorMsg.contains('empty')) {
          print('🔧 Backend cart issue detected, trying legacy payment fallback...');
          return await _fallbackToDirectXenditPayment(userEmail, notes, cartTotal, itemCount);
        }
        
        print('🔄 Falling back to cart sync method...');
      }
      
      // Fallback: Check if backend cart is synchronized
      print('🛒 Checking backend cart synchronization...');
      try {
        final backendCart = await ApiService.getCart();
        print('🛒 Backend cart response: $backendCart');
        
        // If backend cart is empty but frontend cart has items, sync them
        final backendItemCount = (backendCart['data']?['items'] as List?)?.length ?? 0;
        if (backendItemCount == 0 && itemCount > 0) {
          print('🔄 Backend cart is empty, syncing frontend cart...');
          
          // Convert frontend cart items to format needed for sync
          final cartItemsToSync = cartService.items.map((item) => {
            'product_id': item.product.id,
            'quantity': item.quantity,
            'variant_id': item.selectedVariant?.id, // Send variant ID if selected
          }).toList();
          
          final syncResult = await ApiService.syncCartToBackend(cartItemsToSync);
          if (!syncResult) {
            throw Exception('Failed to synchronize cart with backend');
          }
        }
      } catch (e) {
        print('⚠️ Backend cart check failed: $e');
        
        // Try to sync the cart as a fallback
        if (itemCount > 0) {
          print('🔄 Attempting to sync frontend cart to backend...');
          try {
            final cartItemsToSync = cartService.items.map((item) => {
              'product_id': item.product.id,
              'quantity': item.quantity,
              'variant_id': item.selectedVariant?.id, // Send variant ID if selected
            }).toList();
            
            final syncResult = await ApiService.syncCartToBackend(cartItemsToSync);
            if (!syncResult) {
              throw Exception('Failed to synchronize cart with backend');
            }
          } catch (syncError) {
            print('❌ Cart sync failed: $syncError');
            throw Exception('Backend cart synchronization failed. Please refresh the app and try again.');
          }
        } else {
          throw Exception('Backend cart is not synchronized and frontend cart is empty.');
        }
      }
      
      // Create order with Xendit payment (FALLBACK - uses backend cart)
      final response = await ApiService.createOrder(
        shippingMethod: shippingMethodLabel,
        shippingAmount: shippingAmount,
        paymentMethod: 'xendit',
        notes: notes,
        deliveryAddress: deliveryAddress,
      );
      
      _checkoutData = response;
      print('✅ Order created with backend cart: $response');

      // Emit event so history shows entry immediately as pending (fallback path)
      try {
        final orderJson = (response['order'] ?? response['data'] ?? response) as Map<String, dynamic>;
        final createdAt = DateTime.now();
        final items = cartService.items
            .map((ci) => OrderProduct(
                  id: 0,
                  orderId: 0,
                  productId: ci.product.id,
                  variantId: ci.selectedVariant?.id,
                  quantity: ci.quantity,
                  price: ci.effectivePrice,
                  createdAt: createdAt,
                  updatedAt: createdAt,
                  product: ci.product,
                  variant: ci.selectedVariant,
                ))
            .toList();

        final optimisticOrder = Order(
          id: (orderJson['id'] is int)
              ? orderJson['id'] as int
              : int.tryParse(orderJson['id']?.toString() ?? '') ?? DateTime.now().millisecondsSinceEpoch,
          externalId: orderJson['external_id']?.toString(),
          total: frontendCalculatedTotal,
          status: (orderJson['status']?.toString() ?? 'pending'),
          paymentStatus: (orderJson['payment_status']?.toString() ?? 'pending'),
          paidAt: null,
          createdAt: createdAt,
          xenditInvoiceUrl: orderJson['xendit_invoice_url']?.toString() ?? response['invoice']?['invoice_url']?.toString() ?? response['invoice_url']?.toString(),
          invoicePdfUrl: orderJson['invoice_pdf_url']?.toString(),
          receiptPdfUrl: orderJson['receipt_pdf_url']?.toString(),
          orderProducts: items,
        );
        AppEventBus.I.emitOrderCreated(optimisticOrder);
      } catch (ee) {
        print('⚠️ Failed to emit OrderCreated event (fallback): $ee');
      }
      
      return await _handleOrderResponse(response);
      
    } catch (e) {
      print('❌ Checkout error: $e');
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Fallback method for direct Xendit payment (temporary workaround)
  Future<bool> _fallbackToDirectXenditPayment(
    String userEmail, 
    String? notes, 
    double cartTotal, 
    int itemCount
  ) async {
    try {
      print('💳 Using legacy direct Xendit payment as fallback...');
      
      // Generate unique external ID for this order
      final timestamp = DateTime.now().millisecondsSinceEpoch;
      final externalId = 'nutrifarm-order-$timestamp';
      
      // Create description
      final description = notes != null && notes.isNotEmpty
          ? 'Nutrifarm Order ($itemCount items) - $notes'
          : 'Nutrifarm Order ($itemCount items)';
      
      // Create Xendit payment invoice directly
      final response = await ApiService.createXenditPayment(
        amount: cartTotal.round(), // Convert to integer (cents/smallest unit)
        externalId: externalId,
        payerEmail: userEmail,
        description: description,
      );
      
      _checkoutData = response;
      print('✅ Legacy Xendit invoice created: $response');
      
      // Handle the response
      if (response['invoice_url'] != null) {
        final invoiceUrl = response['invoice_url'] as String;
        print('💳 Opening Xendit invoice URL: $invoiceUrl');
        
        // Open payment URL
        await _openPaymentUrl(invoiceUrl);
        
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        throw Exception('No invoice URL received from legacy Xendit');
      }
    } catch (e) {
      print('❌ Legacy Xendit fallback failed: $e');
      throw e;
    }
  }

  // Helper method to handle order response and extract invoice URL
  Future<bool> _handleOrderResponse(Map<String, dynamic> response) async {
    // 🔍 DEBUG: Check invoice pricing
    if (response['invoice'] != null) {
      print('🔍 XENDIT INVOICE AMOUNT: ${response['invoice']['amount']}');
      print('🔍 XENDIT INVOICE CURRENCY: ${response['invoice']['currency']}');
    }
    
    // Check if we have invoice URL from the order response
    String? invoiceUrl;
    
    // The response structure might be nested, check both possible locations
    if (response['invoice']?['invoice_url'] != null) {
      invoiceUrl = response['invoice']['invoice_url'] as String;
    } else if (response['redirect_url'] != null) {
      invoiceUrl = response['redirect_url'] as String;
    } else if (response['invoice_url'] != null) {
      invoiceUrl = response['invoice_url'] as String;
    }
    
    if (invoiceUrl != null) {
      print('💳 Opening Xendit invoice URL: $invoiceUrl');
      
      // Open payment URL
      await _openPaymentUrl(invoiceUrl);
      
      _isLoading = false;
      notifyListeners();
      return true;
    } else {
      print('⚠️ Response structure: $response');
      throw Exception('No invoice URL received from order creation. Please check backend response format.');
    }
  }

  Future<void> _openPaymentUrl(String url) async {
    try {
      final uri = Uri.parse(url);
      
      if (await canLaunchUrl(uri)) {
        await launchUrl(
          uri,
          mode: LaunchMode.externalApplication, // Open in browser
        );
      } else {
        throw Exception('Could not launch payment URL');
      }
    } catch (e) {
      print('❌ Error opening payment URL: $e');
      throw Exception('Failed to open payment page: $e');
    }
  }

  // Clear checkout data (call after successful payment)
  void clearCheckout() {
    _checkoutData = null;
    _error = null;
    notifyListeners();
  }

  // Handle payment result (call this when returning from payment)
  Future<void> handlePaymentResult({
    required bool success,
    String? transactionId,
    String? message,
  }) async {
    if (success) {
      print('✅ Payment successful: $transactionId');
      
      // Note: Cart clearing is now handled by backend after successful payment
      // The backend clears the cart when order is created and payment is confirmed via webhook
      print('✅ Order completed successfully');
      
      clearCheckout();
    } else {
      print('❌ Payment failed: $message');
      _error = message ?? 'Payment failed';
      notifyListeners();
    }
  }
}

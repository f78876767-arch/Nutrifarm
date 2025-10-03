import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart' show kDebugMode;
import '../models/product.dart';
import '../models/jnt_models.dart';
import '../models/banner.dart';

class ApiService {
  // Base URL for your Laravel backend API
  static const String baseUrl = 'http://127.0.0.1:8000/api';
  
  static const Map<String, String> headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  // TODO: Replace with actual auth token from user session
  static String? _authToken;
  
  static void setAuthToken(String token) {
    _authToken = token;
  }
  
  static void clearAuthToken() {
    _authToken = null;
  }
  
  static String? get authToken => _authToken;
  
  static Map<String, String> get _headersWithAuth {
    final headersWithAuth = Map<String, String>.from(headers);
    if (_authToken != null) {
      headersWithAuth['Authorization'] = 'Bearer $_authToken';
    }
    return headersWithAuth;
  }

  // ================= User Shipping Address (Backend) =================
  static Future<Map<String, dynamic>?> getUserShippingAddress() async {
    try {
      final resp = await http
          .get(Uri.parse('$baseUrl/shipping/address'), headers: _headersWithAuth)
          .timeout(const Duration(seconds: 20));
      if (resp.statusCode == 200) {
        final data = json.decode(resp.body);
        if (data is Map<String, dynamic>) {
          if (data['data'] is Map<String, dynamic>) return Map<String, dynamic>.from(data['data']);
          return Map<String, dynamic>.from(data);
        }
        return null;
      }
      throw ApiException('Failed to get shipping address: ${resp.statusCode}');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error get shipping address: $e');
    }
  }

  static Future<bool> saveUserShippingAddress({
    String? address,
    String? phone,
    required int provinceId,
    required String provinceName,
    required int cityId,
    required String cityName,
    String? postalCode,
    int? subdistrictId,
    String? subdistrictName,
  }) async {
    try {
      final body = json.encode({
        if (address != null) 'address': address,
        if (phone != null) 'phone': phone,
        'province_id': provinceId,
        'province_name': provinceName,
        'city_id': cityId,
        'city_name': cityName,
        if (postalCode != null) 'postal_code': postalCode,
        if (subdistrictId != null) 'subdistrict_id': subdistrictId,
        if (subdistrictName != null) 'subdistrict_name': subdistrictName,
      });
      final resp = await http
          .post(Uri.parse('$baseUrl/shipping/address'), headers: _headersWithAuth, body: body)
          .timeout(const Duration(seconds: 20));
      if (resp.statusCode == 200 || resp.statusCode == 201) {
        return true;
      }
      throw ApiException('Failed to save shipping address: ${resp.statusCode}');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error save shipping address: $e');
    }
  }

  // Get all products
  static Future<List<Product>> getProducts() async {
    try {
      print('🌐 Making API request to: $baseUrl/products');
      final response = await http.get(
        Uri.parse('$baseUrl/products'),
        headers: headers,
      ).timeout(const Duration(seconds: 10));

      print('📡 API Response Status: ${response.statusCode}');
      
      if (response.statusCode == 200) {
        print('✅ API Response received, parsing JSON...');
        final List<dynamic> jsonData = json.decode(response.body);
        print('📊 Raw products count: ${jsonData.length}');
        
        final products = jsonData.map((json) => Product.fromJson(json)).toList();
        print('✅ Products parsed successfully: ${products.length} items');
        return products;
      } else {
        print('❌ API Error - Status: ${response.statusCode}, Body: ${response.body}');
        throw ApiException('Failed to load products: ${response.statusCode}');
      }
    } catch (e) {
      print('💥 API Exception: $e');
      if (e is ApiException) rethrow;
      throw ApiException('Network error: ${e.toString()}');
    }
  }

  // Get single product by ID
  static Future<Product> getProduct(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/products/$id'),
        headers: headers,
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final Map<String, dynamic> jsonData = json.decode(response.body);
        return Product.fromJson(jsonData);
      } else {
        throw ApiException('Failed to load product: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error: ${e.toString()}');
    }
  }

  // Get products by category
  static Future<List<Product>> getProductsByCategory(String category) async {
    final products = await getProducts();
    if (category == 'All') return products;
    
    return products.where((product) => 
      product.categories.any((cat) => 
        cat.name.toLowerCase() == category.toLowerCase()
      )
    ).toList();
  }

  // Search products
  static Future<List<Product>> searchProducts(String query) async {
    final products = await getProducts();
    final lowerQuery = query.toLowerCase();
    
    return products.where((product) {
      return product.name.toLowerCase().contains(lowerQuery) ||
             (product.description?.toLowerCase().contains(lowerQuery) ?? false) ||
             product.categories.any((cat) => 
               cat.name.toLowerCase().contains(lowerQuery)
             );
    }).toList();
  }

  // Get all unique categories
  static Future<List<String>> getCategories() async {
    try {
      final products = await getProducts();
      final categorySet = <String>{'All'};
      
      for (final product in products) {
        for (final category in product.categories) {
          categorySet.add(category.name);
        }
      }
      
      return categorySet.toList();
    } catch (e) {
      // Return default categories if API fails
      return ['All', 'Beverages', 'Health Products', 'Natural Foods'];
    }
  }

  // Get featured products
  static Future<List<Product>> getFeaturedProducts() async {
    final products = await getProducts();
    return products.where((product) => 
      product.discountPrice != null || product.variants.isNotEmpty
    ).toList();
  }

  // Favorites API methods
  static Future<List<Product>> getFavorites() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/favorites'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final dynamic jsonResponse = json.decode(response.body);
        
        // Handle different API response formats
        if (jsonResponse is Map<String, dynamic> && jsonResponse['success'] == true) {
          // Format 1: {success: true, data: [{product: {...}}]}
          final List<dynamic> favoritesData = jsonResponse['data'];
          return favoritesData.map((favoriteItem) => 
            Product.fromJson(favoriteItem['product'])
          ).toList();
        } else if (jsonResponse is List<dynamic>) {
          // Format 2: Direct array of products [{id: 1, name: "..."}]
          return jsonResponse.map((productData) => 
            Product.fromJson(productData)
          ).toList();
        } else if (jsonResponse is Map<String, dynamic> && jsonResponse['data'] is List) {
          // Format 3: {data: [{id: 1, name: "..."}]} (direct products)
          final List<dynamic> productsData = jsonResponse['data'];
          return productsData.map((productData) => 
            Product.fromJson(productData)
          ).toList();
        } else {
          throw ApiException('Failed to get favorites: Unknown response format');
        }
      } else {
        throw ApiException('Failed to get favorites: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error getting favorites: ${e.toString()}');
    }
  }

  static Future<bool> addToFavorites(int productId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/favorites'),
        headers: _headersWithAuth,
        body: json.encode({'product_id': productId}),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200 || response.statusCode == 201) {
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        return jsonResponse['success'] == true;
      } else {
        throw ApiException('Failed to add to favorites: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error adding to favorites: ${e.toString()}');
    }
  }

  static Future<bool> removeFromFavorites(int productId) async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/favorites/$productId'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        return jsonResponse['success'] == true;
      } else {
        throw ApiException('Failed to remove from favorites: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error removing from favorites: ${e.toString()}');
    }
  }

  static Future<bool> toggleFavorite(int productId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/favorites/toggle'),
        headers: _headersWithAuth,
        body: json.encode({'product_id': productId}),
      ).timeout(const Duration(seconds: 10));

      // Accept both 200 and 201 as success codes
      if (response.statusCode == 200 || response.statusCode == 201) {
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        return jsonResponse['success'] == true || response.statusCode == 201;
      } else {
        throw ApiException('Failed to toggle favorite: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error toggling favorite: ${e.toString()}');
    }
  }

  static Future<bool> isFavorite(int productId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/favorites/check/$productId'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        if (jsonResponse['success'] == true) {
          return jsonResponse['data']['is_favorite'] == true;
        }
        return false;
      } else {
        throw ApiException('Failed to check favorite status: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error checking favorite: ${e.toString()}');
    }
  }

  // Get discounted products
  static Future<List<Product>> getDiscountedProducts() async {
    final products = await getProducts();
    return products.where((product) => product.discountPrice != null).toList();
  }

  // Cart API methods
  static Future<Map<String, dynamic>> getCart() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/cart'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw ApiException('Failed to get cart: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error getting cart: ${e.toString()}');
    }
  }

  // --- Cart (refactored to match provided backend spec) ---
  static Future<Map<String, dynamic>> addToCart(int productId, {int quantity = 1, int? variantId}) async {
    try {
      print('🔥 API DEBUG: addToCart called with productId: $productId, quantity: $quantity');
      print('🔥 API DEBUG: Base URL: $baseUrl');
      print('🔥 API DEBUG: Auth token present: ${_authToken != null}');
      
      final body = {
        'product_id': productId,
        'quantity': quantity,
      };
      if (variantId != null) body['variant_id'] = variantId;
      
      print('🔥 API DEBUG: Request body: $body');
      
      final response = await http.post(
        Uri.parse('$baseUrl/cart'),
        headers: _headersWithAuth,
        body: json.encode(body),
      ).timeout(const Duration(seconds: 10));
      
      print('🔥 API DEBUG: Response status: ${response.statusCode}');
      print('🔥 API DEBUG: Response body: ${response.body}');
      
      if (response.statusCode == 200 || response.statusCode == 201) {
        final dynamic jsonResponse = json.decode(response.body);
        if (jsonResponse is Map<String, dynamic>) {
          final data = jsonResponse['data'];
          if (data is Map<String, dynamic>) {
            if (!jsonResponse.containsKey('cart_item_id') && data['id'] != null) {
              jsonResponse['cart_item_id'] = data['id'];
              print('🔥 API DEBUG: Added cart_item_id from data.id: ${data['id']}');
            }
          }
        }
        return (jsonResponse is Map<String, dynamic>) ? jsonResponse : {'success': true};
      }
      throw ApiException('Failed to add to cart: ${response.statusCode}');
    } catch (e) {
      print('🔥 API DEBUG: Exception in addToCart: $e');
      if (e is ApiException) rethrow; throw ApiException('Network error adding to cart: $e');
    }
  }

  static Future<bool> updateCartQuantity(int cartItemId, int quantity) async {
    try {
      print('🔥 API DEBUG: updateCartQuantity called with cartItemId: $cartItemId, quantity: $quantity');
      
      // Try PUT first (more common for updates)
      var response = await http.put(
        Uri.parse('$baseUrl/cart/$cartItemId'),
        headers: _headersWithAuth,
        body: json.encode({'quantity': quantity}),
      ).timeout(const Duration(seconds: 10));
      
      print('🔥 API DEBUG: PUT response status: ${response.statusCode}');
      
      if (response.statusCode == 405) {
        // If PUT not allowed, try PATCH
        print('🔥 API DEBUG: PUT returned 405, trying PATCH...');
        response = await http.patch(
          Uri.parse('$baseUrl/cart/$cartItemId'),
          headers: _headersWithAuth,
          body: json.encode({'quantity': quantity}),
        ).timeout(const Duration(seconds: 10));
        print('🔥 API DEBUG: PATCH response status: ${response.statusCode}');
      }
      
      print('🔥 API DEBUG: Update response body: ${response.body}');
      
      if (response.statusCode == 200) {
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        return jsonResponse['success'] == true;
      }
      throw ApiException('Failed to update cart: ${response.statusCode}');
    } catch (e) { 
      print('🔥 API DEBUG: Exception in updateCartQuantity: $e');
      if (e is ApiException) rethrow; throw ApiException('Network error updating cart: $e'); 
    }
  }

  static Future<bool> removeFromCart(int cartItemId) async {
    try {
      print('🔥 API DEBUG: removeFromCart called with cartItemId: $cartItemId');
      
      final response = await http.delete(
        Uri.parse('$baseUrl/cart/$cartItemId'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 10));
      
      print('🔥 API DEBUG: Delete response status: ${response.statusCode}');
      print('🔥 API DEBUG: Delete response body: ${response.body}');
      
      if (response.statusCode == 200 || response.statusCode == 204) {
        if (response.statusCode == 204 || response.body.isEmpty) {
          return true; // No content response is success
        }
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        return jsonResponse['success'] == true;
      }
      throw ApiException('Failed to remove from cart: ${response.statusCode}');
    } catch (e) { 
      print('🔥 API DEBUG: Exception in removeFromCart: $e');
      if (e is ApiException) rethrow; throw ApiException('Network error removing from cart: $e'); 
    }
  }

  static Future<bool> clearCart() async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/cart'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 10));
      if (response.statusCode == 200) {
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        return jsonResponse['success'] == true;
      }
      throw ApiException('Failed to clear cart: ${response.statusCode}');
    } catch (e) { if (e is ApiException) rethrow; throw ApiException('Network error clearing cart: $e'); }
  }

  static Future<int> getCartCount() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/cart/count'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 10));
      if (response.statusCode == 200) {
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        return jsonResponse['data']?['count'] ?? 0;
      }
      throw ApiException('Failed to get cart count: ${response.statusCode}');
    } catch (e) { if (e is ApiException) rethrow; throw ApiException('Network error cart count: $e'); }
  }

  // Get segmented products (discounted & regular) with fallback
  static Future<Map<String, List<Product>>> getProductsSegmented() async {
    try {
      final uri = Uri.parse('$baseUrl/products-segmented');
      print('🌐 Fetching segmented products: $uri');
      final response = await http.get(uri, headers: headers).timeout(const Duration(seconds: 10));
      print('📡 Segmented Status: ${response.statusCode}');
      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        final List discountedRaw = (data['discounted'] as List?) ?? [];
        final List regularRaw = (data['regular'] as List?) ?? [];
        final discounted = discountedRaw.map<Product>((e) => _productFromSegmented(e)).toList();
        final regular = regularRaw.map<Product>((e) => _productFromSegmented(e)).toList();
        print('✅ Segmented loaded. discounted=${discounted.length} regular=${regular.length}');
        return {
          'discounted': discounted,
          'regular': regular,
        };
      } else {
        throw ApiException('Segmented failed code ${response.statusCode}');
      }
    } catch (e) {
      print('⚠️ Segmented fetch failed: $e. Fallback to /products');
      try {
        final fallbackUri = Uri.parse('$baseUrl/products');
        final resp = await http.get(fallbackUri, headers: headers).timeout(const Duration(seconds: 10));
        if (resp.statusCode == 200) {
          final List list = json.decode(resp.body);
          final discounted = <Product>[];
          final regular = <Product>[];
          for (final raw in list) {
            if (raw is Map<String, dynamic>) {
              final isDiscount = (raw['is_discount_active'] == true) || raw['discount_amount'] != null || raw['discount_price'] != null;
              final prod = _productFromFlexible(raw);
              if (isDiscount) {
                discounted.add(prod);
              } else {
                regular.add(prod);
              }
            }
          }
          print('✅ Fallback partitioned. discounted=${discounted.length} regular=${regular.length}');
          return {
            'discounted': discounted,
            'regular': regular,
          };
        } else {
          throw ApiException('Fallback /products failed code ${resp.statusCode}');
        }
      } catch (e2) {
        print('❌ Fallback also failed: $e2');
        rethrow;
      }
    }
  }

  // Sync local cart to backend before checkout
  static Future<bool> syncCartToBackend(List<Map<String, dynamic>> cartItems) async {
    try {
      print('🔄 Syncing ${cartItems.length} cart items to backend...');
      
      // Clear backend cart first
      await clearCart();
      
      // Add each item to backend cart
      for (final item in cartItems) {
        await addToCart(
          item['product_id'] as int,
          quantity: item['quantity'] as int,
          variantId: item['variant_id'] as int?,
        );
      }
      
      print('✅ Cart synchronized successfully');
      return true;
    } catch (e) {
      print('❌ Failed to sync cart: $e');
      return false;
    }
  }

  // Create Order with Xendit Payment (NEW - proper order flow)
  static Future<Map<String, dynamic>> createOrder({
    String shippingMethod = 'regular',
    double? shippingAmount,
    String paymentMethod = 'xendit',
    String? notes,
    String? deliveryAddress,
    List<Map<String, dynamic>>? orderItems, // Optional: pass items directly
  }) async {
    try {
      print('🛍️ Creating order with Xendit payment...');
      
      final requestBody = {
        'shipping_method': shippingMethod,
        if (shippingAmount != null) 'shipping_amount': shippingAmount,
        'payment_method': paymentMethod,
        if (notes != null && notes.isNotEmpty) 'notes': notes,
        if (deliveryAddress != null && deliveryAddress.isNotEmpty) 'delivery_address': deliveryAddress,
        if (orderItems != null && orderItems.isNotEmpty) 'items': orderItems,
      };
      
      print('📤 Order request body: $requestBody');
      
      final response = await http.post(
        Uri.parse('$baseUrl/orders'),
        headers: _headersWithAuth,
        body: json.encode(requestBody),
      ).timeout(const Duration(seconds: 30));

      print('📡 Order Response Status: ${response.statusCode}');
      print('📡 Order Response Body: ${response.body}');
      
      if (response.statusCode == 200 || response.statusCode == 201) {
        final responseData = json.decode(response.body);
        print('✅ Order created successfully with Xendit invoice');
        return responseData;
      } else {
        final errorData = json.decode(response.body);
        final errorMessage = errorData['message'] ?? errorData['error'] ?? 'Order creation failed';
        
        // Special handling for database column errors
        if (errorMessage.contains('Column not found') || 
            errorMessage.contains('Unknown column')) {
          print('🔧 Detected database column missing in backend');
          throw ApiException('Backend database missing required columns. Please check BACKEND_MIGRATION_FIX.md for solution.');
        }
        
        // Special handling for cart model errors
        if (errorMessage.contains('No query results for model') && 
            errorMessage.contains('Cart')) {
          print('🔧 Detected cart model issue in backend');
          throw ApiException('Backend cart system needs fixing. Cart model not found in database. Please check BACKEND_CART_MODEL_FIX.md for solution.');
        }
        
        print('❌ Order creation failed: $errorMessage');
        throw ApiException(errorMessage);
      }
    } catch (e) {
      print('❌ Error creating order: $e');
      if (e is ApiException) rethrow;
      throw ApiException('Failed to create order: ${e.toString()}');
    }
  }

  // Helper: safe double parsing for flexible numeric fields
  static double _toDouble(dynamic v) {
    if (v == null) return 0.0;
    if (v is num) return v.toDouble();
    return double.tryParse(v.toString()) ?? 0.0;
  }

  // Create Xendit payment invoice directly (fallback path)
  static Future<Map<String, dynamic>> createXenditPayment({
    required int amount,
    required String externalId,
    required String payerEmail,
    String? description,
  }) async {
    try {
      final body = {
        'amount': amount,
        'external_id': externalId,
        'payer_email': payerEmail,
        if (description != null && description.isNotEmpty) 'description': description,
      };

      final resp = await http
          .post(
            Uri.parse('$baseUrl/payments/xendit/create'),
            headers: _headersWithAuth,
            body: json.encode(body),
          )
          .timeout(const Duration(seconds: 30));

      if (resp.statusCode == 200 || resp.statusCode == 201) {
        return json.decode(resp.body) as Map<String, dynamic>;
      }

      final dynamic err = resp.body.isNotEmpty ? json.decode(resp.body) : null;
      final String msg = (err is Map<String, dynamic>)
          ? (err['message']?.toString() ?? err['error']?.toString() ?? 'Failed to create Xendit payment')
          : 'Failed to create Xendit payment: ${resp.statusCode}';
      throw ApiException(msg);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error creating Xendit payment: $e');
    }
  }

  // ================= RajaOngkir Integration =================
  // Disabled
  // static int originCityId = 369;
  // static Future<List<Map<String, dynamic>>> getRajaOngkirProvinces() async { /* removed */ }
  // static Future<List<Map<String, dynamic>>> getRajaOngkirCities(int provinceId) async { /* removed */ }
  // static Future<List<Map<String, dynamic>>> getRajaOngkirCost({...}) async { /* removed */ }
  // ==========================================================

  // Order Management API Methods
  
  // Get user's orders list
  static Future<dynamic> getOrders() async {
    try {
      print('🔄 Fetching user orders...');
      
      final response = await http.get(
        Uri.parse('$baseUrl/orders'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 30));

      print('📡 Orders Response Status: ${response.statusCode}');
      print('📡 Orders Response Body: ${response.body}');
      
      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);
        print('✅ Orders fetched successfully');
        return responseData;
      } else {
        final errorData = json.decode(response.body);
        final errorMessage = errorData['message'] ?? errorData['error'] ?? 'Failed to fetch orders';
        throw ApiException(errorMessage);
      }
    } catch (e) {
      print('❌ Error fetching orders: $e');
      throw ApiException('Failed to fetch orders: $e');
    }
  }

  // Get specific order detail
  static Future<Map<String, dynamic>> getOrderDetail(int orderId) async {
    try {
      print('🔄 Fetching order detail for ID: $orderId');
      
      final response = await http.get(
        Uri.parse('$baseUrl/orders/$orderId'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 30));

      print('📡 Order Detail Response Status: ${response.statusCode}');
      print('📡 Order Detail Response Body: ${response.body}');
      
      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);
        print('✅ Order detail fetched successfully');
        
        // Handle different response structures
        if (responseData is Map<String, dynamic>) {
          if (responseData['data'] != null) {
            return responseData['data'];
          }
          return responseData;
        }
        
        throw ApiException('Invalid order detail response format');
      } else {
        final errorData = json.decode(response.body);
        final errorMessage = errorData['message'] ?? errorData['error'] ?? 'Failed to fetch order detail';
        throw ApiException(errorMessage);
      }
    } catch (e) {
      print('❌ Error fetching order detail: $e');
      throw ApiException('Failed to fetch order detail: $e');
    }
  }

  // Get order invoice (returns PDF URL)
  static Future<Map<String, dynamic>> getOrderInvoice(int orderId) async {
    try {
      print('🔄 Fetching order invoice for ID: $orderId');
      
      final response = await http.get(
        Uri.parse('$baseUrl/orders/$orderId/invoice'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 30));

      print('📡 Order Invoice Response Status: ${response.statusCode}');
      
      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);
        print('✅ Order invoice fetched successfully');
        return responseData;
      } else {
        final errorData = json.decode(response.body);
        final errorMessage = errorData['message'] ?? errorData['error'] ?? 'Failed to fetch order invoice';
        throw ApiException(errorMessage);
      }
    } catch (e) {
      print('❌ Error fetching order invoice: $e');
      throw ApiException('Failed to fetch order invoice: $e');
    }
  }

  // Get order receipt (returns PDF URL and paid_at)
  static Future<Map<String, dynamic>> getOrderReceipt(int orderId) async {
    try {
      print('🔄 Fetching order receipt for ID: $orderId');
      
      final response = await http.get(
        Uri.parse('$baseUrl/orders/$orderId/receipt'),
        headers: _headersWithAuth,
      ).timeout(const Duration(seconds: 30));

      print('📡 Order Receipt Response Status: ${response.statusCode}');
      
      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);
        print('✅ Order receipt fetched successfully');
        return responseData;
      } else {
        final errorData = json.decode(response.body);
        final errorMessage = errorData['message'] ?? errorData['error'] ?? 'Failed to fetch order receipt';
        throw ApiException(errorMessage);
      }
    } catch (e) {
      print('❌ Error fetching order receipt: $e');
      throw ApiException('Failed to fetch order receipt: $e');
    }
  }

  static Product _productFromSegmented(dynamic raw) {
    final map = raw as Map<String, dynamic>;
    final double price = _toDouble(map['price']);
    final bool isDiscount = map['is_discount_active'] == true && map['discount_amount'] != null;
    double? discountPrice;
    if (isDiscount) {
      if (map['effective_price'] != null) {
        discountPrice = _toDouble(map['effective_price']);
      } else if (map['discount_amount'] != null) {
        discountPrice = (price - _toDouble(map['discount_amount'])).clamp(0, price);
      }
    }

    // Parse categories if present
    List<Category> categories = const [];
    final cats = map['categories'] ?? map['category_list'] ?? map['category_names'];
    if (cats is List) {
      if (cats.isNotEmpty) {
        if (cats.first is Map<String, dynamic>) {
          categories = cats
              .whereType<Map<String, dynamic>>()
              .map((c) => Category.fromJson(c))
              .toList();
        } else {
          categories = cats
              .map((c) => Category(
                    id: 0,
                    name: c.toString(),
                    createdAt: DateTime.now(),
                    updatedAt: DateTime.now(),
                  ))
              .toList();
        }
      }
    } else if (cats is String && cats.isNotEmpty) {
      categories = [Category(id: 0, name: cats, createdAt: DateTime.now(), updatedAt: DateTime.now())];
    }

    return Product(
      id: map['id'] ?? 0,
      name: map['name']?.toString() ?? 'Unknown',
      description: null,
      price: price,
      discountPrice: discountPrice,
      stock: map['stock_quantity'] ?? map['stock'] ?? 0,
      active: true,
      image: map['image_url']?.toString() ?? map['image']?.toString(),
      createdAt: DateTime.now(),
      updatedAt: DateTime.now(),
      categories: categories,
      variants: const [],
    );
  }

  static Product _productFromFlexible(Map<String, dynamic> map) {
    try {
      if (map.containsKey('created_at') && map.containsKey('updated_at')) {
        return Product.fromJson(map);
      }
    } catch (_) {}
    final double price = _toDouble(map['price']);
    final double? discountPrice = map['discount_price'] != null
        ? _toDouble(map['discount_price'])
        : (map['effective_price'] != null ? _toDouble(map['effective_price']) : null);

    List<Category> categories = const [];
    final cats = map['categories'] ?? map['category_list'] ?? map['category_names'];
    if (cats is List) {
      if (cats.isNotEmpty) {
        if (cats.first is Map<String, dynamic>) {
          categories = cats
              .whereType<Map<String, dynamic>>()
              .map((c) => Category.fromJson(c))
              .toList();
        } else {
          categories = cats
              .map((c) => Category(
                    id: 0,
                    name: c.toString(),
                    createdAt: DateTime.now(),
                    updatedAt: DateTime.now(),
                  ))
              .toList();
        }
      }
    } else if (cats is String && cats.isNotEmpty) {
      categories = [Category(id: 0, name: cats, createdAt: DateTime.now(), updatedAt: DateTime.now())];
    }

    return Product(
      id: map['id'] ?? 0,
      name: map['name']?.toString() ?? 'Unknown',
      description: map['description']?.toString(),
      price: price,
      discountPrice: discountPrice,
      stock: map['stock_quantity'] ?? map['stock'] ?? 0,
      active: map['is_active'] ?? true,
      image: map['image_path']?.toString() ?? map['image_url']?.toString() ?? map['image']?.toString(),
      createdAt: DateTime.tryParse(map['created_at']?.toString() ?? '') ?? DateTime.now(),
      updatedAt: DateTime.tryParse(map['updated_at']?.toString() ?? '') ?? DateTime.now(),
      categories: categories,
      variants: const [],
    );
  }

  // ================= J&T (JNT) Shipping Integration =================
  static Future<List<JntTariffResult>> jntTariff(Map<String, dynamic> payload) async {
    try {
      final resp = await http
          .post(
            Uri.parse('$baseUrl/shipping/jnt/tariff'),
            headers: const {
              'Content-Type': 'application/x-www-form-urlencoded',
              'Accept': 'application/json',
            },
            body: payload.map((k, v) => MapEntry(k, v.toString())),
          )
          .timeout(const Duration(seconds: 20));
      if (kDebugMode) {
        // ignore: avoid_print
        print('🧾 JNT tariff status=${resp.statusCode} body=${resp.body}');
      }
      if (resp.statusCode == 200) {
        if (resp.body.isEmpty) return const [];
        final dynamic data = json.decode(resp.body);

        List list = const [];
        if (data is List) {
          list = List.from(data);
        } else if (data is Map) {
          // Try several common shapes
          final d = data['data'];
          final r = data['result'];
          if (d is List) {
            list = List.from(d);
          } else if (d is Map) {
            if (d['rates'] is List) list = List.from(d['rates']);
            else if (d['services'] is List) list = List.from(d['services']);
            else if (d['options'] is List) list = List.from(d['options']);
          } else if (data['rates'] is List) {
            list = List.from(data['rates']);
          } else if (data['services'] is List) {
            list = List.from(data['services']);
          } else if (data['options'] is List) {
            list = List.from(data['options']);
          } else if (r is List) {
            list = List.from(r);
          }
        }

        return List<JntTariffResult>.from(list.map((e) => JntTariffResult.fromJson(e)));
      }
      throw ApiException('JNT Tariff failed: ${resp.statusCode}');
    } catch (e) {
      if (kDebugMode) print('❌ JNT Tariff error: $e');
      if (e is ApiException) rethrow; throw ApiException('Network error JNT Tariff: $e');
    }
  }

  static Future<List<JntTrackEvent>> jntTrack(Map<String, dynamic> payload) async {
    try {
      final resp = await http
          .post(Uri.parse('$baseUrl/shipping/jnt/track'), headers: const {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
          }, body: payload.map((k, v) => MapEntry(k, v.toString())))
          .timeout(const Duration(seconds: 20));
      if (resp.statusCode == 200) {
        final dynamic data = json.decode(resp.body);
        final List list = (data is Map && data['data'] is List)
            ? List.from(data['data'] as List)
            : (data is List ? List.from(data) : const []);
        return List<JntTrackEvent>.from(list.map((e) => JntTrackEvent.fromJson(e)));
      }
      throw ApiException('JNT Track failed: ${resp.statusCode}');
    } catch (e) {
      if (kDebugMode) print('❌ JNT Track error: $e');
      if (e is ApiException) rethrow; throw ApiException('Network error JNT Track: $e');
    }
  }

  static Future<JntCreateResult> jntCreateOrder(Map<String, dynamic> body) async {
    try {
      final resp = await http
          .post(Uri.parse('$baseUrl/shipping/jnt/order/create'), headers: _headersWithAuth, body: json.encode(body))
          .timeout(const Duration(seconds: 30));
      if (resp.statusCode == 200 || resp.statusCode == 201) {
        final dynamic data = json.decode(resp.body);
        final Map<String, dynamic> payload = (data is Map && data['data'] is Map)
            ? Map<String, dynamic>.from(data['data'] as Map)
            : (data is Map)
                ? Map<String, dynamic>.from(data as Map)
                : <String, dynamic>{};
        return JntCreateResult.fromJson(payload);
      }
      throw ApiException('JNT Create failed: ${resp.statusCode}');
    } catch (e) {
      if (kDebugMode) print('❌ JNT Create error: $e');
      if (e is ApiException) rethrow; throw ApiException('Network error JNT Create: $e');
    }
  }

  static Future<bool> jntCancelOrder(Map<String, dynamic> body) async {
    try {
      final resp = await http
          .post(Uri.parse('$baseUrl/shipping/jnt/order/cancel'), headers: _headersWithAuth, body: json.encode(body))
          .timeout(const Duration(seconds: 20));
      if (resp.statusCode == 200) {
        return true;
      }
      throw ApiException('JNT Cancel failed: ${resp.statusCode}');
    } catch (e) {
      if (kDebugMode) print('❌ JNT Cancel error: $e');
      if (e is ApiException) rethrow; throw ApiException('Network error JNT Cancel: $e');
    }
  }

  static Future<bool> updateOrderResi(int orderId, {required String resi, String? status}) async {
    try {
      final payload = json.encode({'resi': resi, if (status != null) 'status': status});
      final resp = await http
          .patch(Uri.parse('$baseUrl/orders/$orderId'), headers: _headersWithAuth, body: payload)
          .timeout(const Duration(seconds: 20));
      if (resp.statusCode == 200) return true;
      throw ApiException('Update order resi failed: ${resp.statusCode}');
    } catch (e) {
      if (kDebugMode) print('❌ Update order resi error: $e');
      if (e is ApiException) rethrow; throw ApiException('Network error update resi: $e');
    }
  }

  // Get banners
  static Future<List<BannerModel>> getBanners() async {
    try {
      final resp = await http.get(Uri.parse('$baseUrl/banners'), headers: headers).timeout(const Duration(seconds: 10));
      if (resp.statusCode != 200) throw ApiException('Failed banners: ${resp.statusCode}');
      final body = json.decode(resp.body);
      List dataList;
      if (body is List) {
        dataList = body;
      } else if (body is Map && body['data'] is List) {
        dataList = body['data'];
      } else {
        return [];
      }
      return dataList.map((e) => BannerModel.fromJson(e as Map<String,dynamic>)).toList();
    } catch (e) {
      if (kDebugMode) {
        print('Banner fetch error: $e');
      }
      return [];
    }
  }
}

class ApiException implements Exception {
  final String message;
  ApiException(this.message);
  
  @override
  String toString() => 'ApiException: $message';
}

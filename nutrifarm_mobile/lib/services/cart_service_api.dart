import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart';
import '../models/product.dart';
import 'api_service.dart';

class CartItemApi {
  final int id;
  final Product product;
  final int quantity;
  final DateTime createdAt;
  final DateTime updatedAt;

  CartItemApi({
    required this.id,
    required this.product,
    required this.quantity,
    required this.createdAt,
    required this.updatedAt,
  });

  double get totalPrice => product.effectivePrice * quantity;
  double get totalOriginalPrice => product.price * quantity;
  double get totalSavings => totalOriginalPrice - totalPrice;

  factory CartItemApi.fromJson(Map<String, dynamic> json) {
    return CartItemApi(
      id: json['id'],
      product: Product.fromJson(json['product']),
      quantity: json['quantity'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'product': product,
    'quantity': quantity,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
  };
}

class CartServiceApi extends ChangeNotifier {
  static final CartServiceApi _instance = CartServiceApi._internal();
  factory CartServiceApi() => _instance;
  CartServiceApi._internal();

  List<CartItemApi> _items = [];
  bool _isLoading = false;
  
  List<CartItemApi> get items => List.unmodifiable(_items);
  bool get isLoading => _isLoading;
  int get itemCount => _items.length;
  int get totalQuantity => _items.fold(0, (sum, item) => sum + item.quantity);
  
  double get subtotal => _items.fold(0.0, (sum, item) => sum + item.totalPrice);
  double get originalTotal => _items.fold(0.0, (sum, item) => sum + item.totalOriginalPrice);
  double get totalSavings => originalTotal - subtotal;
  
  bool get isEmpty => _items.isEmpty;
  bool get isNotEmpty => _items.isNotEmpty;

  // Load cart from backend
  Future<void> loadCart() async {
    _isLoading = true;
    notifyListeners();
    
    try {
      final response = await http.get(
        Uri.parse('${ApiService.baseUrl}/cart'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${ApiService.authToken}',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        final List<dynamic> cartData = data['cart_items'] ?? data['data'] ?? [];
        _items = cartData.map((item) => CartItemApi.fromJson(item)).toList();
      } else {
        throw Exception('Failed to load cart: ${response.statusCode}');
      }
    } catch (e) {
      print('Error loading cart: $e');
      throw e;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Add product to cart
  Future<void> addToCart(int productId, {int quantity = 1}) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiService.baseUrl}/cart'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${ApiService.authToken}',
        },
        body: json.encode({
          'product_id': productId,
          'quantity': quantity,
        }),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200 || response.statusCode == 201) {
        await loadCart(); // Reload cart to get updated state
      } else {
        throw Exception('Failed to add to cart: ${response.statusCode}');
      }
    } catch (e) {
      print('Error adding to cart: $e');
      throw e;
    }
  }

  // Update cart item quantity
  Future<void> updateQuantity(int cartItemId, int quantity) async {
    if (quantity <= 0) {
      await removeFromCart(cartItemId);
      return;
    }

    try {
      final response = await http.put(
        Uri.parse('${ApiService.baseUrl}/cart/$cartItemId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${ApiService.authToken}',
        },
        body: json.encode({
          'quantity': quantity,
        }),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        await loadCart(); // Reload cart to get updated state
      } else {
        throw Exception('Failed to update cart: ${response.statusCode}');
      }
    } catch (e) {
      print('Error updating cart quantity: $e');
      throw e;
    }
  }

  // Remove item from cart
  Future<void> removeFromCart(int cartItemId) async {
    try {
      final response = await http.delete(
        Uri.parse('${ApiService.baseUrl}/cart/$cartItemId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${ApiService.authToken}',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        await loadCart(); // Reload cart to get updated state
      } else {
        throw Exception('Failed to remove from cart: ${response.statusCode}');
      }
    } catch (e) {
      print('Error removing from cart: $e');
      throw e;
    }
  }

  // Clear entire cart
  Future<void> clearCart() async {
    try {
      final response = await http.delete(
        Uri.parse('${ApiService.baseUrl}/cart/clear'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${ApiService.authToken}',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        _items.clear();
        notifyListeners();
      } else {
        throw Exception('Failed to clear cart: ${response.statusCode}');
      }
    } catch (e) {
      print('Error clearing cart: $e');
      throw e;
    }
  }

  // Check if product is in cart
  bool isInCart(int productId) {
    return _items.any((item) => item.product.id == productId);
  }

  // Get cart item for specific product
  CartItemApi? getCartItem(int productId) {
    try {
      return _items.firstWhere((item) => item.product.id == productId);
    } catch (e) {
      return null;
    }
  }

  // Get quantity of specific product in cart
  int getQuantity(int productId) {
    final item = getCartItem(productId);
    return item?.quantity ?? 0;
  }
}

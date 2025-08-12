import 'package:flutter/foundation.dart';
import '../models/product.dart';

class CartItem {
  final String id;
  final Product product;
  int quantity;
  final DateTime addedAt;

  CartItem({
    required this.id,
    required this.product,
    this.quantity = 1,
    DateTime? addedAt,
  }) : addedAt = addedAt ?? DateTime.now();

  double get totalPrice => product.discountPrice != null 
      ? product.discountPrice! * quantity 
      : product.price * quantity;
  double get totalOriginalPrice => product.price * quantity;
  double get totalSavings => totalOriginalPrice - totalPrice;
}

class CartService extends ChangeNotifier {
  static final CartService _instance = CartService._internal();
  factory CartService() => _instance;
  CartService._internal();

  final List<CartItem> _items = [];
  
  List<CartItem> get items => List.unmodifiable(_items);
  int get itemCount => _items.length;
  int get totalQuantity => _items.fold(0, (sum, item) => sum + item.quantity);
  
  double get subtotal => _items.fold(0.0, (sum, item) => sum + item.totalPrice);
  double get totalSavings => _items.fold(0.0, (sum, item) => sum + item.totalSavings);
  
  bool isInCart(int productId) {
    return _items.any((item) => item.product.id == productId);
  }
  
  CartItem? getCartItem(int productId) {
    try {
      return _items.firstWhere((item) => item.product.id == productId);
    } catch (e) {
      return null;
    }
  }
  
  void addToCart(Product product, {int quantity = 1}) {
    final existingIndex = _items.indexWhere((item) => item.product.id == product.id);
    
    if (existingIndex >= 0) {
      // Update quantity if product already in cart
      final newQuantity = _items[existingIndex].quantity + quantity;
      if (newQuantity <= product.stock) {
        _items[existingIndex].quantity = newQuantity;
      }
    } else {
      // Add new item to cart
      if (quantity <= product.stock) {
        _items.add(CartItem(
          id: DateTime.now().millisecondsSinceEpoch.toString(),
          product: product,
          quantity: quantity,
        ));
      }
    }
    notifyListeners();
  }
  
  void removeFromCart(int productId) {
    _items.removeWhere((item) => item.product.id == productId);
    notifyListeners();
  }
  
  void updateQuantity(int productId, int newQuantity) {
    if (newQuantity <= 0) {
      removeFromCart(productId);
      return;
    }
    
    final itemIndex = _items.indexWhere((item) => item.product.id == productId);
    if (itemIndex >= 0) {
      final maxQuantity = _items[itemIndex].product.stock;
      _items[itemIndex].quantity = newQuantity.clamp(1, maxQuantity);
      notifyListeners();
    }
  }
  
  void clearCart() {
    _items.clear();
    notifyListeners();
  }
  
  void increaseQuantity(int productId) {
    final item = getCartItem(productId);
    if (item != null && item.quantity < item.product.stock) {
      updateQuantity(productId, item.quantity + 1);
    }
  }
  
  void decreaseQuantity(int productId) {
    final item = getCartItem(productId);
    if (item != null) {
      updateQuantity(productId, item.quantity - 1);
    }
  }
}

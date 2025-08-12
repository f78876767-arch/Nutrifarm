import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'cart_service.dart';

enum OrderStatus {
  pending,
  confirmed,
  processing,
  shipped,
  delivered,
  cancelled
}

class OrderItem {
  final int productId;
  final String productName;
  final String productImageUrl;
  final double price;
  final int quantity;
  final double totalPrice;

  OrderItem({
    required this.productId,
    required this.productName,
    required this.productImageUrl,
    required this.price,
    required this.quantity,
    required this.totalPrice,
  });

  Map<String, dynamic> toJson() => {
    'productId': productId,
    'productName': productName,
    'productImageUrl': productImageUrl,
    'price': price,
    'quantity': quantity,
    'totalPrice': totalPrice,
  };

  static OrderItem fromJson(Map<String, dynamic> json) => OrderItem(
    productId: json['productId'],
    productName: json['productName'],
    productImageUrl: json['productImageUrl'],
    price: json['price'],
    quantity: json['quantity'],
    totalPrice: json['totalPrice'],
  );
}

class Order {
  final String id;
  final List<OrderItem> items;
  final double subtotal;
  final double deliveryFee;
  final double totalAmount;
  final OrderStatus status;
  final DateTime createdAt;
  final DateTime? deliveredAt;
  final String deliveryAddress;
  final String? trackingNumber;
  final String paymentMethod;

  Order({
    required this.id,
    required this.items,
    required this.subtotal,
    required this.deliveryFee,
    required this.totalAmount,
    required this.status,
    required this.createdAt,
    this.deliveredAt,
    required this.deliveryAddress,
    this.trackingNumber,
    required this.paymentMethod,
  });

  Map<String, dynamic> toJson() => {
    'id': id,
    'items': items.map((item) => item.toJson()).toList(),
    'subtotal': subtotal,
    'deliveryFee': deliveryFee,
    'totalAmount': totalAmount,
    'status': status.name,
    'createdAt': createdAt.toIso8601String(),
    'deliveredAt': deliveredAt?.toIso8601String(),
    'deliveryAddress': deliveryAddress,
    'trackingNumber': trackingNumber,
    'paymentMethod': paymentMethod,
  };

  static Order fromJson(Map<String, dynamic> json) => Order(
    id: json['id'],
    items: (json['items'] as List).map((item) => OrderItem.fromJson(item)).toList(),
    subtotal: json['subtotal'],
    deliveryFee: json['deliveryFee'],
    totalAmount: json['totalAmount'],
    status: OrderStatus.values.firstWhere((s) => s.name == json['status']),
    createdAt: DateTime.parse(json['createdAt']),
    deliveredAt: json['deliveredAt'] != null ? DateTime.parse(json['deliveredAt']) : null,
    deliveryAddress: json['deliveryAddress'],
    trackingNumber: json['trackingNumber'],
    paymentMethod: json['paymentMethod'],
  );

  Order copyWith({
    OrderStatus? status,
    DateTime? deliveredAt,
    String? trackingNumber,
  }) => Order(
    id: id,
    items: items,
    subtotal: subtotal,
    deliveryFee: deliveryFee,
    totalAmount: totalAmount,
    status: status ?? this.status,
    createdAt: createdAt,
    deliveredAt: deliveredAt ?? this.deliveredAt,
    deliveryAddress: deliveryAddress,
    trackingNumber: trackingNumber ?? this.trackingNumber,
    paymentMethod: paymentMethod,
  );
}

class OrderService extends ChangeNotifier {
  static final OrderService _instance = OrderService._internal();
  factory OrderService() => _instance;
  OrderService._internal();

  final List<Order> _orders = [];
  SharedPreferences? _prefs;
  
  List<Order> get orders => List.unmodifiable(_orders);
  List<Order> get activeOrders => _orders.where((order) => 
    order.status != OrderStatus.delivered && order.status != OrderStatus.cancelled).toList();
  List<Order> get completedOrders => _orders.where((order) => 
    order.status == OrderStatus.delivered).toList();

  Future<void> initialize() async {
    _prefs = await SharedPreferences.getInstance();
    final ordersData = _prefs?.getStringList('orders') ?? [];
    _orders.clear();
    for (final orderJson in ordersData) {
      try {
        _orders.add(Order.fromJson(json.decode(orderJson)));
      } catch (e) {
        print('Error loading order: $e');
      }
    }
    notifyListeners();
  }

  Future<String> createOrder({
    required String deliveryAddress,
    required String paymentMethod,
    double deliveryFee = 15000,
  }) async {
    final cartService = CartService();
    if (cartService.items.isEmpty) {
      throw Exception('Cart is empty');
    }

    final orderId = 'ORD${DateTime.now().millisecondsSinceEpoch}';
    final orderItems = cartService.items.map((cartItem) => OrderItem(
      productId: cartItem.product.id,
      productName: cartItem.product.name,
      productImageUrl: cartItem.product.imageUrl,
      price: cartItem.product.price,
      quantity: cartItem.quantity,
      totalPrice: cartItem.totalPrice,
    )).toList();

    final order = Order(
      id: orderId,
      items: orderItems,
      subtotal: cartService.subtotal,
      deliveryFee: deliveryFee,
      totalAmount: cartService.subtotal + deliveryFee,
      status: OrderStatus.pending,
      createdAt: DateTime.now(),
      deliveryAddress: deliveryAddress,
      paymentMethod: paymentMethod,
      trackingNumber: 'TRK${DateTime.now().millisecondsSinceEpoch}',
    );

    _orders.insert(0, order);
    await _saveOrders();
    
    // Clear cart after successful order
    cartService.clearCart();
    
    // Simulate order processing
    _simulateOrderProgress(orderId);
    
    notifyListeners();
    return orderId;
  }

  void _simulateOrderProgress(String orderId) {
    // Simulate order status changes
    Future.delayed(const Duration(seconds: 30), () {
      updateOrderStatus(orderId, OrderStatus.confirmed);
    });
    
    Future.delayed(const Duration(minutes: 2), () {
      updateOrderStatus(orderId, OrderStatus.processing);
    });
    
    Future.delayed(const Duration(minutes: 5), () {
      updateOrderStatus(orderId, OrderStatus.shipped);
    });
  }

  void updateOrderStatus(String orderId, OrderStatus newStatus) {
    final orderIndex = _orders.indexWhere((order) => order.id == orderId);
    if (orderIndex >= 0) {
      _orders[orderIndex] = _orders[orderIndex].copyWith(
        status: newStatus,
        deliveredAt: newStatus == OrderStatus.delivered ? DateTime.now() : null,
      );
      _saveOrders();
      notifyListeners();
    }
  }

  void cancelOrder(String orderId) {
    updateOrderStatus(orderId, OrderStatus.cancelled);
  }

  Order? getOrder(String orderId) {
    try {
      return _orders.firstWhere((order) => order.id == orderId);
    } catch (e) {
      return null;
    }
  }

  Future<void> _saveOrders() async {
    final ordersJson = _orders.map((order) => json.encode(order.toJson())).toList();
    await _prefs?.setStringList('orders', ordersJson);
  }

  String getOrderStatusText(OrderStatus status) {
    switch (status) {
      case OrderStatus.pending:
        return 'Menunggu Konfirmasi';
      case OrderStatus.confirmed:
        return 'Dikonfirmasi';
      case OrderStatus.processing:
        return 'Sedang Diproses';
      case OrderStatus.shipped:
        return 'Dalam Pengiriman';
      case OrderStatus.delivered:
        return 'Diterima';
      case OrderStatus.cancelled:
        return 'Dibatalkan';
    }
  }
}

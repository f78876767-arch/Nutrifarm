import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart';
import '../models/product.dart';
import 'api_service.dart';

enum OrderStatusApi {
  pending,
  confirmed,
  processing,
  shipped,
  delivered,
  cancelled,
  refunded,
}

class OrderItemApi {
  final int id;
  final int productId;
  final String productName;
  final String? productImageUrl;
  final double price;
  final int quantity;
  final double totalPrice;
  final DateTime createdAt;
  final DateTime updatedAt;

  OrderItemApi({
    required this.id,
    required this.productId,
    required this.productName,
    this.productImageUrl,
    required this.price,
    required this.quantity,
    required this.totalPrice,
    required this.createdAt,
    required this.updatedAt,
  });

  factory OrderItemApi.fromJson(Map<String, dynamic> json) {
    return OrderItemApi(
      id: json['id'],
      productId: json['product_id'],
      productName: json['product_name'],
      productImageUrl: json['product_image_url'],
      price: double.parse(json['price'].toString()),
      quantity: json['quantity'],
      totalPrice: double.parse(json['total_price'].toString()),
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'product_id': productId,
    'product_name': productName,
    'product_image_url': productImageUrl,
    'price': price,
    'quantity': quantity,
    'total_price': totalPrice,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
  };
}

class OrderApi {
  final String id;
  final List<OrderItemApi> items;
  final double subtotal;
  final double deliveryFee;
  final double totalAmount;
  final String deliveryAddress;
  final String paymentMethod;
  final OrderStatusApi status;
  final DateTime createdAt;
  final DateTime updatedAt;
  final DateTime? deliveredAt;

  OrderApi({
    required this.id,
    required this.items,
    required this.subtotal,
    required this.deliveryFee,
    required this.totalAmount,
    required this.deliveryAddress,
    required this.paymentMethod,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
    this.deliveredAt,
  });

  factory OrderApi.fromJson(Map<String, dynamic> json) {
    return OrderApi(
      id: json['id'].toString(),
      items: (json['items'] as List)
          .map((item) => OrderItemApi.fromJson(item))
          .toList(),
      subtotal: double.parse(json['subtotal'].toString()),
      deliveryFee: double.parse(json['delivery_fee'].toString()),
      totalAmount: double.parse(json['total_amount'].toString()),
      deliveryAddress: json['delivery_address'],
      paymentMethod: json['payment_method'],
      status: OrderStatusApi.values.firstWhere(
        (s) => s.name == json['status'], 
        orElse: () => OrderStatusApi.pending
      ),
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      deliveredAt: json['delivered_at'] != null 
          ? DateTime.parse(json['delivered_at'])
          : null,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'items': items.map((item) => item.toJson()).toList(),
    'subtotal': subtotal,
    'delivery_fee': deliveryFee,
    'total_amount': totalAmount,
    'delivery_address': deliveryAddress,
    'payment_method': paymentMethod,
    'status': status.name,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
    'delivered_at': deliveredAt?.toIso8601String(),
  };

  // Formatted prices
  String get formattedSubtotal => 'Rp ${subtotal.toStringAsFixed(0).replaceAllMapped(
    RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
    (Match m) => '${m[1]}.'
  )}';
  
  String get formattedDeliveryFee => 'Rp ${deliveryFee.toStringAsFixed(0).replaceAllMapped(
    RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
    (Match m) => '${m[1]}.'
  )}';
  
  String get formattedTotalAmount => 'Rp ${totalAmount.toStringAsFixed(0).replaceAllMapped(
    RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
    (Match m) => '${m[1]}.'
  )}';

  // Status helpers
  String get statusDisplayName {
    switch (status) {
      case OrderStatusApi.pending:
        return 'Pending';
      case OrderStatusApi.confirmed:
        return 'Confirmed';
      case OrderStatusApi.processing:
        return 'Processing';
      case OrderStatusApi.shipped:
        return 'Shipped';
      case OrderStatusApi.delivered:
        return 'Delivered';
      case OrderStatusApi.cancelled:
        return 'Cancelled';
      case OrderStatusApi.refunded:
        return 'Refunded';
    }
  }

  bool get isActive => status != OrderStatusApi.delivered && 
                      status != OrderStatusApi.cancelled && 
                      status != OrderStatusApi.refunded;
  
  bool get isCompleted => status == OrderStatusApi.delivered;
  bool get isCancelled => status == OrderStatusApi.cancelled;
}

class OrderServiceApi extends ChangeNotifier {
  static final OrderServiceApi _instance = OrderServiceApi._internal();
  factory OrderServiceApi() => _instance;
  OrderServiceApi._internal();

  List<OrderApi> _orders = [];
  bool _isLoading = false;
  
  List<OrderApi> get orders => List.unmodifiable(_orders);
  List<OrderApi> get activeOrders => _orders.where((order) => order.isActive).toList();
  List<OrderApi> get completedOrders => _orders.where((order) => order.isCompleted).toList();
  bool get isLoading => _isLoading;

  // Load all orders from backend
  Future<void> loadOrders() async {
    _isLoading = true;
    notifyListeners();
    
    try {
      final response = await http.get(
        Uri.parse('${ApiService.baseUrl}/orders'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${ApiService.authToken}',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        final List<dynamic> ordersData = data['orders'] ?? data['data'] ?? [];
        _orders = ordersData.map((order) => OrderApi.fromJson(order)).toList();
        
        // Sort by creation date (newest first)
        _orders.sort((a, b) => b.createdAt.compareTo(a.createdAt));
      } else {
        throw Exception('Failed to load orders: ${response.statusCode}');
      }
    } catch (e) {
      print('Error loading orders: $e');
      throw e;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Create new order
  Future<String> createOrder({
    required String deliveryAddress,
    required String paymentMethod,
    double deliveryFee = 15000,
    String? notes,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiService.baseUrl}/orders'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${ApiService.authToken}',
        },
        body: json.encode({
          'delivery_address': deliveryAddress,
          'payment_method': paymentMethod,
          'delivery_fee': deliveryFee,
          'notes': notes,
        }),
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200 || response.statusCode == 201) {
        final Map<String, dynamic> data = json.decode(response.body);
        
        // Reload orders to get the latest state
        await loadOrders();
        
        return data['order_id'] ?? data['id']?.toString() ?? 'unknown';
      } else {
        final errorData = json.decode(response.body);
        throw Exception(errorData['message'] ?? 'Failed to create order: ${response.statusCode}');
      }
    } catch (e) {
      print('Error creating order: $e');
      throw e;
    }
  }

  // Get specific order by ID
  Future<OrderApi?> getOrder(String orderId) async {
    try {
      final response = await http.get(
        Uri.parse('${ApiService.baseUrl}/orders/$orderId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${ApiService.authToken}',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        return OrderApi.fromJson(data['order'] ?? data);
      } else if (response.statusCode == 404) {
        return null; // Order not found
      } else {
        throw Exception('Failed to get order: ${response.statusCode}');
      }
    } catch (e) {
      print('Error getting order: $e');
      throw e;
    }
  }

  // Cancel order
  Future<void> cancelOrder(String orderId) async {
    try {
      final response = await http.put(
        Uri.parse('${ApiService.baseUrl}/orders/$orderId/cancel'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${ApiService.authToken}',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        await loadOrders(); // Reload to get updated status
      } else {
        final errorData = json.decode(response.body);
        throw Exception(errorData['message'] ?? 'Failed to cancel order: ${response.statusCode}');
      }
    } catch (e) {
      print('Error cancelling order: $e');
      throw e;
    }
  }

  // Track order (get updated status)
  Future<void> trackOrder(String orderId) async {
    final order = await getOrder(orderId);
    if (order != null) {
      // Update the order in our list
      final index = _orders.indexWhere((o) => o.id == orderId);
      if (index >= 0) {
        _orders[index] = order;
        notifyListeners();
      }
    }
  }
}

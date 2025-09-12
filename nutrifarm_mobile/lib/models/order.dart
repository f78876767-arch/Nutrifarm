import '../models/product.dart';

class Order {
  final int id;
  final String? externalId;
  final double total;
  final String status;
  final String paymentStatus;
  final DateTime? paidAt;
  final DateTime createdAt;
  final String? xenditInvoiceUrl;
  final String? invoicePdfUrl;
  final String? receiptPdfUrl;
  final String? resi; // AWB / tracking number
  final List<OrderProduct> orderProducts;

  Order({
    required this.id,
    this.externalId,
    required this.total,
    required this.status,
    required this.paymentStatus,
    this.paidAt,
    required this.createdAt,
    this.xenditInvoiceUrl,
    this.invoicePdfUrl,
    this.receiptPdfUrl,
    this.resi,
    required this.orderProducts,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      externalId: json['external_id'],
      total: (json['total'] is String) 
          ? double.parse(json['total']) 
          : (json['total'] as num).toDouble(),
      status: json['status'] ?? 'pending',
      paymentStatus: json['payment_status'] ?? 'pending',
      paidAt: json['paid_at'] != null ? DateTime.parse(json['paid_at']) : null,
      createdAt: DateTime.parse(json['created_at']),
      xenditInvoiceUrl: json['xendit_invoice_url'],
      invoicePdfUrl: json['invoice_pdf_url'],
      receiptPdfUrl: json['receipt_pdf_url'],
      resi: json['resi']?.toString(),
      orderProducts: (json['order_products'] as List?)
          ?.map((item) => OrderProduct.fromJson(item))
          .toList() ?? [],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'external_id': externalId,
      'total': total,
      'status': status,
      'payment_status': paymentStatus,
      'paid_at': paidAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'xendit_invoice_url': xenditInvoiceUrl,
      'invoice_pdf_url': invoicePdfUrl,
      'receipt_pdf_url': receiptPdfUrl,
      'resi': resi,
      'order_products': orderProducts.map((item) => item.toJson()).toList(),
    };
  }
}

class OrderProduct {
  final int id;
  final int orderId;
  final int productId;
  final int? variantId;
  final int quantity;
  final double price;
  final DateTime createdAt;
  final DateTime updatedAt;
  final Product product;
  final Variant? variant;

  OrderProduct({
    required this.id,
    required this.orderId,
    required this.productId,
    this.variantId,
    required this.quantity,
    required this.price,
    required this.createdAt,
    required this.updatedAt,
    required this.product,
    this.variant,
  });

  factory OrderProduct.fromJson(Map<String, dynamic> json) {
    return OrderProduct(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      orderId: json['order_id'] is int ? json['order_id'] : int.parse(json['order_id'].toString()),
      productId: json['product_id'] is int ? json['product_id'] : int.parse(json['product_id'].toString()),
      variantId: json['variant_id'] != null 
          ? (json['variant_id'] is int ? json['variant_id'] : int.parse(json['variant_id'].toString()))
          : null,
      quantity: json['quantity'] is int ? json['quantity'] : int.parse(json['quantity'].toString()),
      price: (json['price'] is String) 
          ? double.parse(json['price']) 
          : (json['price'] as num).toDouble(),
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      product: Product.fromJson(json['product']),
      variant: json['variant'] != null ? Variant.fromJson(json['variant']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'order_id': orderId,
      'product_id': productId,
      'variant_id': variantId,
      'quantity': quantity,
      'price': price,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'product': product.toJson(),
      'variant': variant?.toJson(),
    };
  }

  // Helper getter for display name
  String get displayName {
    if (variant != null) {
      return '${product.name} (${variant!.displayName})';
    }
    return product.name;
  }

  // Helper getter for total price
  double get totalPrice => price * quantity;
}

// Re-export existing models for compatibility


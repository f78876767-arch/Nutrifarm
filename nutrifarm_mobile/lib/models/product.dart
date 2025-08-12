class Product {
  final int id;
  final String name;
  final String? description;
  final double price;
  final double? discountPrice;
  final int stock;
  final bool active;
  final String? image;
  final DateTime createdAt;
  final DateTime updatedAt;
  final List<Category> categories;
  final List<Variant> variants;

  Product({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.discountPrice,
    required this.stock,
    required this.active,
    this.image,
    required this.createdAt,
    required this.updatedAt,
    required this.categories,
    required this.variants,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    try {
      return Product(
        id: json['id'],
        name: json['name'],
        description: json['description'],
        price: double.parse(json['price'].toString()),
        discountPrice: json['discount_price'] != null 
            ? double.parse(json['discount_price'].toString()) 
            : null,
        stock: json['stock_quantity'] ?? json['stock'] ?? 0, // Handle both field names
        active: json['is_active'] ?? json['active'] ?? true, // Handle both field names
        image: json['image_path'] ?? json['image'], // Handle both field names
        createdAt: DateTime.parse(json['created_at']),
        updatedAt: DateTime.parse(json['updated_at']),
        categories: (json['categories'] as List? ?? [])
            .map((category) => Category.fromJson(category))
            .toList(),
        variants: (json['variants'] as List? ?? [])
            .map((variant) => Variant.fromJson(variant))
            .toList(),
      );
    } catch (e) {
      print('❌ Product.fromJson parsing error: $e');
      print('📄 JSON data: $json');
      rethrow;
    }
  }

  // Helper methods for backwards compatibility with existing UI code
  double get originalPrice => price;
  double? get discount => discountPrice != null 
      ? ((price - discountPrice!) / price * 100).roundToDouble() 
      : null;
  bool get isFeatured => variants.isNotEmpty || (discount != null && discount! > 0);
  String get category => categories.isNotEmpty ? categories.first.name : 'Uncategorized';
  double get rating => 4.5; // Default rating until we have ratings API
  String get imageUrl => getImageUrl(image);
  
  // Get the effective price (discount price if available, otherwise regular price)
  double get effectivePrice => discountPrice ?? price;
  
  // Get display price in Rupiah format
  String get formattedPrice => 'Rp ${effectivePrice.toStringAsFixed(0).replaceAllMapped(
    RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
    (Match m) => '${m[1]}.'
  )}';
  
  String get formattedOriginalPrice => 'Rp ${price.toStringAsFixed(0).replaceAllMapped(
    RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
    (Match m) => '${m[1]}.'
  )}';
  
  // Check if product has discount
  bool get hasDiscount => discountPrice != null && discountPrice! < price;
  
  // Check if product is available
  bool get isAvailable => active && stock > 0;

  // Format total price (price * quantity) in Rupiah
  String formatTotalPrice(int quantity) => 'Rp ${(effectivePrice * quantity).toStringAsFixed(0).replaceAllMapped(
    RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
    (Match m) => '${m[1]}.'
  )}';

  static String getImageUrl(String? imagePath) {
    if (imagePath == null || imagePath.isEmpty) return '';
    return 'http://127.0.0.1:8000/storage/$imagePath';
  }
}

class Category {
  final int id;
  final String name;
  final DateTime createdAt;
  final DateTime updatedAt;

  Category({
    required this.id,
    required this.name,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'],
      name: json['name'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }
}

class Variant {
  final int id;
  final int productId;
  final String name;
  final String value;
  final String unit;
  final String? customUnit;
  final double? price;
  final int? stock;
  final DateTime createdAt;
  final DateTime updatedAt;

  Variant({
    required this.id,
    required this.productId,
    required this.name,
    required this.value,
    required this.unit,
    this.customUnit,
    this.price,
    this.stock,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Variant.fromJson(Map<String, dynamic> json) {
    return Variant(
      id: json['id'],
      productId: json['product_id'],
      name: json['name'],
      value: json['value'],
      unit: json['unit'],
      customUnit: json['custom_unit'],
      price: json['price'] != null ? double.parse(json['price']) : null,
      stock: json['stock'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  String get displayUnit => customUnit ?? unit;
  String get displayName => '$value $displayUnit';
}

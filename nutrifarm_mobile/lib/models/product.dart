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

  static double _safeDouble(dynamic v) {
    if (v == null) return 0.0;
    if (v is num) return v.toDouble();
    return double.tryParse(v.toString()) ?? 0.0;
  }

  factory Product.fromJson(Map<String, dynamic> json) {
    try {
      // ID & Name
      final int id = (json['id'] is String) ? int.tryParse(json['id']) ?? 0 : (json['id'] ?? 0);
      final String name = (json['name'] ?? 'Unknown').toString();

      // Price & Discount handling (flexible)
      final rawPrice = json['price'] ?? json['product_price'] ?? json['original_price'] ?? json['effective_price'] ?? 0;
      final double price = _safeDouble(rawPrice);
      double? discountPrice;
      if (json['discount_price'] != null) {
        discountPrice = _safeDouble(json['discount_price']);
      } else if (json['effective_price'] != null) {
        final eff = _safeDouble(json['effective_price']);
        if (eff > 0 && eff < price) discountPrice = eff;
      } else if (json['discount_amount'] != null) {
        final discAmount = _safeDouble(json['discount_amount']);
        if (discAmount > 0 && discAmount < price) {
          discountPrice = (price - discAmount).clamp(0, price);
        }
      }

      // Stock
      final stockRaw = json['stock_quantity'] ?? json['stock'] ?? json['qty_available'] ?? 0;
      final int stock = (stockRaw is String) ? int.tryParse(stockRaw) ?? 0 : (stockRaw is num ? stockRaw.toInt() : 0);

      // Dates (graceful fallback)
      final createdAt = DateTime.tryParse(json['created_at']?.toString() ?? '') ?? DateTime.now();
      final updatedAt = DateTime.tryParse(json['updated_at']?.toString() ?? '') ?? DateTime.now();

      // Image (multiple possible keys)
      final String? image = (json['image_path'] ?? json['image_url'] ?? json['image'])?.toString();

      // Categories: could be List<Map>, List<String>, single string, or null
      List<Category> categories = [];
      final rawCategories = json['categories'];
      if (rawCategories is List) {
        if (rawCategories.isNotEmpty) {
          if (rawCategories.first is Map<String, dynamic>) {
            categories = rawCategories
                .whereType<Map<String, dynamic>>()
                .map((c) => Category.fromJson(c))
                .toList();
          } else { // assume list of strings
            categories = rawCategories
                .map((c) => Category(
                      id: categories.length + 1,
                      name: c.toString(),
                      createdAt: DateTime.now(),
                      updatedAt: DateTime.now(),
                    ))
                .toList();
          }
        }
      } else if (rawCategories is String && rawCategories.isNotEmpty) {
        categories = [Category(id: 1, name: rawCategories, createdAt: DateTime.now(), updatedAt: DateTime.now())];
      }

      // Variants (optional list of maps)
      List<Variant> variants = [];
      if (json['variants'] is List) {
        variants = (json['variants'] as List)
            .whereType<Map<String, dynamic>>()
            .map((v) => Variant.fromJson(v))
            .toList();
      }

      return Product(
        id: id,
        name: name,
        description: json['description']?.toString(),
        price: price,
        discountPrice: discountPrice,
        stock: stock,
        active: (json['is_active'] ?? json['active'] ?? true) == true,
        image: image,
        createdAt: createdAt,
        updatedAt: updatedAt,
        categories: categories,
        variants: variants,
      );
    } catch (e) {
      print('❌ Product.fromJson parsing error (tolerant): $e');
      print('📄 JSON data: $json');
      // Return minimal product so UI can still render placeholder
      return Product(
        id: json['id'] ?? 0,
        name: (json['name'] ?? 'Unknown').toString(),
        description: json['description']?.toString(),
        price: _safeDouble(json['price']),
        discountPrice: null,
        stock: 0,
        active: true,
        image: (json['image_path'] ?? json['image_url'] ?? json['image'])?.toString(),
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
        categories: const [],
        variants: const [],
      );
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
  
  // NEW: Get minimum price from variants (for home page display)
  double get minPrice {
    if (variants.isEmpty) return effectivePrice;
    final variantPrices = variants
        .where((v) => v.effectivePrice > 0)
        .map((v) => v.effectivePrice)
        .toList();
    if (variantPrices.isEmpty) return effectivePrice;
    return variantPrices.reduce((a, b) => a < b ? a : b);
  }
  
  // NEW: Get maximum price from variants
  double get maxPrice {
    if (variants.isEmpty) return effectivePrice;
    final variantPrices = variants
        .where((v) => v.effectivePrice > 0)
        .map((v) => v.effectivePrice)
        .toList();
    if (variantPrices.isEmpty) return effectivePrice;
    return variantPrices.reduce((a, b) => a > b ? a : b);
  }
  
  // NEW: Get the cheapest variant (default selection)
  Variant? get cheapestVariant {
    if (variants.isEmpty) return null;
    final activeVariants = variants.where((v) => v.effectivePrice > 0).toList();
    if (activeVariants.isEmpty) return variants.isNotEmpty ? variants.first : null;
    
    activeVariants.sort((a, b) => a.effectivePrice.compareTo(b.effectivePrice));
    return activeVariants.first;
  }
  
  // NEW: Check if product has price range (min != max)
  bool get hasPriceRange {
    if (variants.length <= 1) return false;
    return minPrice != maxPrice;
  }
  
  // Get the effective price (use minPrice for home page, or discountPrice/price for backward compatibility)
  double get effectivePrice => discountPrice ?? price;
  
  // NEW: Get display price for home page (shows min price or range)
  String get displayPrice {
    if (variants.isEmpty) return formattedPrice;
    
    final min = minPrice;
    final max = maxPrice;
    
    if (min == max) {
      return 'Rp ${min.toStringAsFixed(0).replaceAllMapped(
        RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
        (Match m) => '${m[1]}.'
      )}';
    } else {
      return 'Rp ${min.toStringAsFixed(0).replaceAllMapped(
        RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
        (Match m) => '${m[1]}.'
      )} - ${max.toStringAsFixed(0).replaceAllMapped(
        RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
        (Match m) => '${m[1]}.'
      )}';
    }
  }
  
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
    // If already an absolute URL, return as-is
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    // Normalize leading slashes
    final cleaned = imagePath.startsWith('/') ? imagePath.substring(1) : imagePath;
    return 'http://127.0.0.1:8000/storage/$cleaned';
  }

  // Convert product to JSON for serialization
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'price': price,
      'discount_price': discountPrice,
      'stock_quantity': stock,
      'is_active': active,
      'image_path': image,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'categories': categories.map((cat) => cat.toJson()).toList(),
      'variants': variants.map((variant) => variant.toJson()).toList(),
    };
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
    try {
      return Category(
        id: json['id'] ?? 0,
        name: json['name'] ?? 'Unknown',
        createdAt: DateTime.tryParse(json['created_at']?.toString() ?? '') ?? DateTime.now(),
        updatedAt: DateTime.tryParse(json['updated_at']?.toString() ?? '') ?? DateTime.now(),
      );
    } catch (e) {
      print('❌ Category.fromJson parsing error: $e');
      print('📄 JSON data: $json');
      // Return a default category if parsing fails
      return Category(
        id: json['id'] ?? 0,
        name: json['name'] ?? 'Unknown',
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      );
    }
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
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
  final double? basePrice;
  final double? discountAmount;
  final bool? isDiscountActive;
  final int? stock;
  final String? sku;
  final double? weight;
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
    this.basePrice,
    this.discountAmount,
    this.isDiscountActive,
    this.stock,
    this.sku,
    this.weight,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Variant.fromJson(Map<String, dynamic> json) {
    double _localSafeDouble(dynamic v) {
      if (v == null) return 0.0;
      if (v is num) return v.toDouble();
      return double.tryParse(v.toString()) ?? 0.0;
    }
    try {
      return Variant(
        id: json['id'] ?? 0,
        productId: json['product_id'] ?? 0,
        name: (json['name'] ?? json['type'] ?? 'Default').toString(),
        value: json['value']?.toString() ?? '0',
        unit: json['unit']?.toString() ?? '',
        customUnit: json['custom_unit']?.toString(),
        price: json['price'] != null ? _localSafeDouble(json['price']) : null,
        basePrice: json['base_price'] != null ? _localSafeDouble(json['base_price']) : null,
        discountAmount: json['discount_amount'] != null ? _localSafeDouble(json['discount_amount']) : null,
        isDiscountActive: json['is_discount_active'] == true,
        stock: json['stock_quantity'] is String ? int.tryParse(json['stock_quantity']) : json['stock_quantity'] ?? json['stock'],
        sku: json['sku']?.toString(),
        weight: json['weight'] != null ? _localSafeDouble(json['weight']) : null,
        createdAt: DateTime.tryParse(json['created_at']?.toString() ?? '') ?? DateTime.now(),
        updatedAt: DateTime.tryParse(json['updated_at']?.toString() ?? '') ?? DateTime.now(),
      );
    } catch (e) {
      print('❌ Variant.fromJson parsing error: $e');
      print('📄 JSON data: $json');
      // Return a default variant if parsing fails
      return Variant(
        id: json['id'] ?? 0,
        productId: json['product_id'] ?? 0,
        name: 'Default',
        value: json['value']?.toString() ?? '0',
        unit: json['unit']?.toString() ?? '',
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      );
    }
  }

  String get displayUnit => customUnit ?? unit;
  String get displayName => '$value $displayUnit';
  
  // NEW: Get effective price (after discount)
  double get effectivePrice {
    final base = basePrice ?? price ?? 0.0;
    if (isDiscountActive == true && discountAmount != null && discountAmount! > 0) {
      return (base - discountAmount!).clamp(0, base);
    }
    return base;
  }
  
  // NEW: Get original price (before discount)
  double get originalPrice => basePrice ?? price ?? 0.0;
  
  // NEW: Check if variant has active discount
  bool get hasDiscount => isDiscountActive == true && discountAmount != null && discountAmount! > 0;
  
  // NEW: Get discount percentage
  double? get discountPercentage {
    if (!hasDiscount) return null;
    final base = originalPrice;
    if (base <= 0) return null;
    return ((discountAmount! / base) * 100).roundToDouble();
  }
  
  // NEW: Format price in Rupiah
  String get formattedPrice => 'Rp ${effectivePrice.toStringAsFixed(0).replaceAllMapped(
    RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
    (Match m) => '${m[1]}.'
  )}';
  
  String get formattedOriginalPrice => 'Rp ${originalPrice.toStringAsFixed(0).replaceAllMapped(
    RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), 
    (Match m) => '${m[1]}.'
  )}';
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'product_id': productId,
      'name': name,
      'value': value,
      'unit': unit,
      'custom_unit': customUnit,
      'price': price,
      'base_price': basePrice,
      'discount_amount': discountAmount,
      'is_discount_active': isDiscountActive,
      'stock_quantity': stock,
      'sku': sku,
      'weight': weight,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}

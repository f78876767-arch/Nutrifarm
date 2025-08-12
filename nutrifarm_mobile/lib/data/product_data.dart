import '../models/product.dart';
import '../services/api_service.dart';

class ProductData {
  // Cache for products to avoid repeated API calls
  static List<Product>? _cachedProducts;
  static List<String>? _cachedCategories;
  static DateTime? _lastFetch;
  
  // Cache duration (5 minutes)
  static const Duration _cacheDuration = Duration(minutes: 5);

  // Get all products (with caching)
  static Future<List<Product>> getProducts() async {
    if (_cachedProducts != null && 
        _lastFetch != null && 
        DateTime.now().difference(_lastFetch!) < _cacheDuration) {
      print('📦 Using cached products: ${_cachedProducts!.length} items');
      return _cachedProducts!;
    }

    try {
      print('🌐 Fetching products from API...');
      _cachedProducts = await ApiService.getProducts();
      _lastFetch = DateTime.now();
      print('✅ Products loaded successfully: ${_cachedProducts!.length} items');
      return _cachedProducts!;
    } catch (e) {
      print('❌ Failed to load products from API: $e');
      // If API fails and we have cached data, return it
      if (_cachedProducts != null) {
        print('📦 Fallback to cached products: ${_cachedProducts!.length} items');
        return _cachedProducts!;
      }
      // Otherwise return empty list
      print('📦 No cached data available, returning empty list');
      return [];
    }
  }

  // Get products synchronously (for backwards compatibility)
  static List<Product> get products => _cachedProducts ?? [];

  // Get single product by ID
  static Future<Product?> getProduct(int id) async {
    try {
      return await ApiService.getProduct(id);
    } catch (e) {
      // Try to find in cached products
      if (_cachedProducts != null) {
        try {
          return _cachedProducts!.firstWhere((p) => p.id == id);
        } catch (_) {
          return null;
        }
      }
      return null;
    }
  }

  // Get categories (with caching)
  static Future<List<String>> getCategories() async {
    if (_cachedCategories != null &&
        _lastFetch != null &&
        DateTime.now().difference(_lastFetch!) < _cacheDuration) {
      return _cachedCategories!;
    }

    try {
      _cachedCategories = await ApiService.getCategories();
      return _cachedCategories!;
    } catch (e) {
      // Return default categories if API fails
      _cachedCategories = ['All', 'Beverages', 'Health Products', 'Natural Foods'];
      return _cachedCategories!;
    }
  }

  // Get categories synchronously (for backwards compatibility)
  static List<String> get categories => _cachedCategories ?? ['All', 'Beverages', 'Health Products', 'Natural Foods'];

  // Get products by category
  static Future<List<Product>> getProductsByCategory(String category) async {
    if (category == 'All') {
      return await getProducts();
    }
    
    final products = await getProducts();
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

  // Get featured products
  static Future<List<Product>> getFeaturedProducts() async {
    final products = await getProducts();
    return products.where((product) => 
      product.discountPrice != null || product.variants.isNotEmpty
    ).toList();
  }

  // Get discounted products
  static Future<List<Product>> getDiscountedProducts() async {
    final products = await getProducts();
    return products.where((product) => product.discountPrice != null).toList();
  }

  // Clear cache (useful for refresh functionality)
  static void clearCache() {
    _cachedProducts = null;
    _cachedCategories = null;
    _lastFetch = null;
  }

  // Initialize data (call this when app starts)
  static Future<void> initialize() async {
    try {
      await getProducts();
      await getCategories();
    } catch (e) {
      print('Failed to initialize product data: $e');
    }
  }
}

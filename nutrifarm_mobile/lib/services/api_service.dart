import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/product.dart';

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
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        if (jsonResponse['success'] == true) {
          final List<dynamic> favoritesData = jsonResponse['data'];
          return favoritesData.map((favoriteItem) => 
            Product.fromJson(favoriteItem['product'])
          ).toList();
        } else {
          throw ApiException('Failed to get favorites: Invalid response format');
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

  static Future<bool> removeFromFavorites(int favoriteId) async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/favorites/$favoriteId'),
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
}

class ApiException implements Exception {
  final String message;
  ApiException(this.message);
  
  @override
  String toString() => 'ApiException: $message';
}

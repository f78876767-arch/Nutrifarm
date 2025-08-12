import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/product.dart';
import '../data/product_data.dart';

class SearchService extends ChangeNotifier {
  static final SearchService _instance = SearchService._internal();
  factory SearchService() => _instance;
  SearchService._internal() {
    _loadSearchHistory();
  }

  List<Product> _searchResults = [];
  List<String> _searchHistory = [];
  String _currentQuery = '';
  String _selectedCategory = 'All';
  
  List<Product> get searchResults => List.unmodifiable(_searchResults);
  List<String> get searchHistory => List.unmodifiable(_searchHistory);
  String get currentQuery => _currentQuery;
  String get selectedCategory => _selectedCategory;

  // Load search history from SharedPreferences
  void _loadSearchHistory() async {
    final prefs = await SharedPreferences.getInstance();
    _searchHistory = prefs.getStringList('search_history') ?? [];
    notifyListeners();
  }

  // Clear search history
  void clearSearchHistory() {
    _searchHistory.clear();
    _saveSearchHistory();
    notifyListeners();
  }

  // Save search history to SharedPreferences
  void _saveSearchHistory() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList('search_history', _searchHistory);
  }
  
  Future<List<Product>> search(String query, {String category = 'All'}) async {
    _currentQuery = query.trim();
    _selectedCategory = category;
    
    if (_currentQuery.isEmpty) {
      _searchResults = category == 'All' 
          ? await ProductData.getProducts()
          : await ProductData.getProductsByCategory(category);
    } else {
      // Perform search using API
      final allProducts = await ProductData.getProducts();
      var filteredProducts = allProducts.where((product) {
        final matchesQuery = product.name.toLowerCase().contains(_currentQuery.toLowerCase()) ||
                            (product.description?.toLowerCase().contains(_currentQuery.toLowerCase()) ?? false) ||
                            product.categories.any((cat) => 
                              cat.name.toLowerCase().contains(_currentQuery.toLowerCase())
                            );
        final matchesCategory = category == 'All' || 
                              product.categories.any((cat) => cat.name == category);
        return matchesQuery && matchesCategory;
      }).toList();
      
      // Sort by relevance
      filteredProducts.sort((a, b) {
        final aNameMatch = a.name.toLowerCase().startsWith(_currentQuery.toLowerCase());
        final bNameMatch = b.name.toLowerCase().startsWith(_currentQuery.toLowerCase());
        
        if (aNameMatch && !bNameMatch) return -1;
        if (!aNameMatch && bNameMatch) return 1;
        
        return a.name.compareTo(b.name);
      });
      
      _searchResults = filteredProducts;
    }
    
    notifyListeners();
    return _searchResults;
  }

  // Method to perform search AND add to history (only called when user commits to search)
  Future<List<Product>> performSearchAndAddToHistory(String query, {String category = 'All'}) async {
    final trimmedQuery = query.trim();
    
    // Add to search history only if query is not empty and not already in history
    if (trimmedQuery.isNotEmpty && !_searchHistory.contains(trimmedQuery)) {
      _searchHistory.insert(0, trimmedQuery);
      if (_searchHistory.length > 10) {
        _searchHistory.removeLast();
      }
      _saveSearchHistory();
    }
    
    // Perform the actual search
    return await search(trimmedQuery, category: category);
  }
  
  void clearSearch() {
    _currentQuery = '';
    _searchResults = [];
    notifyListeners();
  }
  
  void removeFromHistory(String query) {
    _searchHistory.remove(query);
    _saveSearchHistory();
    notifyListeners();
  }
  
  Future<List<Product>> getProductsByCategory(String category) async {
    return await ProductData.getProductsByCategory(category);
  }
  
  Future<List<Product>> getFeaturedProducts() async {
    return await ProductData.getFeaturedProducts();
  }
  
  Future<List<Product>> getDiscountedProducts() async {
    return await ProductData.getDiscountedProducts();
  }
  
  Future<List<String>> getSuggestions(String query) async {
    if (query.isEmpty) return _searchHistory;
    
    final suggestions = <String>[];
    
    // Add matching history items
    for (final item in _searchHistory) {
      if (item.toLowerCase().contains(query.toLowerCase())) {
        suggestions.add(item);
      }
    }
    
    // Add matching product names from API
    try {
      final products = await ProductData.getProducts();
      for (final product in products) {
        if (product.name.toLowerCase().contains(query.toLowerCase())) {
          if (!suggestions.contains(product.name)) {
            suggestions.add(product.name);
          }
        }
      }
    } catch (e) {
      // If API fails, just return history suggestions
    }
    
    return suggestions.take(8).toList();
  }
}

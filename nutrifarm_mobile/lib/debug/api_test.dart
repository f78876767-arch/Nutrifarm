import 'dart:convert';
import 'package:http/http.dart' as http;
import '../services/api_service.dart';
import '../models/product.dart';

class ApiTest {
  static Future<void> testProductsAPI() async {
    print('🧪 Testing Products API Connection...');
    print('🌐 Base URL: ${ApiService.baseUrl}');
    
    try {
      // Test raw HTTP call first
      print('📡 Making raw HTTP request...');
      final response = await http.get(
        Uri.parse('${ApiService.baseUrl}/products'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      print('📊 Response Status: ${response.statusCode}');
      print('📄 Response Headers: ${response.headers}');
      
      if (response.statusCode == 200) {
        final List<dynamic> jsonData = json.decode(response.body);
        print('✅ Raw API Response Success!');
        print('📦 Products Count: ${jsonData.length}');
        print('📝 First Product Raw: ${jsonData.isNotEmpty ? jsonData.first : "No products"}');
        
        // Test Product model parsing
        print('\n🔧 Testing Product Model Parsing...');
        try {
          final products = jsonData.map((json) => Product.fromJson(json)).toList();
          print('✅ Product Model Parsing Success!');
          print('📊 Parsed Products: ${products.length}');
          
          if (products.isNotEmpty) {
            final firstProduct = products.first;
            print('🛍️  First Product Details:');
            print('   - ID: ${firstProduct.id}');
            print('   - Name: ${firstProduct.name}');
            print('   - Price: ${firstProduct.formattedPrice}');
            print('   - Stock: ${firstProduct.stock}');
            print('   - Active: ${firstProduct.active}');
            print('   - Image: ${firstProduct.image ?? "No image"}');
            print('   - Categories: ${firstProduct.categories.map((c) => c.name).join(", ")}');
            print('   - Variants: ${firstProduct.variants.length}');
          }
          
        } catch (modelError) {
          print('❌ Product Model Parsing Failed!');
          print('🐛 Model Error: $modelError');
          
          // Show detailed parsing info
          if (jsonData.isNotEmpty) {
            final firstJson = jsonData.first;
            print('🔍 First Product JSON Structure:');
            firstJson.forEach((key, value) {
              print('   - $key: $value (${value.runtimeType})');
            });
          }
        }
        
      } else {
        print('❌ API Request Failed!');
        print('📄 Response Body: ${response.body}');
      }
      
    } catch (e) {
      print('💥 API Test Failed with Exception!');
      print('🐛 Error: $e');
      print('🔧 Error Type: ${e.runtimeType}');
    }
    
    // Test using ApiService
    print('\n🔧 Testing via ApiService...');
    try {
      final products = await ApiService.getProducts();
      print('✅ ApiService Success!');
      print('📦 Products via ApiService: ${products.length}');
    } catch (apiError) {
      print('❌ ApiService Failed!');
      print('🐛 ApiService Error: $apiError');
    }
  }
}

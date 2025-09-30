# Product Sales Count - Frontend Integration Guide

## 🎯 **Overview**
The backend now provides real sales count data for all products. The Flutter app can display actual "X+ sold" instead of hardcoded values.

## 📊 **API Response Format**

### **GET /api/products**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "name": "Sari Lemon",
      "description": "Air sari lemon yang sangat menyegarkan...",
      "price": 59000,
      "effective_price": 59000,
      "discount_amount": 3000,
      "is_discount_active": true,
      "image_url": "http://yourapp.com/storage/products/...",
      "stock_quantity": 2000,
      "is_active": true,
      "is_featured": false,
      "categories": ["Minuman"],
      "variants": [...],
      
      // 🔥 NEW SALES DATA
      "total_sales": 420,     // Total units sold
      "sales_count": 420      // Same as total_sales (alternative field)
    },
    {
      "id": 6,
      "name": "Sari Kurma 1 L",
      "price": 25000,
      "total_sales": 59,      // Only 59 units sold
      "sales_count": 59
    }
  ]
}
```

## 🔧 **Frontend Implementation**

### **Flutter Model Update**
```dart
class Product {
  final int id;
  final String name;
  final double price;
  final String imageUrl;
  final int stockQuantity;
  // Add these new fields
  final int totalSales;
  final int salesCount;
  
  Product({
    required this.id,
    required this.name,
    required this.price,
    required this.imageUrl,
    required this.stockQuantity,
    required this.totalSales,      // 🔥 NEW
    required this.salesCount,      // 🔥 NEW
  });
  
  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'],
      price: json['effective_price']?.toDouble() ?? 0.0,
      imageUrl: json['image_url'] ?? '',
      stockQuantity: json['stock_quantity'] ?? 0,
      totalSales: json['total_sales'] ?? 0,      // 🔥 NEW
      salesCount: json['sales_count'] ?? 0,      // 🔥 NEW
    );
  }
}
```

### **Sales Display Logic**
```dart
class ProductCard extends StatelessWidget {
  final Product product;
  
  String get salesText {
    final sales = product.totalSales;
    
    if (sales == 0) {
      return "New product";
    } else if (sales < 10) {
      return "$sales sold";
    } else if (sales < 1000) {
      return "$sales+ sold";
    } else {
      final k = (sales / 1000).toStringAsFixed(1);
      return "${k}k+ sold";
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Card(
      child: Column(
        children: [
          // Product image, name, price...
          
          // Sales count display
          Container(
            padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: product.totalSales > 0 
                ? Colors.green.withOpacity(0.1) 
                : Colors.grey.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              salesText,
              style: TextStyle(
                fontSize: 12,
                color: product.totalSales > 0 
                  ? Colors.green[700] 
                  : Colors.grey[600],
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
```

### **Popular Products Feature**
```dart
// You can now sort by sales count
List<Product> getPopularProducts(List<Product> products) {
  return products
    ..sort((a, b) => b.totalSales.compareTo(a.totalSales))
    ..take(10).toList();
}

// Or use the dedicated API endpoint
// GET /api/products-popular
```

## 🎨 **UI Examples**

### **Sales Badge Styling**
```dart
Widget buildSalesBadge(int salesCount) {
  if (salesCount == 0) {
    return Container(
      padding: EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: Colors.blue[50],
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.blue[200]!),
      ),
      child: Text(
        "NEW",
        style: TextStyle(
          fontSize: 10,
          color: Colors.blue[700],
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
  
  return Container(
    padding: EdgeInsets.symmetric(horizontal: 8, vertical: 2),
    decoration: BoxDecoration(
      color: Colors.green[50],
      borderRadius: BorderRadius.circular(10),
    ),
    child: Text(
      _formatSalesCount(salesCount),
      style: TextStyle(
        fontSize: 10,
        color: Colors.green[700],
        fontWeight: FontWeight.w500,
      ),
    ),
  );
}

String _formatSalesCount(int count) {
  if (count < 10) return "$count sold";
  if (count < 1000) return "$count+ sold";
  return "${(count/1000).toStringAsFixed(1)}k+ sold";
}
```

## 🔥 **Real Data Examples**

Based on current database:
- **Sari Lemon**: 420 sales → Shows "420+ sold"
- **Sari Kurma 1 L**: 59 sales → Shows "59+ sold"
- **New products**: 0 sales → Shows "New product"

## 📈 **Analytics Features**

### **Top Selling Products**
```dart
Widget buildTopSellingSection(List<Product> products) {
  final topSelling = products
    .where((p) => p.totalSales > 100)
    .toList()
    ..sort((a, b) => b.totalSales.compareTo(a.totalSales));
    
  return Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Text("🔥 Top Selling", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
      SizedBox(height: 8),
      ...topSelling.take(5).map((product) => 
        ListTile(
          leading: Image.network(product.imageUrl),
          title: Text(product.name),
          subtitle: Text("${product.totalSales}+ sold"),
          trailing: Text("Rp ${NumberFormat('#,###').format(product.price)}"),
        )
      ),
    ],
  );
}
```

## 🎯 **Key Benefits**

1. **Real Data**: No more hardcoded sales numbers
2. **Dynamic**: Updates automatically when orders complete
3. **Social Proof**: Shows actual customer purchase behavior
4. **Sorting**: Can sort products by popularity
5. **Analytics**: Track which products are selling well

## 🚀 **API Endpoints Available**

- `GET /api/products` - All products with sales data
- `GET /api/products/{id}` - Single product with sales data
- `GET /api/products-popular` - Products sorted by sales (top 10)
- `GET /api/products-featured` - Featured products with sales data

## ✅ **What's Ready**

- ✅ Database migration completed
- ✅ API endpoints returning sales data
- ✅ Test data seeded (420 sales for Sari Lemon)
- ✅ Auto-increment when orders complete
- ✅ ProductResource with clean JSON format

## 🔄 **Next Steps for Frontend**

1. Update Product model to include `total_sales` field
2. Modify product cards to show sales count
3. Add "New product" badge for 0 sales
4. Implement popular products section
5. Test with real API data

**Ready to implement! No more hardcoded sales numbers! 🎉**

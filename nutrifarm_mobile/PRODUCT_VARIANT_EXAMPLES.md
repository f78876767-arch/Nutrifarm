# Contoh Data Produk dengan Varian

## Contoh JSON Response dari Backend

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Virgin Coconut Oil Nutrifarm",
    "description": "Minyak kelapa murni berkualitas tinggi, diproses secara cold-pressed untuk mempertahankan nutrisi alami.",
    "price": 25000,
    "discount_price": null,
    "stock": 100,
    "image_path": "products/vco-nutrifarm.jpg",
    "variants": [
      {
        "id": 1,
        "product_id": 1,
        "name": "Volume",
        "value": "250",
        "unit": "ml",
        "custom_unit": null,
        "price": 25000,
        "stock": 50
      },
      {
        "id": 2,
        "product_id": 1,
        "name": "Volume",
        "value": "500",
        "unit": "ml", 
        "custom_unit": null,
        "price": 45000,
        "stock": 30
      },
      {
        "id": 3,
        "product_id": 1,
        "name": "Volume",
        "value": "1",
        "unit": "liter",
        "custom_unit": null,
        "price": 80000,
        "stock": 20
      }
    ]
  }
}
```

## Contoh Penggunaan dalam Flutter

### 1. Menampilkan Varian di Product Detail

```dart
class ProductDetailWidget extends StatefulWidget {
  final Product product;
  
  @override
  _ProductDetailWidgetState createState() => _ProductDetailWidgetState();
}

class _ProductDetailWidgetState extends State<ProductDetailWidget> {
  Variant? selectedVariant;
  
  @override
  void initState() {
    super.initState();
    // Auto-select first variant
    if (widget.product.variants.isNotEmpty) {
      selectedVariant = widget.product.variants.first;
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Product name
        Text(widget.product.name),
        
        // Price (updates based on selected variant)
        Text(_getEffectivePrice()),
        
        // Variant selector
        if (widget.product.variants.isNotEmpty)
          _buildVariantSelector(),
          
        // Add to cart button
        ElevatedButton(
          onPressed: () => _addToCart(),
          child: Text('Tambah ke Keranjang'),
        ),
      ],
    );
  }
  
  Widget _buildVariantSelector() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Pilih ${widget.product.variants.first.name}:'),
        SizedBox(height: 8),
        Wrap(
          spacing: 8,
          children: widget.product.variants.map((variant) {
            final isSelected = selectedVariant?.id == variant.id;
            return ChoiceChip(
              label: Text(variant.displayName), // "250 ml"
              selected: isSelected,
              onSelected: (selected) {
                if (selected) {
                  setState(() => selectedVariant = variant);
                }
              },
            );
          }).toList(),
        ),
      ],
    );
  }
  
  String _getEffectivePrice() {
    final price = selectedVariant?.price ?? widget.product.price;
    return 'Rp ${price.toStringAsFixed(0)}';
  }
  
  void _addToCart() {
    CartService().addToCart(
      widget.product, 
      quantity: 1,
      variant: selectedVariant,
    );
  }
}
```

### 2. Menampilkan Cart Item dengan Varian

```dart
Widget buildCartItem(CartItem item) {
  return ListTile(
    leading: Image.network(item.product.imageUrl),
    title: Text(item.displayName), // "Virgin Coconut Oil (500 ml)"
    subtitle: Text('Rp ${item.effectivePrice}'),
    trailing: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        IconButton(
          onPressed: () => CartService().decreaseQuantity(
            item.product.id,
            variant: item.selectedVariant,
          ),
          icon: Icon(Icons.remove),
        ),
        Text('${item.quantity}'),
        IconButton(
          onPressed: () => CartService().increaseQuantity(
            item.product.id, 
            variant: item.selectedVariant,
          ),
          icon: Icon(Icons.add),
        ),
      ],
    ),
  );
}
```

## Contoh Skenario Penggunaan

### Skenario 1: Produk Minyak Kelapa dengan Varian Volume
- **Produk**: Virgin Coconut Oil
- **Varian**: 250ml (Rp 25.000), 500ml (Rp 45.000), 1L (Rp 80.000)
- **User Flow**: User pilih 500ml → harga update ke Rp 45.000 → add to cart → cart show "Virgin Coconut Oil (500 ml)"

### Skenario 2: Produk Madu dengan Varian Rasa
```json
{
  "name": "Madu Kelengkeng Premium",
  "variants": [
    {"name": "Rasa", "value": "Original", "unit": "", "price": 50000},
    {"name": "Rasa", "value": "Jahe", "unit": "", "price": 55000},
    {"name": "Rasa", "value": "Kunyit", "unit": "", "price": 55000}
  ]
}
```

### Skenario 3: Produk dengan Multiple Variant Types
```json
{
  "name": "Beras Organik",
  "variants": [
    {"name": "Kemasan", "value": "1", "unit": "kg", "price": 15000},
    {"name": "Kemasan", "value": "5", "unit": "kg", "price": 70000},
    {"name": "Kemasan", "value": "25", "unit": "kg", "price": 320000}
  ]
}
```

## Contoh Cart API Calls

### Add to Cart dengan Varian
```dart
// POST /api/cart
{
  "product_id": 1,
  "variant_id": 2,  // 500ml variant
  "quantity": 2
}
```

### Update Quantity untuk Varian Spesifik
```dart  
// PUT /api/cart/123
{
  "product_id": 1,
  "variant_id": 2,
  "quantity": 3
}
```

### Response Cart dengan Varian
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 123,
        "product_id": 1,
        "variant_id": 2,
        "quantity": 2,
        "product": {
          "id": 1,
          "name": "Virgin Coconut Oil Nutrifarm",
          "image_path": "products/vco.jpg"
        },
        "variant": {
          "id": 2,
          "name": "Volume",
          "value": "500",
          "unit": "ml",
          "price": 45000
        }
      }
    ]
  }
}
```

## Testing Checklist

### ✅ Basic Functionality
- [ ] Produk tanpa varian bisa ditambah ke cart normal
- [ ] Produk dengan varian menampilkan selector
- [ ] Harga berubah saat pilih varian berbeda  
- [ ] Cart menampilkan nama dengan varian
- [ ] Quantity update bekerja per varian

### ✅ Advanced Scenarios  
- [ ] Multiple varian produk yang sama di cart
- [ ] Varian dengan harga sama dengan produk base
- [ ] Varian tanpa custom price (use base price)
- [ ] Stock validation per varian
- [ ] Debouncing bekerja per varian

### ✅ UI/UX Validation
- [ ] Variant selector mudah digunakan
- [ ] Visual feedback untuk selected variant
- [ ] Price update smooth tanpa delay
- [ ] Cart item naming clear dan informatif
- [ ] Loading states tidak mengganggu

---

**Catatan**: Sistem ini dirancang fleksibel untuk mendukung berbagai jenis varian produk yang umum di marketplace seperti ukuran, warna, rasa, kemasan, dll.

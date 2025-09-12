# Variant-Based Pricing System Migration

## Overview
Successfully migrated the mobile app to use the new variant-centric pricing system, similar to Tokopedia/Shopee's approach where:
- Home page shows minimum price or price range
- Product detail pages default to cheapest variant
- Pricing updates dynamically when variant changes

## Changes Made

### 1. Product Model Updates (`lib/models/product.dart`)

#### New Properties & Methods:
```dart
// Price calculations from variants
double get minPrice           // Cheapest variant price (for home display)
double get maxPrice           // Most expensive variant price
Variant? get cheapestVariant  // Default variant selection
bool get hasPriceRange        // Whether min != max price

// Display formatting
String get displayPrice       // Shows "Rp 25.000" or "Rp 25.000 - 50.000" range
```

#### Variant Model Enhancements:
```dart
// New variant properties from backend
final double? basePrice;
final double? discountAmount;
final bool? isDiscountActive;
final String? sku;
final double? weight;

// Calculated properties
double get effectivePrice     // Price after discount
double get originalPrice      // Price before discount
bool get hasDiscount          // Whether variant has active discount
double? get discountPercentage // Discount % if applicable
String get formattedPrice     // Formatted Rupiah price
```

### 2. Home Page Product Cards (`lib/widgets/product_card.dart`)

#### Price Display:
- **Before**: `widget.product.formattedPrice` (single product price)
- **After**: `widget.product.displayPrice` (min price or range)

#### Additional Info:
```dart
// Shows variant count
if (widget.product.variants.isNotEmpty)
  Text('${widget.product.variants.length} variant${widget.product.variants.length > 1 ? 's' : ''}')
```

#### Add to Cart:
```dart
// Automatically adds cheapest variant
await cartService.addToCart(
  widget.product, 
  quantity: 1,
  variant: widget.product.cheapestVariant,
);
```

### 3. Product Detail Pages

#### Default Variant Selection:
```dart
// Both product_detail_page.dart & product_detail_page_new.dart
void _preselectVariant() {
  if (_product!.variants.isNotEmpty) {
    _selectedVariant = _product!.cheapestVariant ?? _product!.variants.first;
  }
}
```

#### Price Calculations:
```dart
// Legacy page (product_detail_page.dart)
double get _basePrice => _selectedVariant?.effectivePrice ?? _product!.minPrice;
bool get _hasDiscount => _selectedVariant?.hasDiscount ?? _product!.hasDiscount;
double get _discountPercent => _selectedVariant?.discountPercentage ?? (_product!.discount ?? 0);

// New page (product_detail_page_new.dart)
double get effectivePrice => selectedVariant?.effectivePrice ?? widget.product.minPrice;
bool get hasDiscount => selectedVariant?.hasDiscount ?? widget.product.hasDiscount;
```

#### Dynamic Price Display:
```dart
// Shows variant-based pricing that updates when selection changes
Row(
  children: [
    Text(formattedEffectivePrice, style: priceStyle),
    if (hasDiscount) ...[
      const SizedBox(width: 8),
      Text(formattedOriginalPrice, style: strikethroughStyle),
    ],
  ],
)
```

## User Experience

### Home Page (Like Tokopedia/Shopee):
1. **Single Variant Products**: Shows exact price "Rp 25.000"
2. **Multiple Variants**: Shows range "Rp 25.000 - 50.000" 
3. **Add to Cart**: Automatically selects cheapest variant
4. **Variant Indicator**: Shows "2 variants" below price

### Product Detail Page:
1. **Default Selection**: Cheapest variant auto-selected
2. **Price Updates**: Real-time price changes when variant selected
3. **Discount Display**: Shows variant-specific discounts
4. **Stock Info**: Can show variant-specific stock (when backend ready)

## API Integration Notes

### Expected Backend Response:
```json
{
  "id": 1,
  "name": "Virgin Coconut Oil",
  "variants": [
    {
      "id": 1,
      "name": "Size",
      "value": "250ml",
      "base_price": 25000,
      "effective_price": 20000,
      "discount_amount": 5000,
      "is_discount_active": true,
      "stock_quantity": 100,
      "sku": "VCO-250ML"
    },
    {
      "id": 2,
      "name": "Size", 
      "value": "500ml",
      "base_price": 45000,
      "effective_price": 45000,
      "discount_amount": 0,
      "is_discount_active": false,
      "stock_quantity": 50,
      "sku": "VCO-500ML"
    }
  ]
}
```

### Backward Compatibility:
- Product-level fields still supported for gradual migration
- Falls back to product price if no variants available
- Existing cart functionality preserved

## Benefits

1. **Better UX**: Users see minimum price first (like major e-commerce)
2. **Flexible Pricing**: Different variants can have different discounts
3. **Clearer Information**: Price ranges indicate choice available
4. **Consistent Experience**: Matches user expectations from other platforms
5. **Performance**: No additional API calls needed

## Testing Checklist

- [x] Home page shows correct min prices or ranges
- [x] Product detail defaults to cheapest variant
- [x] Price updates when variant changes
- [x] Add to cart works with variants
- [x] Discount calculations work per variant
- [x] Backward compatibility maintained
- [ ] Server integration with variant_id (pending backend confirmation)

## Next Steps

1. **Backend Integration**: Verify server accepts `variant_id` in cart operations
2. **Stock Display**: Update UI to show variant-specific stock levels
3. **Multi-Attribute Variants**: Support color + size combinations if needed
4. **Performance Optimization**: Consider caching min/max price calculations

The migration is complete and ready for testing with the new backend structure! 🚀

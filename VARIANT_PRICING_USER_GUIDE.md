# Variant Pricing System - User Guide

## ✅ Fixed Issues

### Database Constraint Fixed
- **Problem**: `base_price` field was NOT NULL, causing errors when creating variants
- **Solution**: Migration created to make `base_price` nullable
- **Status**: ✅ Fixed - Migration `2025_08_13_023032_make_variant_base_price_nullable` applied

### Admin Form Fields Updated
- **Problem**: Form used old field names (`price`, `stock`) instead of new variant fields
- **Solution**: Updated both create and edit forms with correct field names
- **New Fields**: `base_price`, `stock_quantity`, `sku`, `discount_amount`, `weight`
- **Status**: ✅ Fixed - Forms now use proper variant field names

## 🚀 How to Use Variant Pricing

### 1. Product Structure Now:
- **Products**: Store only metadata (name, description, image, category)
- **Variants**: Store all pricing, stock, and SKU information

### 2. Creating Products with Variants:

#### Via Admin Interface:
1. Go to **Create Product** or **Edit Product**
2. Fill in basic product info (name, description, image)
3. Click **Add Variant** to add pricing variants
4. For each variant, set:
   - **Variant Name**: e.g., "Size", "Weight", "Type"
   - **Variant Value**: e.g., "500ml", "1kg", "Premium"
   - **Unit**: ml, kg, g, l, pcs, pack
   - **Base Price**: The variant's main price
   - **Stock Quantity**: Stock for this specific variant
   - **SKU**: Unique identifier for this variant
   - **Discount**: Optional discount amount in Rupiah
   - **Weight**: For shipping calculations

#### Example Variant Setup:
```
Product: "Madu Premium"
├── Variant 1: Size - 250ml, Price: 25,000, Stock: 50
├── Variant 2: Size - 500ml, Price: 45,000, Stock: 30, Discount: 5,000  
└── Variant 3: Size - 1L, Price: 80,000, Stock: 15
```

### 3. Setting Variant Prices:

You can now set **different prices for each variant**:
- **40,000** for "Size - 250ml"  
- **70,000** for "Size - 500ml"
- **120,000** for "Size - 1L"

Each variant can also have **individual discounts**:
- 250ml: No discount
- 500ml: 5,000 Rupiah discount (becomes 65,000)
- 1L: 10,000 Rupiah discount (becomes 110,000)

### 4. API Response Structure:

The API now returns detailed variant information:

```json
{
  "id": 6,
  "name": "Madu 1 L",
  "price": 45000,
  "effective_price": 40000,
  "discount_amount": 5000,
  "is_discount_active": true,
  "variants": [
    {
      "id": 5,
      "name": "Size",
      "value": "500ml",
      "base_price": 45000,
      "effective_price": 40000,
      "discount_amount": 5000,
      "is_discount_active": true,
      "stock_quantity": 100,
      "sku": "HONEY-500ML",
      "weight": 0.6,
      "unit": "ml"
    }
  ]
}
```

### 5. Import via CSV:

Use the new CSV template with these headers:
```csv
name,description,category,variant_name,variant_value,base_price,stock_quantity,sku,is_active,discount_amount,weight,unit
```

Example data:
```csv
Madu Premium,Madu asli,Minuman,Size,250ml,25000,50,HONEY-250,true,,0.3,ml
Madu Premium,Madu asli,Minuman,Size,500ml,45000,30,HONEY-500,true,5000,0.6,ml
```

## 🎯 Benefits You Get:

1. **Flexible Pricing**: Each variant can have completely different prices
2. **Individual Discounts**: Apply discounts to specific variants only
3. **Granular Stock**: Track inventory per variant
4. **Better Mobile Experience**: App shows detailed variant options
5. **Unique SKUs**: Each variant has its own SKU for tracking
6. **Weight-based Shipping**: Calculate shipping costs per variant

## 💡 Tips:

- **Start Simple**: Create one variant per product initially
- **Use Descriptive Names**: "Size", "Weight", "Type", "Flavor" etc.
- **Consistent Values**: "250ml", "500ml", "1L" for sizes
- **Test Discounts**: Set different discount amounts per variant
- **Mobile-First**: API provides all variant data for mobile app

The system is now ready for complex product catalogs with multiple pricing tiers! 🎉

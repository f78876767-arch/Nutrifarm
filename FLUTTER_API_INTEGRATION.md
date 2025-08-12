# Flutter API Integration Guide for Nutrifarm Mobile

## Overview
This document contains all the information needed to integrate the Nutrifarm Laravel API with the Flutter mobile application. Replace hardcoded product data with real API calls using the structure and endpoints defined below.

## API Configuration

### Base URL
```
http://127.0.0.1:8000/api
```
> **Note**: Change this to your production server URL when deploying

### Authentication
Products API is **public** - no authentication required for reading products.

## API Endpoints

### Products
- **GET** `/api/products` - Get all products with categories and variants
- **GET** `/api/products/{id}` - Get single product with full details

### Required Headers
```
Content-Type: application/json
Accept: application/json
```

## Complete API Response Structure

### Products List Response
```json
[
  {
    "id": 1,
    "name": "Cuka Apel",
    "description": "Cuka apel organik untuk kesehatan.",
    "price": "35000.00",
    "discount_price": "29991.00",
    "stock": 100,
    "active": true,
    "image": "products/cuka-apel-250.png",
    "created_at": "2025-08-04T18:53:46.000000Z",
    "updated_at": "2025-08-05T14:32:32.000000Z",
    "categories": [
      {
        "id": 5,
        "name": "Beverages",
        "created_at": "2025-08-05T14:21:07.000000Z",
        "updated_at": "2025-08-05T14:21:07.000000Z"
      }
    ],
    "variants": [
      {
        "id": 1,
        "product_id": 1,
        "name": "Cuka Apel 500 ML",
        "value": "500",
        "unit": "ml",
        "custom_unit": null,
        "price": "27000.00",
        "stock": 1000,
        "created_at": "2025-08-05T14:39:19.000000Z",
        "updated_at": "2025-08-05T14:48:10.000000Z"
      },
      {
        "id": 2,
        "product_id": 1,
        "name": "Cuka Apel 250 ML",
        "value": "250",
        "unit": "ml",
        "custom_unit": null,
        "price": "20000.00",
        "stock": 500,
        "created_at": "2025-08-05T14:44:03.000000Z",
        "updated_at": "2025-08-05T14:48:10.000000Z"
      }
    ]
  }
]
```

### Single Product Response
Same structure as individual items in the products list above.

## Data Models and Types

### Product Model
```dart
class Product {
  final int id;                    // Product ID
  final String name;               // Product name
  final String? description;       // Product description (nullable)
  final double price;              // Base price (convert from string)
  final double? discountPrice;     // Discounted price (nullable, convert from string)
  final int stock;                 // Available stock quantity
  final bool active;               // Product availability status
  final String? image;             // Image path (nullable)
  final DateTime createdAt;        // Creation timestamp
  final DateTime updatedAt;        // Update timestamp
  final List<Category> categories; // Associated categories
  final List<Variant> variants;    // Product variants/options
}
```

### Category Model
```dart
class Category {
  final int id;           // Category ID
  final String name;      // Category name
  final DateTime createdAt;
  final DateTime updatedAt;
}
```

### Variant Model
```dart
class Variant {
  final int id;               // Variant ID
  final int productId;        // Parent product ID
  final String name;          // Variant name (e.g., "Cuka Apel 500 ML")
  final String value;         // Size/amount value (e.g., "500")
  final String unit;          // Unit type (ml, g, kg, l, other)
  final String? customUnit;   // Custom unit if unit is "other" (nullable)
  final double? price;        // Variant-specific price (nullable, convert from string)
  final int? stock;           // Variant-specific stock (nullable)
  final DateTime createdAt;
  final DateTime updatedAt;
}
```

## Field Specifications

### Product Fields
| Field | Type | Description | Notes |
|-------|------|-------------|--------|
| `id` | `int` | Product ID | Primary key |
| `name` | `String` | Product name | Required |
| `description` | `String?` | Product description | Nullable |
| `price` | `String` → `double` | Base price | Convert to double, IDR currency |
| `discount_price` | `String?` → `double?` | Discounted price | Nullable, convert to double |
| `stock` | `int` | Available stock | Required |
| `active` | `bool` | Product availability | Required |
| `image` | `String?` | Image path | Nullable, see Image URL Construction |
| `categories` | `List<Category>` | Associated categories | For filtering/organization |
| `variants` | `List<Variant>` | Product variants | Different sizes/options |

### Variant Fields
| Field | Type | Description | Notes |
|-------|------|-------------|--------|
| `unit` | `String` | Unit type | Values: "ml", "g", "kg", "l", "other" |
| `custom_unit` | `String?` | Custom unit | Only when unit is "other" |
| `value` | `String` | Amount/size | e.g., "500", "250" |
| `price` | `String?` → `double?` | Variant price | Nullable, overrides product price |
| `stock` | `int?` | Variant stock | Nullable, overrides product stock |

## Image URL Construction
```dart
String getImageUrl(String? imagePath) {
  if (imagePath == null) return null;
  return 'http://127.0.0.1:8000/storage/$imagePath';
}

// Example:
// imagePath: "products/cuka-apel-250.png"
// Full URL: "http://127.0.0.1:8000/storage/products/cuka-apel-250.png"
```

## Business Logic Rules

### Product Display
1. **Price Display**:
   - If `discount_price` exists: Show original price crossed out, discount price prominently
   - If no discount: Show regular price
   - Format: "Rp 35.000" or "Rp 35,000"

2. **Availability**:
   - Only show "Add to Cart" if `active: true` AND `stock > 0`
   - Show "Out of Stock" if stock is 0
   - Show "Unavailable" if active is false

3. **Variants**:
   - Variants are options for the same product (not separate products)
   - Each variant can have its own price and stock
   - If variant has no price, use product price
   - If variant has no stock, use product stock

### Error Handling
- Handle network timeouts gracefully
- Show loading states during API calls
- Provide retry functionality for failed requests
- Cache products data when possible

## Required Flutter Dependencies
Add to `pubspec.yaml`:
```yaml
dependencies:
  http: ^1.1.0  # For API calls
```

## Currency and Localization
- All prices are in Indonesian Rupiah (IDR)
- Format prices with thousand separators
- Example: 35000 → "Rp 35.000"

## Implementation Requirements

### Replace Hardcoded Data
Replace all hardcoded product arrays/lists with API calls to:
- `GET /api/products` for product listings
- `GET /api/products/{id}` for product details

### API Integration Steps
1. Create HTTP service class for API calls
2. Create data models matching the JSON structure above
3. Implement JSON parsing using `fromJson` factory methods
4. Replace hardcoded data with API calls
5. Add loading states and error handling
6. Implement image loading with proper URLs

### UI Considerations
- Show loading spinners during API calls
- Display product images using NetworkImage with proper URLs
- Handle null/empty states gracefully
- Implement pull-to-refresh functionality

## Testing the API
Test endpoints using curl:
```bash
# Get all products
curl -X GET "http://127.0.0.1:8000/api/products" -H "Accept: application/json"

# Get single product
curl -X GET "http://127.0.0.1:8000/api/products/1" -H "Accept: application/json"
```

## Additional APIs Available

### Authentication (Required for Cart & Favorites)
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Login Response:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### Cart Management

#### Get Cart Items
```http
GET /api/cart
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "variant_id": 2,
        "quantity": 2,
        "price": 15000,
        "total_price": 30000,
        "product": {
          "id": 1,
          "name": "Fresh Tomatoes",
          "image_url": "storage/products/tomatoes.jpg",
          "category": "Vegetables"
        },
        "variant": {
          "id": 2,
          "type": "size",
          "value": "1 kg",
          "unit": "kg",
          "price": 15000
        }
      }
    ],
    "summary": {
      "subtotal": 30000,
      "total_items": 2,
      "shipping": 0,
      "total": 30000
    }
  }
}
```

#### Add Item to Cart
```http
POST /api/cart
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "variant_id": 2,
  "quantity": 1
}
```

#### Update Cart Item
```http
PUT /api/cart/{cart_item_id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantity": 3
}
```

#### Remove Cart Item
```http
DELETE /api/cart/{cart_item_id}
Authorization: Bearer {token}
```

#### Clear Cart
```http
DELETE /api/cart
Authorization: Bearer {token}
```

#### Get Cart Count
```http
GET /api/cart/count
Authorization: Bearer {token}
```

### Favorites Management

#### Get Favorites
```http
GET /api/favorites
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "created_at": "2025-08-09T12:00:00.000000Z",
      "product": {
        "id": 1,
        "name": "Fresh Tomatoes",
        "description": "Organic tomatoes...",
        "price": 12000,
        "discount_price": null,
        "image_url": "storage/products/tomatoes.jpg",
        "is_active": true,
        "category": {
          "id": 1,
          "name": "Vegetables"
        },
        "variants": [
          {
            "id": 1,
            "type": "size",
            "value": "500g",
            "unit": "g",
            "price": 12000
          }
        ]
      }
    }
  ]
}
```

#### Add to Favorites
```http
POST /api/favorites
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1
}
```

#### Remove from Favorites
```http
DELETE /api/favorites/{favorite_id}
Authorization: Bearer {token}
```

#### Toggle Favorite
```http
POST /api/favorites/toggle
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1
}
```

#### Check if Product is Favorite
```http
GET /api/favorites/check/{product_id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "is_favorite": true
  }
}
```

### Orders
For future implementation:
- Orders: `GET /api/orders`, `POST /api/orders`

### Email Verification

#### Generate and Send Verification Code
```http
POST /api/email/generate-code
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "New verification code sent successfully",
  "data": {
    "expires_in_minutes": 10
  }
}
```

#### Send Custom Verification Code
```http
POST /api/send-verification-email
Content-Type: application/json

{
  "email": "user@example.com",
  "verification_code": "1234"
}
```

#### Verify Email Code
```http
POST /api/verify-email-code
Content-Type: application/json

{
  "email": "user@example.com",
  "verification_code": "1234"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Invalid or expired verification code"
}
```

#### Check Verification Status
```http
POST /api/email/check-status
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "has_active_verification": true,
    "email": "user@example.com"
  }
}
```

## Development Token
For development and testing, you can use this authentication token:

**Token:** `4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec`

**Test Account:**
- Email: `test@example.com`
- Password: `password123`

Use this token in the `Authorization` header: `Bearer 4|k6WiTK9a3RSMbUj51yxVJIqxKhVehj2xa8GpsHkh5769faec`


---

**Integration Goal**: Replace all hardcoded product data with real API calls using this exact JSON structure and business logic rules.

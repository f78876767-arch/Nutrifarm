# J&T Express API Frontend Integration Guide

## Overview
This guide provides complete instructions for integrating J&T Express shipping APIs in the Nutrifarm frontend application. All backend APIs are fully implemented and tested.

## Available APIs

### 1. Tariff Check API ✅
**Purpose**: Get shipping cost estimates  
**Endpoint**: `POST /api/shipping/jnt/tariff`  
**Status**: Ready for integration

### 2. Create Order API ✅
**Purpose**: Create shipping orders and get AWB numbers  
**Endpoint**: `POST /api/shipping/jnt/order/create`  
**Status**: Ready for integration

### 3. Tracking API ✅
**Purpose**: Track shipment status and history  
**Endpoint**: `POST /api/shipping/jnt/track`  
**Status**: Ready for integration

---

## 1. Tariff Check API Integration

### Frontend Implementation Requirements

#### API Call
```javascript
// Tariff check request
const checkShippingCost = async (origin, destination, weight) => {
  try {
    const response = await fetch('/api/shipping/jnt/tariff', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        sendSiteCode: origin,      // e.g., "JAKARTA"
        destAreaCode: destination, // e.g., "KALIDERES" 
        weight: weight            // e.g., 2.0
      })
    });
    
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Tariff check failed:', error);
    throw error;
  }
};
```

#### Expected Response
```javascript
{
  "content": [
    {
      "cost": "10000",
      "name": "EZ",
      "productType": "EZ"
    }
  ],
  "is_success": "true",
  "message": ""
}
```

#### UI Integration Points
1. **Product Page**: Show shipping cost calculation
2. **Cart Page**: Display shipping options and costs
3. **Checkout**: Final cost confirmation

#### UI Components Needed
- [ ] Shipping cost calculator component
- [ ] Origin/destination city selector (dropdown with J&T city codes)
- [ ] Weight input with product weight calculation
- [ ] Real-time cost display as user changes options

---

## 2. Create Order API Integration

### Frontend Implementation Requirements

#### API Call
```javascript
// Create shipping order
const createShippingOrder = async (orderData) => {
  try {
    const response = await fetch('/api/shipping/jnt/order/create', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(orderData)
    });
    
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Order creation failed:', error);
    throw error;
  }
};
```

#### Required Request Format
```javascript
{
  "order_no": "NF-20250915141834",  // Unique order number
  "shipper": {
    "name": "Nutrifarm Official Store",
    "phone": "+6281234567890",
    "area": "JKT",                   // 3-letter J&T city code
    "address": "Jl. Sudirman No. 123, Jakarta Pusat",
    "postcode": "10110"
  },
  "receiver": {
    "name": "Customer Test",
    "phone": "+6281987654321", 
    "area": "JKT",                   // 3-letter J&T city code
    "address": "Jl. Raya Kalideres No. 456, Jakarta Barat",
    "postcode": "11840"
  },
  "goods": [
    {
      "name": "Pupuk Organik Nutrifarm Premium",
      "qty": 2,
      "weight": 1.5,
      "value": 75000
    }
  ],
  "service_type": "EZ",             // "EZ" for regular service
  "cod": 0,                         // Cash on delivery amount
  "insurance": 0,                   // Insurance amount
  "remark": "Test order creation from Nutrifarm"
}
```

#### Expected Response
```javascript
{
  "success": true,
  "desc": "Request berhasil",
  "detail": [
    {
      "orderid": "NF-20250915141834",
      "status": "Sukses",
      "awb_no": "JO9001515746",      // Important: Save this AWB number!
      "desCode": "555-567",
      "etd": "No Data"
    }
  ]
}
```

#### UI Integration Points
1. **Checkout Completion**: Create order after payment success
2. **Order Confirmation**: Display AWB number to customer
3. **Order Management**: Store AWB for tracking

#### UI Components Needed
- [ ] Order confirmation page with AWB display
- [ ] Shipping address form validation
- [ ] Phone number format validation (+62 format)
- [ ] Order number generation (prefix: NF-)
- [ ] Success/error handling for order creation

---

## 3. Tracking API Integration

### Frontend Implementation Requirements

#### API Call
```javascript
// Track shipment
const trackShipment = async (awbNumber) => {
  try {
    const response = await fetch('/api/shipping/jnt/track', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        awb: awbNumber  // e.g., "JO9001515746"
      })
    });
    
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Tracking failed:', error);
    throw error;
  }
};
```

#### Expected Response
```javascript
{
  "awb": "JO9001515746",
  "orderid": "NF-20250915141834",
  "detail": {
    "actual_amount": 0,
    "itemname": "Pupuk Organik Nutrifarm Premium",
    "qty": 2,
    "weight": 0,
    "sender": {
      "name": "Nutrifarm Official Store",
      "addr": "Jl. Sudirman No. 123, Jakarta Pusat",
      "city": "JAKARTA",
      "zipcode": ""
    },
    "receiver": {
      "name": "Customer Test", 
      "addr": "Jl. Raya Kalideres No. 456, Jakarta Barat",
      "city": "JAKARTA",
      "zipcode": "11840"
    },
    "delivDriver": {
      "name": "",
      "phone": ""
    }
  },
  "history": [
    {
      "date_time": "2025-09-15 14:18:37",
      "city_name": "JAKARTA",
      "status": "Manifes",
      "status_code": 101,
      "note": ""
    }
  ]
}
```

#### Status Code Reference
| Code | Status | Description |
|------|--------|-------------|
| 101 | Manifes | Order has been created |
| 100 | Package picked up | Package collected by J&T |
| 100 | In transit | Package in transit to destination |
| 100 | Out for delivery | Package being delivered |
| 150 | On hold | Problem with shipment |
| 200 | Delivered | Package delivered successfully |
| 401 | Return | Package being returned |

#### UI Integration Points
1. **Order History**: Track button for each order
2. **Tracking Page**: Dedicated tracking interface
3. **Customer Dashboard**: Quick status overview

#### UI Components Needed
- [ ] Tracking input form (AWB number entry)
- [ ] Timeline component for shipment history
- [ ] Status badge component with color coding
- [ ] Delivery details display
- [ ] Driver contact information (when available)
- [ ] Real-time status updates

---

## Implementation Steps

### Phase 1: Basic Integration
1. **Set up API service layer** in frontend
   - Create `jntService.js` with all three API methods
   - Add error handling and loading states
   - Configure base URL and headers

2. **Implement tariff check**
   - Add to product pages and cart
   - Create city selector component
   - Show real-time shipping costs

### Phase 2: Order Creation
3. **Integrate order creation**
   - Connect to checkout flow
   - Validate all required fields
   - Handle success/error responses
   - Store AWB numbers in orders

### Phase 3: Tracking System
4. **Build tracking interface**
   - Create tracking page
   - Add to order history
   - Implement status timeline
   - Add customer notifications

### Phase 4: Enhancements
5. **Advanced features**
   - Real-time tracking updates
   - Email notifications with tracking links
   - Mobile-responsive tracking page
   - Bulk order management

---

## Required Frontend Libraries/Dependencies

```json
{
  "suggested": {
    "@tanstack/react-query": "^4.0.0",  // For API state management
    "react-hook-form": "^7.0.0",        // For form handling
    "date-fns": "^2.0.0",               // For date formatting
    "react-router-dom": "^6.0.0"        // For routing to tracking pages
  }
}
```

---

## Testing Data

### Test Credentials (Demo Environment)
- **Origin**: JAKARTA, KALIDERES, BANDUNG
- **Destination**: JAKARTA, KALIDERES, SURABAYA
- **Test Weight**: 1.0 - 5.0 kg
- **Test AWB**: JO9001515746 (from successful test order)

### Sample Test Cases
1. **Tariff Check**: JAKARTA → KALIDERES, 2kg = IDR 10,000
2. **Order Creation**: Use test data provided in API format
3. **Tracking**: Track AWB JO9001515746 for full response

---

## Error Handling

### Common Error Responses
```javascript
// Tariff API error
{
  "is_success": "false",
  "message": "Invalid area code"
}

// Order API error  
{
  "success": false,
  "desc": "Kesalahan pada parameter"
}

// Tracking API error
{
  "error_id": "404",
  "error_message": "Invalid AWB number"
}
```

### Frontend Error Handling Strategy
- Show user-friendly error messages
- Provide retry mechanisms
- Log errors for debugging
- Fallback to alternative shipping methods if needed

---

## Security Considerations

1. **No Authentication Required**: All three APIs are public endpoints
2. **Rate Limiting**: Implement frontend rate limiting for API calls
3. **Input Validation**: Validate all input data before sending to API
4. **Error Logging**: Log errors without exposing sensitive data

---

## Next Steps

1. **Review this guide** and plan implementation phases
2. **Set up development environment** with API access
3. **Create API service layer** with proper error handling
4. **Implement UI components** following the specifications
5. **Test with provided test data** before production
6. **Add monitoring** for API performance and errors

---

## Support

- **Backend APIs**: All endpoints tested and ready
- **Test Environment**: Available on demo J&T servers
- **Documentation**: Complete with request/response examples
- **Error Handling**: Comprehensive error scenarios covered

**Ready for frontend implementation! 🚀**

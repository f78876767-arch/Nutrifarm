# Xendit Checkout Integration

This document explains how the Xendit payment integration works in the Nutrifarm Mobile app.

## Overview

The checkout flow integrates with Xendit payment gateway to handle online payments. When users click "Bayar" in the cart, the app creates a Xendit invoice request to the backend which generates a payment URL.

## Implementation Details

### Files Modified/Created:

1. **`lib/services/api_service.dart`**
   - Added `createXenditPayment()` method that calls `/api/payments/xendit/create` endpoint
   - Sends amount, external_id, payer_email, and description to backend
   - Expects Xendit invoice data with invoice_url in response

2. **`lib/services/checkout_service.dart`** (New)
   - Handles checkout flow and state management
   - Generates unique external_id for each order
   - Opens Xendit invoice URL in external browser
   - Manages loading states and error handling

3. **`lib/pages/cart_page.dart`**
   - Updated "Bayar" button to use CheckoutService
   - Gets user email from AuthService
   - Shows loading indicator during checkout process
   - Displays success/error messages

4. **`lib/pages/checkout_result_page.dart`** (New)
   - Result page to show payment success/failure
   - Handles return from Xendit payment
   - Navigation to orders or back to cart

5. **`lib/main.dart`**
   - Added CheckoutService to provider list
   - Added route for checkout result page

### Backend Endpoint Structure:

```http
POST /api/payments/xendit/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 50000,
  "external_id": "nutrifarm-order-1692892800000",
  "payer_email": "user@email.com",  
  "description": "Nutrifarm Order (3 items) - Optional notes"
}
```

### Expected Response:

```json
{
  "id": "inv-123456",
  "external_id": "nutrifarm-order-1692892800000", 
  "amount": 50000,
  "status": "PENDING",
  "invoice_url": "https://checkout.xendit.co/invoice/inv-123456",
  "expiry_date": "2025-08-13T12:00:00Z"
}
```

### Flow Diagram:

1. User clicks "Bayar" in cart
2. App gets user email from AuthService
3. App calculates cart total and generates unique external_id
4. App calls `ApiService.createXenditPayment()` with payment details
5. Backend creates Xendit invoice and returns invoice data
6. App opens invoice_url in browser using `url_launcher`
7. User completes payment on Xendit
8. Xendit sends webhook to `/api/payments/xendit/callback`
9. App shows checkout result page (if implemented)
10. Cart is cleared on successful payment

### Parameters Generated:

- **amount**: Cart subtotal converted to integer (smallest currency unit)
- **external_id**: Unique identifier in format `nutrifarm-order-{timestamp}`
- **payer_email**: User's email from AuthService
- **description**: Order description with item count and optional notes

### Error Handling:

- Network errors are caught and displayed to user
- Invalid responses from backend show appropriate error messages
- Loading states prevent multiple checkout attempts
- Failed payments allow retry from cart
- User email validation before payment processing

### Security Considerations:

- All API calls include Sanctum authentication token
- Payment processing happens on secure Xendit servers
- No sensitive payment data stored in app
- Backend validates user permissions and cart contents
- Webhook endpoint handles payment status updates securely

### Testing:

To test the integration:

1. Add items to cart
2. Make sure user is logged in (email available)
3. Click "Bayar" button
4. Verify Xendit invoice creation request is sent to backend
5. Check that Xendit invoice URL opens in browser
6. Test payment flow on Xendit checkout page
7. Verify webhook callback handling on backend
8. Test both success and failure scenarios

### Webhook Integration:

The backend webhook endpoint `/api/payments/xendit/callback` receives payment status updates from Xendit. This is handled automatically by your Laravel backend and doesn't require mobile app changes.

Common webhook events:
- `invoice.paid` - Payment completed successfully
- `invoice.expired` - Payment expired
- `invoice.failed` - Payment failed

### Dependencies:

- `url_launcher: ^6.3.1` - For opening payment URLs
- `provider: ^6.1.2` - For state management  
- `http: ^1.1.0` - For API calls

## Usage Instructions:

1. Make sure your backend has the `/api/payments/xendit/create` endpoint implemented
2. Configure Xendit in your Laravel backend with proper API keys
3. Set up Sanctum authentication tokens
4. Configure webhook URL in Xendit dashboard: `{your_domain}/api/payments/xendit/callback`
5. Test the complete flow from cart to payment completion

The integration now matches your specific Laravel Xendit implementation!

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../services/cart_service.dart';
import '../services/order_service.dart';
import '../widgets/skeleton_loading.dart';
import 'package:google_fonts/google_fonts.dart';

class CartPageNew extends StatefulWidget {
  const CartPageNew({super.key});

  @override
  State<CartPageNew> createState() => _CartPageNewState();
}

class _CartPageNewState extends State<CartPageNew> {
  String selectedAddress = 'Jl. Kebon Jeruk No. 123, Jakarta Barat';
  String selectedPaymentMethod = 'COD';
  bool _isProcessing = false;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    // Simulate initial loading
    Future.delayed(Duration(milliseconds: 800), () {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        scrolledUnderElevation: 1,
        automaticallyImplyLeading: false,
        title: Text(
          'Keranjang',
          style: GoogleFonts.nunitoSans(
            fontSize: 24,
            fontWeight: FontWeight.w700,
            color: AppColors.primaryGreen,
          ),
        ),
        actions: [
          Consumer<CartService>(
            builder: (context, cartService, child) {
              if (cartService.itemCount == 0) return const SizedBox();
              return TextButton(
                onPressed: () => _showClearCartDialog(context, cartService),
                child: Text(
                  'Clear All',
                  style: GoogleFonts.nunitoSans(
                    color: AppColors.error,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              );
            },
          ),
        ],
      ),
      body: Consumer<CartService>(
        builder: (context, cartService, child) {
          if (_isLoading) {
            return CartSkeleton(itemCount: 3);
          }
          
          if (cartService.itemCount == 0) {
            return _buildEmptyCart();
          }
          
          const double deliveryFee = 15000;
          const double serviceFee = 0;
          final double total = cartService.subtotal + deliveryFee + serviceFee;

          return Column(
            children: [
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Cart Items Section
                      _buildCartItemsSection(cartService),
                      const SizedBox(height: 20),
                      
                      // Delivery Address
                      _buildDeliveryAddressSection(),
                      const SizedBox(height: 20),
                      
                      // Order Summary
                      _buildOrderSummarySection(cartService, deliveryFee, serviceFee, total),
                    ],
                  ),
                ),
              ),
              
              // Checkout Button
              _buildCheckoutButton(context, cartService, total),
            ],
          );
        },
      ),
    );
  }

  Widget _buildEmptyCart() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 120,
            height: 120,
            decoration: BoxDecoration(
              color: AppColors.primaryGreen.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(
              Icons.shopping_cart_outlined,
              size: 60,
              color: AppColors.primaryGreen.withOpacity(0.6),
            ),
          ),
          const SizedBox(height: 24),
          Text(
            'Keranjang Kosong',
            style: GoogleFonts.nunitoSans(
              fontSize: 24,
              fontWeight: FontWeight.w700,
              color: AppColors.onSurface,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Tambahkan produk favorit Anda\nuntuk melanjutkan belanja',
            textAlign: TextAlign.center,
            style: GoogleFonts.nunitoSans(
              fontSize: 16,
              color: AppColors.onSurfaceVariant,
            ),
          ),
          const SizedBox(height: 32),
          ElevatedButton(
            onPressed: () => Navigator.pushReplacementNamed(context, '/home'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primaryGreen,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
            ),
            child: Text(
              'Mulai Belanja',
              style: GoogleFonts.nunitoSans(
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCartItemsSection(CartService cartService) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Keranjang Belanja',
                style: GoogleFonts.nunitoSans(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: AppColors.primaryGreen,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: AppColors.primaryGreen.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '${cartService.totalQuantity} items',
                  style: GoogleFonts.nunitoSans(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: AppColors.primaryGreen,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          ...cartService.items.map((item) => _buildCartItem(item, cartService)),
        ],
      ),
    );
  }

  Widget _buildCartItem(CartItem item, CartService cartService) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceVariant.withOpacity(0.3),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.outline.withOpacity(0.1)),
      ),
      child: Row(
        children: [
          // Product Image
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              color: AppColors.primaryGreen.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(
              Icons.local_pharmacy,
              color: AppColors.primaryGreen,
              size: 24,
            ),
          ),
          const SizedBox(width: 12),
          
          // Product Details
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.product.name,
                  style: GoogleFonts.nunitoSans(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.onSurface,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Text(
                      'Rp ${item.product.price.toStringAsFixed(0)}',
                      style: GoogleFonts.nunitoSans(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: AppColors.primaryGreen,
                      ),
                    ),
                    if (item.product.originalPrice != null && item.product.originalPrice! > item.product.price) ...[
                      const SizedBox(width: 8),
                      Text(
                        'Rp ${item.product.originalPrice!.toStringAsFixed(0)}',
                        style: GoogleFonts.nunitoSans(
                          fontSize: 12,
                          color: AppColors.onSurfaceVariant,
                          decoration: TextDecoration.lineThrough,
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
          
          // Quantity Controls
          Container(
            decoration: BoxDecoration(
              border: Border.all(color: AppColors.outline.withOpacity(0.3)),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              children: [
                GestureDetector(
                  onTap: () {
                    HapticFeedback.lightImpact();
                    cartService.decreaseQuantity(item.product.id);
                  },
                  child: Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: item.quantity > 1 ? AppColors.primaryGreen : AppColors.outline.withOpacity(0.3),
                      borderRadius: const BorderRadius.only(
                        topLeft: Radius.circular(8),
                        bottomLeft: Radius.circular(8),
                      ),
                    ),
                    child: Icon(
                      Icons.remove,
                      color: item.quantity > 1 ? Colors.white : AppColors.onSurfaceVariant,
                      size: 16,
                    ),
                  ),
                ),
                Container(
                  width: 40,
                  height: 32,
                  color: AppColors.surface,
                  child: Center(
                    child: Text(
                      '${item.quantity}',
                      style: GoogleFonts.nunitoSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: AppColors.onSurface,
                      ),
                    ),
                  ),
                ),
                GestureDetector(
                  onTap: () {
                    HapticFeedback.lightImpact();
                    cartService.increaseQuantity(item.product.id);
                  },
                  child: Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: item.quantity < item.product.stock 
                          ? AppColors.primaryGreen 
                          : AppColors.outline.withOpacity(0.3),
                      borderRadius: const BorderRadius.only(
                        topRight: Radius.circular(8),
                        bottomRight: Radius.circular(8),
                      ),
                    ),
                    child: Icon(
                      Icons.add,
                      color: item.quantity < item.product.stock 
                          ? Colors.white 
                          : AppColors.onSurfaceVariant,
                      size: 16,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDeliveryAddressSection() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Alamat Pengiriman',
            style: GoogleFonts.nunitoSans(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: AppColors.primaryGreen,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Icon(
                Icons.location_on_outlined,
                color: AppColors.primaryGreen,
                size: 20,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  selectedAddress,
                  style: GoogleFonts.nunitoSans(
                    fontSize: 14,
                    color: AppColors.onSurface,
                  ),
                ),
              ),
              TextButton(
                onPressed: () {
                  // TODO: Open address picker
                },
                child: Text(
                  'Ubah',
                  style: GoogleFonts.nunitoSans(
                    color: AppColors.primaryGreen,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildOrderSummarySection(CartService cartService, double deliveryFee, double serviceFee, double total) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Ringkasan Pesanan',
            style: GoogleFonts.nunitoSans(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: AppColors.primaryGreen,
            ),
          ),
          const SizedBox(height: 16),
          _buildSummaryRow('Subtotal (${cartService.totalQuantity} items)', 
              'Rp ${cartService.subtotal.toStringAsFixed(0)}'),
          if (cartService.totalSavings > 0)
            _buildSummaryRow('Hemat', '-Rp ${cartService.totalSavings.toStringAsFixed(0)}', 
                color: AppColors.success),
          _buildSummaryRow('Ongkos Kirim', 'Rp ${deliveryFee.toStringAsFixed(0)}'),
          if (serviceFee > 0)
            _buildSummaryRow('Biaya Layanan', 'Rp ${serviceFee.toStringAsFixed(0)}'),
          const Divider(height: 24, color: AppColors.outline),
          _buildSummaryRow('Total Pembayaran', 'Rp ${total.toStringAsFixed(0)}', 
              isTotal: true),
        ],
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value, {Color? color, bool isTotal = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: GoogleFonts.nunitoSans(
              fontSize: isTotal ? 16 : 14,
              fontWeight: isTotal ? FontWeight.w700 : FontWeight.w500,
              color: color ?? (isTotal ? AppColors.onSurface : AppColors.onSurfaceVariant),
            ),
          ),
          Text(
            value,
            style: GoogleFonts.nunitoSans(
              fontSize: isTotal ? 16 : 14,
              fontWeight: isTotal ? FontWeight.w700 : FontWeight.w600,
              color: color ?? (isTotal ? AppColors.primaryGreen : AppColors.onSurface),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCheckoutButton(BuildContext context, CartService cartService, double total) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: ElevatedButton(
        onPressed: _isProcessing ? null : () => _proceedToCheckout(context, cartService, total),
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primaryGreen,
          foregroundColor: Colors.white,
          disabledBackgroundColor: AppColors.outline,
          minimumSize: const Size(double.infinity, 56),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          elevation: 0,
        ),
        child: _isProcessing
            ? const SizedBox(
                width: 24,
                height: 24,
                child: CircularProgressIndicator(
                  color: Colors.white,
                  strokeWidth: 2,
                ),
              )
            : Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.payment_outlined, size: 20),
                  const SizedBox(width: 8),
                  Text(
                    'Bayar Sekarang - Rp ${total.toStringAsFixed(0)}',
                    style: GoogleFonts.nunitoSans(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
      ),
    );
  }

  void _showClearCartDialog(BuildContext context, CartService cartService) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          'Kosongkan Keranjang',
          style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700),
        ),
        content: Text(
          'Apakah Anda yakin ingin menghapus semua item dari keranjang?',
          style: GoogleFonts.nunitoSans(),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              'Batal',
              style: GoogleFonts.nunitoSans(color: AppColors.onSurfaceVariant),
            ),
          ),
          TextButton(
            onPressed: () {
              cartService.clearCart();
              Navigator.pop(context);
              HapticFeedback.lightImpact();
            },
            child: Text(
              'Hapus Semua',
              style: GoogleFonts.nunitoSans(color: AppColors.error),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _proceedToCheckout(BuildContext context, CartService cartService, double total) async {
    setState(() => _isProcessing = true);

    try {
      final orderService = Provider.of<OrderService>(context, listen: false);
      final orderId = await orderService.createOrder(
        deliveryAddress: selectedAddress,
        paymentMethod: selectedPaymentMethod,
      );

      if (mounted) {
        setState(() => _isProcessing = false);
        
        // Show success dialog
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            title: Row(
              children: [
                Icon(Icons.check_circle, color: AppColors.success),
                const SizedBox(width: 8),
                Text(
                  'Pesanan Berhasil!',
                  style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700),
                ),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Pesanan Anda telah berhasil dibuat',
                  style: GoogleFonts.nunitoSans(),
                ),
                const SizedBox(height: 8),
                Text(
                  'Order ID: $orderId',
                  style: GoogleFonts.nunitoSans(
                    fontWeight: FontWeight.w600,
                    color: AppColors.primaryGreen,
                  ),
                ),
              ],
            ),
            actions: [
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.pushReplacementNamed(context, '/home');
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primaryGreen,
                  foregroundColor: Colors.white,
                ),
                child: Text(
                  'Lihat Pesanan',
                  style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isProcessing = false);
        
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Terjadi kesalahan: $e',
              style: GoogleFonts.nunitoSans(color: Colors.white),
            ),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }
}

import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:shimmer/shimmer.dart';

import '../services/api_service.dart';
import '../theme/app_theme.dart';
import '../models/order.dart';
import 'order_detail_page.dart';
import '../services/event_bus.dart';
import 'package:google_fonts/google_fonts.dart';
import '../widgets/app_alert.dart';
import '../services/cart_service.dart';
import 'pdf_viewer_page.dart';
import '../widgets/main_navigator.dart';
import '../widgets/app_dialog.dart';

class OrderHistoryPage extends StatefulWidget {
  const OrderHistoryPage({super.key});

  @override
  State<OrderHistoryPage> createState() => _OrderHistoryPageState();
}

class _OrderHistoryPageState extends State<OrderHistoryPage> {
  List<Order> _orders = [];
  bool _isLoading = true;
  bool _isRefreshing = false;
  String? _error;
  StreamSubscription? _orderCreatedSub;
  // final ScrollController _scrollController = ScrollController();
  // bool _hasMore = true;
  // bool _isPaginating = false;

  @override
  void initState() {
    super.initState();
    _loadOrders();
    // _scrollController.addListener(_onScroll);
    // Listen for new order events to show immediately
    _orderCreatedSub = AppEventBus.I.onOrderCreated.listen((evt) {
      final newOrder = evt.order;
      // Avoid duplicates by id
      final exists = _orders.any((o) => o.id == newOrder.id);
      if (!exists) {
        setState(() {
          _orders = [newOrder, ..._orders];
        });
      }
      // Background refresh to reconcile with backend (status, urls)
      Future.delayed(const Duration(milliseconds: 500), _refreshOrders);
    });
  }

  @override
  void dispose() {
    _orderCreatedSub?.cancel();
    // _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadOrders({bool reset = false}) async {
    if (reset) {
      _orders = [];
    }
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await ApiService.getOrders();

      List<Order> orders = [];
      if (response is List) {
        orders = response
            .whereType<Map<String, dynamic>>()
            .map(Order.fromJson)
            .toList();
      } else if (response is Map) {
        dynamic candidate;
        // Flat lists
        if (response['data'] is List) candidate = response['data'];
        else if (response['orders'] is List) candidate = response['orders'];
        else if (response['items'] is List) candidate = response['items'];
        else if (response['results'] is List) candidate = response['results'];
        else if (response['records'] is List) candidate = response['records'];
        // 'orders' object with inner list
        else if (response['orders'] is Map) {
          final o = response['orders'] as Map;
          if (o['data'] is List) candidate = o['data'];
          else if (o['items'] is List) candidate = o['items'];
          else if (o['results'] is List) candidate = o['results'];
          else if (o['records'] is List) candidate = o['records'];
        }
        // Nested under data
        else if (response['data'] is Map) {
          final data = response['data'] as Map;
          if (data['data'] is List) candidate = data['data'];
          else if (data['orders'] is List) candidate = data['orders'];
          else if (data['items'] is List) candidate = data['items'];
          else if (data['results'] is List) candidate = data['results'];
          else if (data['records'] is List) candidate = data['records'];
          else if (data['orders'] is Map) {
            final o = data['orders'] as Map;
            if (o['data'] is List) candidate = o['data'];
            else if (o['items'] is List) candidate = o['items'];
            else if (o['results'] is List) candidate = o['results'];
            else if (o['records'] is List) candidate = o['records'];
          }
        }

        if (candidate is List) {
          orders = candidate
              .whereType<Map<String, dynamic>>()
              .map(Order.fromJson)
              .toList();
        }
      }

      orders.sort((a, b) => b.createdAt.compareTo(a.createdAt));
      // Debug counts per tab
      // ignore: avoid_print
      print('🧾 Orders mapped: ${orders.length}');

      setState(() {
        _orders = orders;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Gagal memuat pesanan: $e';
        _isLoading = false;
      });
    }
  }

  Future<void> _refreshOrders() async {
    setState(() => _isRefreshing = true);
    await _loadOrders(reset: true);
    setState(() => _isRefreshing = false);
  }

  String _formatCurrency(double amount) {
    final f = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);
    return f.format(amount);
  }

  String _formatDate(DateTime date) {
    return DateFormat('EEE, dd MMM yyyy HH:mm', 'id_ID').format(date);
  }

  Color _statusColor(String s) {
    switch (s.toLowerCase()) {
      case 'paid':
        return AppColors.success;
      case 'pending':
        return AppColors.warning;
      case 'failed':
        return AppColors.error;
      case 'expired':
        return AppColors.onSurfaceVariant;
      default:
        return AppColors.onSurfaceVariant;
    }
  }

  String _statusText(String s) {
    switch (s.toLowerCase()) {
      case 'paid':
        return 'Dibayar';
      case 'pending':
        return 'Menunggu Pembayaran';
      case 'failed':
        return 'Gagal';
      case 'expired':
        return 'Kedaluwarsa';
      default:
        return s;
    }
  }

  // --- Filtering helpers for tabs ---
  List<Order> _ordersForTab(int index) {
    switch (index) {
      case 1: // Menunggu Pembayaran
        return _orders.where((o) => o.paymentStatus.toLowerCase() == 'pending').toList();
      case 2: // Proses (confirmed/processing/shipped)
        return _orders.where((o) {
          final s = o.status.toLowerCase();
          return s == 'confirmed' || s == 'processing' || s == 'shipped';
        }).toList();
      case 3: // Selesai (delivered or paid)
        return _orders.where((o) => o.status.toLowerCase() == 'delivered' || o.paymentStatus.toLowerCase() == 'paid').toList();
      case 4: // Batal (cancelled/refunded/failed/expired)
        return _orders.where((o) {
          final s = o.status.toLowerCase();
          final ps = o.paymentStatus.toLowerCase();
          return s == 'cancelled' || s == 'refunded' || ps == 'failed' || ps == 'expired';
        }).toList();
      case 0: // Semua
      default:
        return _orders;
    }
  }

  Widget _skeletonCard() {
    final theme = Theme.of(context);
    final base = theme.brightness == Brightness.dark ? Colors.grey.shade800 : Colors.grey.shade300;
    final highlight = theme.brightness == Brightness.dark ? Colors.grey.shade700 : Colors.grey.shade100;
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Shimmer.fromColors(
          baseColor: base,
          highlightColor: highlight,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(height: 16, width: 180, color: Colors.white.withOpacity(0.2)),
              const SizedBox(height: 10),
              Container(height: 12, width: 120, color: Colors.white.withOpacity(0.2)),
              const SizedBox(height: 14),
              Row(
                children: [
                  Container(height: 44, width: 44, color: Colors.white.withOpacity(0.2)),
                  const SizedBox(width: 12),
                  Expanded(child: Container(height: 14, color: Colors.white.withOpacity(0.2))),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: Container(height: 36, color: Colors.white.withOpacity(0.2))),
                  const SizedBox(width: 12),
                  Expanded(child: Container(height: 36, color: Colors.white.withOpacity(0.2))),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildOrdersList(List<Order> orders) {
    final theme = Theme.of(context);
    if (orders.isEmpty) {
      return RefreshIndicator(
        onRefresh: _refreshOrders,
        color: AppColors.primaryGreen,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(vertical: 48),
          children: [
            const SizedBox(height: 60),
            Icon(Icons.shopping_bag_outlined, size: 64, color: theme.colorScheme.onSurfaceVariant),
            const SizedBox(height: 12),
            Center(child: Text('Belum ada pesanan', style: GoogleFonts.nunitoSans(fontSize: 18, fontWeight: FontWeight.w600, color: theme.colorScheme.onSurface))),
            const SizedBox(height: 6),
            Center(child: Text('Pesanan Anda akan muncul di sini', style: GoogleFonts.nunitoSans(color: theme.colorScheme.onSurfaceVariant))),
          ],
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: _refreshOrders,
      color: AppColors.primaryGreen,
      child: ListView.builder(
        primary: true,
        physics: const BouncingScrollPhysics(),
        itemCount: orders.length,
        itemBuilder: (context, index) => _orderCard(orders[index]),
      ),
    );
  }

  Future<void> _openUrl(String url) async {
    try {
      final uri = Uri.parse(url);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (!mounted) return;
        AppAlert.showError(context, 'Tidak bisa membuka tautan');
      }
    } catch (e) {
      if (!mounted) return;
      AppAlert.showError(context, 'Gagal membuka URL: $e');
    }
  }

  Future<void> _payNow(Order order) async {
    if (order.xenditInvoiceUrl == null || order.xenditInvoiceUrl!.isEmpty) {
      if (!mounted) return;
      AppAlert.showInfo(context, 'Invoice belum tersedia');
      return;
    }
    await _openUrl(order.xenditInvoiceUrl!);
    // Optional: short re-fetch to make sure entry exists/updated
    Future.delayed(const Duration(seconds: 2), _refreshOrders);
  }

  Widget? _secondaryAction(Order order) {
    final ps = order.paymentStatus.toLowerCase();
    final isCompleted = order.status.toLowerCase() == 'delivered' || ps == 'paid';
    if (ps == 'pending') {
      return ElevatedButton(
        onPressed: () => _payNow(order),
        style: ElevatedButton.styleFrom(backgroundColor: AppColors.primaryGreen, foregroundColor: Colors.white),
        child: const Text('Bayar Sekarang'),
      );
    }
    if (isCompleted) {
      return OutlinedButton(
        onPressed: () => _reorder(order),
        child: const Text('Beli Lagi'),
      );
    }
    return null;
  }

  Future<void> _reorder(Order order) async {
    final clearFirst = await AppDialog.showConfirm(
      context,
      title: 'Beli Lagi',
      message: 'Tambahkan semua item pesanan ini ke keranjang?\n\nIngin kosongkan keranjang terlebih dahulu?',
      confirmText: 'Kosongkan & Tambah',
      cancelText: 'Langsung Tambah',
      destructive: true,
    );
    try {
      final cart = CartService();
      if (clearFirst == true) {
        await cart.clearCart();
      }
      for (final op in order.orderProducts) {
        await cart.addToCart(op.product, quantity: op.quantity, variant: op.variant);
      }
      if (!mounted) return;
      AppAlert.showSuccess(context, 'Produk ditambahkan ke keranjang');
      // Navigate to Cart tab (index 2) on main navigator
      await Future.delayed(const Duration(milliseconds: 250));
      Navigator.of(context).popUntil((route) => route.isFirst);
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const MainNavigator(initialIndex: 2)),
      );
    } catch (e) {
      if (!mounted) return;
      AppAlert.showError(context, 'Gagal menambahkan ke keranjang: $e');
    }
  }

  Widget _orderCard(Order order) {
    final theme = Theme.of(context);
    final totalItems = order.orderProducts.fold<int>(0, (s, it) => s + it.quantity);
    final color = _statusColor(order.paymentStatus);
    final status = _statusText(order.paymentStatus);

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    'Order No: ${order.externalId ?? '#${order.id}'}',
                    style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700, fontSize: 16, color: theme.colorScheme.onSurface),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: color.withOpacity(0.25)),
                  ),
                  child: Text(status, style: GoogleFonts.nunitoSans(color: color, fontSize: 12, fontWeight: FontWeight.w600)),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              _formatDate(order.createdAt),
              style: GoogleFonts.nunitoSans(color: Theme.of(context).colorScheme.onSurfaceVariant, fontSize: 12),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('$totalItems item${totalItems > 1 ? 's' : ''}', style: GoogleFonts.nunitoSans(color: theme.colorScheme.onSurfaceVariant)),
                Text(
                  _formatCurrency(order.total),
                  style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700, fontSize: 16, color: AppColors.primaryGreen),
                ),
              ],
            ),
            // --- Product thumbnails beside names ---
            if (order.orderProducts.isNotEmpty) ...[
              const SizedBox(height: 12),
              Column(
                children: order.orderProducts.map((op) {
                  final imageUrl = op.product.imageUrl;
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Row(
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: imageUrl.isNotEmpty
                              ? Image.network(
                                  imageUrl,
                                  width: 44,
                                  height: 44,
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) => Container(
                                    width: 44,
                                    height: 44,
                                    color: Colors.grey[200],
                                    child: Icon(Icons.image_not_supported, size: 18, color: Colors.grey[500]),
                                  ),
                                )
                              : Container(
                                  width: 44,
                                  height: 44,
                                  color: Colors.grey[200],
                                  child: Icon(Icons.shopping_bag, color: theme.colorScheme.onSurfaceVariant),
                                ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            op.displayName,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.nunitoSans(fontSize: 14, fontWeight: FontWeight.w600, color: theme.colorScheme.onSurface),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text('x${op.quantity}', style: GoogleFonts.nunitoSans(color: theme.colorScheme.onSurfaceVariant, fontSize: 12)),
                      ],
                    ),
                  );
                }).toList(),
              ),
            ],
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => OrderDetailPage(orderId: order.id)),
                      ).then((_) => _refreshOrders());
                    },
                    style: OutlinedButton.styleFrom(side: const BorderSide(color: AppColors.primaryGreen)),
                    child: const Text('Lihat Detail', style: TextStyle(color: AppColors.primaryGreen)),
                  ),
                ),
                const SizedBox(width: 12),
                if (_secondaryAction(order) != null)
                  Expanded(child: _secondaryAction(order)!),
              ],
            ),
            if (order.invoicePdfUrl != null || (order.paymentStatus.toLowerCase() == 'paid' && order.receiptPdfUrl != null)) ...[
              const SizedBox(height: 10),
              Row(
                children: [
                  if (order.invoicePdfUrl != null)
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => PdfViewerPage(url: order.invoicePdfUrl!, title: 'Invoice'),
                            ),
                          );
                        },
                        icon: const Icon(Icons.description_outlined),
                        label: const Text('Invoice'),
                      ),
                    ),
                  if (order.invoicePdfUrl != null && order.paymentStatus.toLowerCase() == 'paid' && order.receiptPdfUrl != null)
                    const SizedBox(width: 12),
                  if (order.paymentStatus.toLowerCase() == 'paid' && order.receiptPdfUrl != null)
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => PdfViewerPage(url: order.receiptPdfUrl!, title: 'Receipt'),
                            ),
                          );
                        },
                        icon: const Icon(Icons.receipt_outlined),
                        label: const Text('Receipt'),
                      ),
                    ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final overlay = isDark ? SystemUiOverlayStyle.light : SystemUiOverlayStyle.dark;

    return DefaultTabController(
      length: 5,
      child: Scaffold(
        appBar: AppBar(
          backgroundColor: theme.colorScheme.surface,
          elevation: 0,
          systemOverlayStyle: overlay,
          centerTitle: true,
          title: Text(
            'Riwayat Pesanan',
            style: GoogleFonts.nunitoSans(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: theme.colorScheme.onSurface,
            ),
          ),
          actions: [
            IconButton(
              icon: Icon(Icons.refresh, color: theme.brightness == Brightness.dark ? Colors.white : theme.colorScheme.onSurface),
              onPressed: () => _refreshOrders(),
            ),
          ],
          bottom: TabBar(
            isScrollable: true,
            indicatorColor: AppColors.primaryGreen,
            labelColor: theme.brightness == Brightness.dark ? Colors.white : AppColors.primaryGreen,
            unselectedLabelColor: theme.colorScheme.onSurfaceVariant,
            labelStyle: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700),
            tabs: const [
              Tab(text: 'Semua'),
              Tab(text: 'Menunggu'),
              Tab(text: 'Proses'),
              Tab(text: 'Selesai'),
              Tab(text: 'Batal'),
            ],
          ),
        ),
        body: _isLoading && !_isRefreshing
            ? ListView.builder(
                padding: const EdgeInsets.only(top: 12),
                itemCount: 6,
                itemBuilder: (_, __) => _skeletonCard(),
              )
            : _error != null
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.error_outline, size: 60, color: AppColors.error),
                          const SizedBox(height: 14),
                          Text('Gagal memuat riwayat pesanan', style: GoogleFonts.nunitoSans(fontSize: 18, fontWeight: FontWeight.w600, color: theme.colorScheme.onSurface)),
                          const SizedBox(height: 8),
                          Text(_error!, style: GoogleFonts.nunitoSans(color: theme.colorScheme.onSurfaceVariant), textAlign: TextAlign.center),
                          const SizedBox(height: 20),
                          ElevatedButton(onPressed: _loadOrders, child: const Text('Coba Lagi')),
                        ],
                      ),
                    ),
                  )
                : TabBarView(
                    children: [
                      KeyedSubtree(key: const PageStorageKey('tab_all'), child: _buildOrdersList(_ordersForTab(0))),
                      KeyedSubtree(key: const PageStorageKey('tab_pending'), child: _buildOrdersList(_ordersForTab(1))),
                      KeyedSubtree(key: const PageStorageKey('tab_processing'), child: _buildOrdersList(_ordersForTab(2))),
                      KeyedSubtree(key: const PageStorageKey('tab_completed'), child: _buildOrdersList(_ordersForTab(3))),
                      KeyedSubtree(key: const PageStorageKey('tab_cancelled'), child: _buildOrdersList(_ordersForTab(4))),
                    ],
                  ),
      ),
    );
  }
}

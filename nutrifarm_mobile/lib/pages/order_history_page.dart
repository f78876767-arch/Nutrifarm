import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import '../theme/app_theme.dart';
import '../widgets/skeleton_loading.dart';

class OrderHistoryPage extends StatefulWidget {
  const OrderHistoryPage({super.key});

  @override
  _OrderHistoryPageState createState() => _OrderHistoryPageState();
}

class _OrderHistoryPageState extends State<OrderHistoryPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 5, vsync: this);
    
    // Simulate loading orders
    Future.delayed(Duration(milliseconds: 1000), () {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    });
  }

  final List<Order> _orders = [
    Order(
      id: 'NF2024001',
      date: DateTime.now().subtract(const Duration(days: 1)),
      status: OrderStatus.delivered,
      total: 125000,
      items: [
        OrderItem(name: 'Virgin Coconut Oil 500ml', quantity: 2, price: 45000),
        OrderItem(name: 'Madu Bunga Kelengkeng 650ml', quantity: 1, price: 35000),
      ],
    ),
    Order(
      id: 'NF2024002',
      date: DateTime.now().subtract(const Duration(days: 3)),
      status: OrderStatus.shipping,
      total: 89000,
      items: [
        OrderItem(name: 'Cuka Apel 250ml', quantity: 3, price: 25000),
        OrderItem(name: 'Organic Honey 300ml', quantity: 1, price: 14000),
      ],
    ),
    Order(
      id: 'NF2024003',
      date: DateTime.now().subtract(const Duration(days: 7)),
      status: OrderStatus.processing,
      total: 67000,
      items: [
        OrderItem(name: 'Extra Virgin Olive Oil', quantity: 1, price: 52000),
        OrderItem(name: 'Delivery Fee', quantity: 1, price: 15000),
      ],
    ),
    Order(
      id: 'NF2024004',
      date: DateTime.now().subtract(const Duration(days: 14)),
      status: OrderStatus.cancelled,
      total: 156000,
      items: [
        OrderItem(name: 'Health Product Bundle', quantity: 2, price: 78000),
      ],
    ),
  ];

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        scrolledUnderElevation: 1,
        leading: IconButton(
          icon: const Icon(FeatherIcons.arrowLeft, color: AppColors.primaryGreen),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Riwayat Pesanan',
          style: GoogleFonts.nunitoSans(
            fontSize: 24,
            fontWeight: FontWeight.w700,
            color: AppColors.primaryGreen,
          ),
        ),
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          indicatorColor: AppColors.primaryGreen,
          labelColor: AppColors.primaryGreen,
          unselectedLabelColor: AppColors.onSurfaceVariant,
          labelStyle: GoogleFonts.nunitoSans(
            fontSize: 14,
            fontWeight: FontWeight.w600,
          ),
          unselectedLabelStyle: GoogleFonts.nunitoSans(
            fontSize: 14,
            fontWeight: FontWeight.w500,
          ),
          tabs: const [
            Tab(text: 'Semua'),
            Tab(text: 'Diproses'),
            Tab(text: 'Dikirim'),
            Tab(text: 'Selesai'),
            Tab(text: 'Dibatalkan'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _isLoading ? _buildOrderListSkeleton() : _buildOrderList(_orders),
          _isLoading ? _buildOrderListSkeleton() : _buildOrderList(_orders.where((o) => o.status == OrderStatus.processing).toList()),
          _isLoading ? _buildOrderListSkeleton() : _buildOrderList(_orders.where((o) => o.status == OrderStatus.shipping).toList()),
          _isLoading ? _buildOrderListSkeleton() : _buildOrderList(_orders.where((o) => o.status == OrderStatus.delivered).toList()),
          _isLoading ? _buildOrderListSkeleton() : _buildOrderList(_orders.where((o) => o.status == OrderStatus.cancelled).toList()),
        ],
      ),
    );
  }

  Widget _buildOrderListSkeleton() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: 5,
      itemBuilder: (context, index) {
        return Container(
          margin: const EdgeInsets.only(bottom: 16),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 8,
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
                  SkeletonLoading(
                    width: 80,
                    height: 16,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  SkeletonLoading(
                    width: 60,
                    height: 20,
                    borderRadius: BorderRadius.circular(10),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              SkeletonLoading(
                width: 100,
                height: 12,
                borderRadius: BorderRadius.circular(4),
              ),
              const SizedBox(height: 12),
              ...List.generate(2, (i) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  children: [
                    SkeletonLoading(
                      width: 40,
                      height: 40,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          SkeletonLoading(
                            width: double.infinity,
                            height: 14,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          const SizedBox(height: 4),
                          SkeletonLoading(
                            width: 80,
                            height: 12,
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              )),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  SkeletonLoading(
                    width: 80,
                    height: 16,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  SkeletonLoading(
                    width: 80,
                    height: 32,
                    borderRadius: BorderRadius.circular(16),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildOrderList(List<Order> orders) {
    if (orders.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              FeatherIcons.package,
              size: 80,
              color: AppColors.onSurfaceVariant.withOpacity(0.5),
            ),
            const SizedBox(height: 16),
            Text(
              'Belum ada pesanan',
              style: GoogleFonts.nunitoSans(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppColors.onSurfaceVariant,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Pesanan Anda akan muncul di sini',
              style: GoogleFonts.nunitoSans(
                fontSize: 14,
                color: AppColors.onSurfaceVariant,
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: orders.length,
      itemBuilder: (context, index) {
        final order = orders[index];
        return _buildOrderCard(order);
      },
    );
  }

  Widget _buildOrderCard(Order order) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppColors.onSurface.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Order Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                order.id,
                style: GoogleFonts.nunitoSans(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: AppColors.onSurface,
                ),
              ),
              _buildStatusChip(order.status),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            _formatDate(order.date),
            style: GoogleFonts.nunitoSans(
              fontSize: 14,
              color: AppColors.onSurfaceVariant,
            ),
          ),
          const SizedBox(height: 12),

          // Order Items
          ...order.items.take(2).map((item) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  children: [
                    Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: AppColors.surfaceVariant,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Icon(
                        FeatherIcons.package,
                        color: AppColors.primaryGreen,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.name,
                            style: GoogleFonts.nunitoSans(
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                              color: AppColors.onSurface,
                            ),
                          ),
                          Text(
                            '${item.quantity}x • Rp ${_formatPrice(item.price)}',
                            style: GoogleFonts.nunitoSans(
                              fontSize: 12,
                              color: AppColors.onSurfaceVariant,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              )),

          if (order.items.length > 2)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Text(
                '+${order.items.length - 2} produk lainnya',
                style: GoogleFonts.nunitoSans(
                  fontSize: 14,
                  color: AppColors.primaryGreen,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),

          const Divider(height: 24),

          // Order Footer
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Total Pesanan',
                    style: GoogleFonts.nunitoSans(
                      fontSize: 12,
                      color: AppColors.onSurfaceVariant,
                    ),
                  ),
                  Text(
                    'Rp ${_formatPrice(order.total)}',
                    style: GoogleFonts.nunitoSans(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: AppColors.primaryGreen,
                    ),
                  ),
                ],
              ),
              Row(
                children: [
                  if (order.status == OrderStatus.delivered) ...[
                    OutlinedButton(
                      onPressed: () {
                        HapticFeedback.lightImpact();
                        _showReorderDialog(order);
                      },
                      style: OutlinedButton.styleFrom(
                        side: BorderSide(color: AppColors.primaryGreen),
                        foregroundColor: AppColors.primaryGreen,
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      ),
                      child: Text(
                        'Pesan Lagi',
                        style: GoogleFonts.nunitoSans(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                  ],
                  OutlinedButton(
                    onPressed: () {
                      HapticFeedback.lightImpact();
                      _showOrderDetails(order);
                    },
                    style: OutlinedButton.styleFrom(
                      side: BorderSide(color: AppColors.outline),
                      foregroundColor: AppColors.onSurface,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    ),
                    child: Text(
                      'Detail',
                      style: GoogleFonts.nunitoSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatusChip(OrderStatus status) {
    Color backgroundColor;
    Color textColor;
    String text;
    IconData icon;

    switch (status) {
      case OrderStatus.processing:
        backgroundColor = AppColors.warning.withOpacity(0.1);
        textColor = AppColors.warning;
        text = 'Diproses';
        icon = FeatherIcons.clock;
        break;
      case OrderStatus.shipping:
        backgroundColor = AppColors.blue.withOpacity(0.1);
        textColor = AppColors.blue;
        text = 'Dikirim';
        icon = FeatherIcons.truck;
        break;
      case OrderStatus.delivered:
        backgroundColor = AppColors.success.withOpacity(0.1);
        textColor = AppColors.success;
        text = 'Selesai';
        icon = FeatherIcons.checkCircle;
        break;
      case OrderStatus.cancelled:
        backgroundColor = AppColors.error.withOpacity(0.1);
        textColor = AppColors.error;
        text = 'Dibatalkan';
        icon = FeatherIcons.xCircle;
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: textColor),
          const SizedBox(width: 4),
          Text(
            text,
            style: GoogleFonts.nunitoSans(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: textColor,
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime date) {
    final months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }

  String _formatPrice(int price) {
    return price.toString().replaceAllMapped(
        RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.');
  }

  void _showOrderDetails(Order order) {
    // Show detailed order information
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        builder: (context, scrollController) => Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              // Handle
              Container(
                margin: const EdgeInsets.symmetric(vertical: 12),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.outline,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              // Content
              Expanded(
                child: SingleChildScrollView(
                  controller: scrollController,
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Detail Pesanan ${order.id}',
                        style: GoogleFonts.nunitoSans(
                          fontSize: 24,
                          fontWeight: FontWeight.w700,
                          color: AppColors.primaryGreen,
                        ),
                      ),
                      const SizedBox(height: 20),
                      // Add detailed order information here
                      Text(
                        'Status: ${_getStatusText(order.status)}',
                        style: GoogleFonts.nunitoSans(
                          fontSize: 16,
                          color: AppColors.onSurface,
                        ),
                      ),
                      Text(
                        'Tanggal: ${_formatDate(order.date)}',
                        style: GoogleFonts.nunitoSans(
                          fontSize: 16,
                          color: AppColors.onSurface,
                        ),
                      ),
                      const SizedBox(height: 20),
                      Text(
                        'Items:',
                        style: GoogleFonts.nunitoSans(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: AppColors.onSurface,
                        ),
                      ),
                      ...order.items.map((item) => ListTile(
                            title: Text(item.name),
                            subtitle: Text('${item.quantity}x'),
                            trailing: Text('Rp ${_formatPrice(item.price)}'),
                          )),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showReorderDialog(Order order) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Pesan Lagi',
          style: GoogleFonts.nunitoSans(
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: AppColors.onSurface,
          ),
        ),
        content: Text(
          'Tambahkan semua item dari pesanan ${order.id} ke keranjang?',
          style: GoogleFonts.nunitoSans(
            fontSize: 16,
            color: AppColors.onSurfaceVariant,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              'Batal',
              style: GoogleFonts.nunitoSans(
                color: AppColors.onSurfaceVariant,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              // Add items to cart
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(
                    'Item berhasil ditambahkan ke keranjang',
                    style: GoogleFonts.nunitoSans(),
                  ),
                  backgroundColor: AppColors.success,
                ),
              );
            },
            child: Text(
              'Tambahkan',
              style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  String _getStatusText(OrderStatus status) {
    switch (status) {
      case OrderStatus.processing:
        return 'Diproses';
      case OrderStatus.shipping:
        return 'Dikirim';
      case OrderStatus.delivered:
        return 'Selesai';
      case OrderStatus.cancelled:
        return 'Dibatalkan';
    }
  }
}

// Data Models
class Order {
  final String id;
  final DateTime date;
  final OrderStatus status;
  final int total;
  final List<OrderItem> items;

  Order({
    required this.id,
    required this.date,
    required this.status,
    required this.total,
    required this.items,
  });
}

class OrderItem {
  final String name;
  final int quantity;
  final int price;

  OrderItem({
    required this.name,
    required this.quantity,
    required this.price,
  });
}

enum OrderStatus { processing, shipping, delivered, cancelled }

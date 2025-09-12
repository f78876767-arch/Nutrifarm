import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../services/api_service.dart';
import '../theme/app_theme.dart';
import '../models/order.dart';
import '../models/product.dart';
import '../models/jnt_models.dart';

class OrderDetailPage extends StatefulWidget {
  final int orderId;

  const OrderDetailPage({
    super.key,
    required this.orderId,
  });

  @override
  State<OrderDetailPage> createState() => _OrderDetailPageState();
}

class _OrderDetailPageState extends State<OrderDetailPage> {
  Order? _order;
  bool _isLoading = true;
  String? _error;

  // J&T Tracking state
  bool _isTracking = false;
  String? _trackError;
  List<JntTrackEvent> _trackEvents = [];

  @override
  void initState() {
    super.initState();
    _loadOrderDetail();
  }

  Future<void> _loadOrderDetail() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final orderData = await ApiService.getOrderDetail(widget.orderId);
      setState(() {
        _order = Order.fromJson(orderData);
        _isLoading = false;
      });
      if (_order?.resi != null && _order!.resi!.isNotEmpty) {
        _loadTracking();
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _loadTracking() async {
    if (_order?.resi == null || _order!.resi!.isEmpty) return;
    setState(() { _isTracking = true; _trackError = null; });
    try {
      final events = await ApiService.jntTrack({ 'billcode': _order!.resi! });
      setState(() { _trackEvents = events; });
    } catch (e) {
      setState(() { _trackError = e.toString(); });
    } finally {
      if (mounted) setState(() { _isTracking = false; });
    }
  }

  Future<void> _createJntShipment() async {
    if (_order == null) return;
    final orderNo = _order!.externalId ?? 'NUT-${_order!.id.toString().padLeft(6, '0')}';
    // Try prefill from saved address
    Map<String, dynamic>? addr;
    try { addr = await ApiService.getUserShippingAddress(); } catch (_) {}

    final body = {
      'order_no': orderNo,
      'shipper': {
        'name': addr?['name'] ?? 'Nutrifarm',
        'phone': addr?['phone'] ?? '0800000000',
        'address': addr?['address'] ?? 'Alamat Toko',
        'city': addr?['city_name'] ?? addr?['city'] ?? 'Jakarta',
        'province': addr?['province_name'] ?? addr?['province'] ?? 'DKI Jakarta',
        'postal_code': addr?['postal_code'] ?? '10110',
      },
      'receiver': {
        'name': addr?['name'] ?? 'Pelanggan',
        'phone': addr?['phone'] ?? '0800000000',
        'address': addr?['address'] ?? 'Alamat Pelanggan',
        'city': addr?['city_name'] ?? 'Jakarta',
        'province': addr?['province_name'] ?? 'DKI Jakarta',
        'postal_code': addr?['postal_code'] ?? '10110',
      },
      'goods': _order!.orderProducts.map((it) => {
        'name': it.product.name,
        'qty': it.quantity,
        'weight': 500,
      }).toList(),
    };

    try {
      if (mounted) setState(() { _isLoading = true; });
      final result = await ApiService.jntCreateOrder(body);
      if (result.awb.isEmpty) throw Exception('AWB kosong dari J&T');
      final ok = await ApiService.updateOrderResi(_order!.id, resi: result.awb);
      if (!ok) throw Exception('Gagal menyimpan resi ke order');
      await _loadOrderDetail();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Resi dibuat: ${result.awb}')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal membuat pengiriman: $e')),
        );
      }
    } finally {
      if (mounted) setState(() { _isLoading = false; });
    }
  }

  Future<void> _cancelJntShipment() async {
    if (_order == null || _order!.externalId == null) return;
    try {
      if (mounted) setState(() { _isLoading = true; });
      final ok = await ApiService.jntCancelOrder({'order_no': _order!.externalId!});
      if (ok) {
        await ApiService.updateOrderResi(_order!.id, resi: '');
        await _loadOrderDetail();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Pengiriman dibatalkan')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal membatalkan: $e')),
        );
      }
    } finally {
      if (mounted) setState(() { _isLoading = false; });
    }
  }

  String _formatCurrency(double amount) {
    return NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    ).format(amount);
  }

  String _formatDate(DateTime date) {
    return DateFormat('EEEE, dd MMMM yyyy', 'id_ID').format(date.toLocal());
  }

  String _formatTime(DateTime date) {
    return DateFormat('HH:mm', 'id_ID').format(date.toLocal());
  }

  Color _getStatusColor(String paymentStatus) {
    switch (paymentStatus.toLowerCase()) {
      case 'paid':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'expired':
        return Colors.grey;
      case 'failed':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _getStatusText(String paymentStatus) {
    switch (paymentStatus.toLowerCase()) {
      case 'paid':
        return 'Berhasil';
      case 'pending':
        return 'Menunggu Pembayaran';
      case 'expired':
        return 'Kedaluwarsa';
      case 'failed':
        return 'Gagal';
      default:
        return 'Unknown';
    }
  }

  Future<void> _openUrl(String url) async {
    try {
      final uri = Uri.parse(url);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Tidak dapat membuka URL')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error membuka URL: $e')),
        );
      }
    }
  }

  Widget _buildOrderItem(OrderProduct item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: Row(
        children: [
          // Product Image Placeholder
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(8),
            ),
            child: item.product.image != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      Product.getImageUrl(item.product.image!),
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => Icon(
                        Icons.image_not_supported,
                        color: Colors.grey[500],
                      ),
                    ),
                  )
                : Icon(
                    Icons.image_not_supported,
                    color: Colors.grey[500],
                  ),
          ),
          const SizedBox(width: 12),

          // Product Details
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.displayName,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Colors.black87,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '${item.quantity}x × ${_formatCurrency(item.price)}',
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          ),

          // Item Total
          Text(
            _formatCurrency(item.totalPrice),
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: AppColors.primaryGreen,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusSection() {
    if (_order == null) return const SizedBox();

    final statusColor = _getStatusColor(_order!.paymentStatus);
    final statusText = _getStatusText(_order!.paymentStatus);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: statusColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: statusColor.withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                _order!.paymentStatus.toLowerCase() == 'paid'
                    ? Icons.check_circle
                    : _order!.paymentStatus.toLowerCase() == 'pending'
                    ? Icons.schedule
                    : _order!.paymentStatus.toLowerCase() == 'expired'
                    ? Icons.access_time_filled
                    : Icons.error,
                color: statusColor,
                size: 20,
              ),
              const SizedBox(width: 8),
              Text(
                statusText,
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: statusColor,
                ),
              ),
            ],
          ),
          if (_order!.paidAt != null) ...[
            const SizedBox(height: 8),
            Text(
              'Dibayar pada: ${_formatDate(_order!.paidAt!)} ${_formatTime(_order!.paidAt!)}',
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey[600],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildTrackingSection() {
    if (_order?.resi == null || _order!.resi!.isEmpty) return const SizedBox();
    return Container(
      width: double.infinity,
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text(
                'Pengiriman J&T',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF616161)),
              ),
              const Spacer(),
              IconButton(
                icon: _isTracking
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Icon(Icons.refresh, color: Colors.black87),
                onPressed: _isTracking ? null : _loadTracking,
                tooltip: 'Refresh Tracking',
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              const Text('Nomor Resi: ', style: TextStyle(fontWeight: FontWeight.w600)),
              SelectableText(_order!.resi!, style: const TextStyle(fontWeight: FontWeight.w700)),
              const SizedBox(width: 8),
              IconButton(
                icon: const Icon(Icons.copy, size: 18),
                onPressed: () {
                  Clipboard.setData(ClipboardData(text: _order!.resi!));
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Resi disalin')));
                },
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (_trackError != null)
            Text(_trackError!, style: const TextStyle(color: Colors.red)),
          if (_trackEvents.isEmpty && _trackError == null)
            Text(_isTracking ? 'Mengambil status…' : 'Belum ada pembaruan tracking', style: TextStyle(color: Colors.grey[600])),
          if (_trackEvents.isNotEmpty)
            Column(
              children: _trackEvents.map((e) {
                final dt = e.datetime != null ? '${DateFormat('dd MMM yyyy', 'id_ID').format(e.datetime!)} • ${DateFormat('HH:mm', 'id_ID').format(e.datetime!)}' : '';
                return ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.local_shipping, color: AppColors.primaryGreen),
                  title: Text(e.status, style: const TextStyle(fontWeight: FontWeight.w700)),
                  subtitle: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    if (dt.isNotEmpty) Text(dt),
                    if (e.location != null) Text(e.location!),
                    if (e.description != null) Text(e.description!),
                  ]),
                );
              }).toList(),
            ),
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    if (_order == null) return const SizedBox();

    List<Widget> buttons = [];

    // Invoice button (always available if invoice_pdf_url exists)
    if (_order!.invoicePdfUrl != null) {
      buttons.add(
        Expanded(
          child: OutlinedButton.icon(
            onPressed: () => _openUrl(_order!.invoicePdfUrl!),
            icon: const Icon(Icons.description_outlined, size: 18),
            label: const Text(
              'Lihat Invoice',
              style: TextStyle(fontWeight: FontWeight.w600),
            ),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: AppColors.primaryGreen),
              foregroundColor: AppColors.primaryGreen,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ),
      );
    }

    // Receipt button (only if paid and receipt_pdf_url exists)
    if (_order!.paymentStatus.toLowerCase() == 'paid' && _order!.receiptPdfUrl != null) {
      if (buttons.isNotEmpty) buttons.add(const SizedBox(width: 12));
      
      buttons.add(
        Expanded(
          child: ElevatedButton.icon(
            onPressed: () => _openUrl(_order!.receiptPdfUrl!),
            icon: const Icon(Icons.receipt_outlined, size: 18),
            label: const Text(
              'Lihat Receipt',
              style: TextStyle(fontWeight: FontWeight.w600),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ),
      );
    }

    // Pay Now button (only if pending)
    if (_order!.paymentStatus.toLowerCase() == 'pending' && _order!.xenditInvoiceUrl != null) {
      if (buttons.isNotEmpty) buttons.add(const SizedBox(width: 12));
      
      buttons.add(
        Expanded(
          child: ElevatedButton.icon(
            onPressed: () {
              HapticFeedback.lightImpact();
              _openUrl(_order!.xenditInvoiceUrl!);
            },
            icon: const Icon(Icons.payment, size: 18),
            label: const Text(
              'Bayar Sekarang',
              style: TextStyle(fontWeight: FontWeight.w600),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primaryGreen,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ),
      );
    }

    // Create J&T Shipment button (if no resi yet)
    if ((_order!.resi == null || _order!.resi!.isEmpty) && _order!.paymentStatus.toLowerCase() == 'paid') {
      if (buttons.isNotEmpty) buttons.add(const SizedBox(width: 12));
      buttons.add(
        Expanded(
          child: ElevatedButton.icon(
            onPressed: _isLoading ? null : _createJntShipment,
            icon: const Icon(Icons.local_shipping_outlined, size: 18),
            label: const Text('Buat Pengiriman J&T', style: TextStyle(fontWeight: FontWeight.w600)),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primaryGreen,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
          ),
        ),
      );
    }

    // Cancel J&T Shipment (if resi exists)
    if (_order!.resi != null && _order!.resi!.isNotEmpty && _order!.paymentStatus.toLowerCase() != 'pending') {
      if (buttons.isNotEmpty) buttons.add(const SizedBox(width: 12));
      buttons.add(
        Expanded(
          child: OutlinedButton.icon(
            onPressed: _isLoading ? null : _cancelJntShipment,
            icon: const Icon(Icons.cancel_outlined, size: 18),
            label: const Text('Batalkan Pengiriman', style: TextStyle(fontWeight: FontWeight.w600)),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: AppColors.primaryGreen),
              foregroundColor: AppColors.primaryGreen,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
          ),
        ),
      );
    }

    if (buttons.isEmpty) return const SizedBox();

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(children: buttons),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text(
          'Detail Pesanan',
          style: TextStyle(
            fontWeight: FontWeight.w700,
            color: Colors.black87,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Colors.black87),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          if (!_isLoading && _order != null)
            IconButton(
              icon: const Icon(Icons.refresh, color: Colors.black87),
              onPressed: _loadOrderDetail,
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primaryGreen))
          : _error != null
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  const Text(
                    'Gagal memuat detail pesanan',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Silakan coba lagi',
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: _loadOrderDetail,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primaryGreen,
                      foregroundColor: Colors.white,
                    ),
                    child: const Text('Coba Lagi'),
                  ),
                ],
              ),
            )
          : SingleChildScrollView(
              child: Column(
                children: [
                  // Order Header
                  Container(
                    width: double.infinity,
                    color: Colors.white,
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Order No: ${_order!.externalId ?? '#${_order!.id}'}',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${_formatDate(_order!.createdAt)} • ${_formatTime(_order!.createdAt)}',
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 8),

                  // Status Section
                  Container(
                    width: double.infinity,
                    color: Colors.white,
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: const [
                        Text(
                          'Status Pembayaran',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF616161),
                          ),
                        ),
                        SizedBox(height: 12),
                        // _buildStatusSection() will be below in children via spread
                      ],
                    ),
                  ),

                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: _buildStatusSection(),
                  ),

                  const SizedBox(height: 8),

                  // Tracking Section (J&T)
                  _buildTrackingSection(),

                  const SizedBox(height: 8),

                  // Order Items
                  Container(
                    width: double.infinity,
                    color: Colors.white,
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Item Pesanan',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF616161),
                          ),
                        ),
                        const SizedBox(height: 16),
                        ..._order!.orderProducts.map(_buildOrderItem),
                      ],
                    ),
                  ),

                  const SizedBox(height: 8),

                  // Total Section
                  Container(
                    width: double.infinity,
                    color: Colors.white,
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Ringkasan Pembayaran',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF616161),
                          ),
                        ),
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.primaryGreen.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppColors.primaryGreen.withOpacity(0.3)),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'Total Pembayaran',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                  color: Colors.black87,
                                ),
                              ),
                              Text(
                                _formatCurrency(_order!.total),
                                style: const TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.primaryGreen,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Action Buttons (including J&T actions)
                  _buildActionButtons(),

                  const SizedBox(height: 32),
                ],
              ),
            ),
    );
  }
}

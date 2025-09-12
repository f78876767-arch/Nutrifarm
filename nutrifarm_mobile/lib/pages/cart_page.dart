import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lottie/lottie.dart';
import '../services/cart_service.dart';
import '../services/checkout_service.dart';
import '../services/auth_service.dart';
import '../theme/app_theme.dart';
import '../services/address_service.dart';
import '../models/address.dart';
import 'address_list_page.dart';
import '../widgets/app_dialog.dart';
import '../services/api_service.dart';
import '../models/jnt_models.dart';

class CartPage extends StatefulWidget {
  const CartPage({super.key});

  @override
  State<CartPage> createState() => _CartPageState();
}

class _CartPageState extends State<CartPage> {
  bool _initialized = false;
  String? _note;
  // --- J&T tariff state ---
  bool _jntLoading = false;
  String? _jntError;
  List<JntTariffResult> _jntOptions = [];
  JntTariffResult? _jntSelected;
  String? _jntDestCity;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      await Provider.of<CartService>(context, listen: false).loadCart();
      await Provider.of<AddressService>(context, listen: false).loadAddresses();
      await _loadJnt(Provider.of<AddressService>(context, listen: false).defaultAddress);
      if (mounted) setState(() => _initialized = true);
    });
  }

  Future<void> _loadJnt(Address? defaultAddress) async {
    setState(() {
      _jntLoading = true;
      _jntError = null;
      _jntOptions = [];
      _jntSelected = null;
    });

    try {
      final cart = Provider.of<CartService>(context, listen: false);
      final totalWeight = _calcTotalWeightGrams(cart); // grams
      // Seller origin city fixed: Tangerang Selatan (ID 369)

      String? destCity;
      String? destinationCityId;
      String? jntDestCode = defaultAddress?.jntCityCode;

      if (defaultAddress != null) {
        destCity = (defaultAddress.roCity?.trim().isNotEmpty == true)
            ? defaultAddress.roCity!.trim()
            : _extractCityFromAddress(defaultAddress.fullAddress);
        destinationCityId = defaultAddress.roCityId?.toString();
      } else {
        if ((_jntDestCity?.trim().isNotEmpty ?? false)) {
          destCity = _jntDestCity!.trim();
        } else {
          setState(() {
            _jntLoading = false;
            _jntError = 'Setel kota agar ongkir bisa dihitung';
            _jntOptions = [];
            _jntSelected = null;
          });
          return;
        }
      }

      // Fallback mapping from common city names to RajaOngkir city IDs
      if (destinationCityId == null) {
        final key = destCity.toLowerCase();
        const Map<String, String> idMap = {
          'jakarta barat': '151',
          'jakarta pusat': '152',
          'jakarta selatan': '153',
          'jakarta timur': '154',
          'jakarta utara': '155',
          'tangerang selatan': '369',
          'kota tangerang selatan': '369',
        };
        if (idMap.containsKey(key)) {
          destinationCityId = idMap[key];
        }
        // Example default J&T code mapping if available
        const Map<String, String> jntCodeMap = {
          'jakarta pusat': 'JAKARTA PUSAT',
        };
        if (jntDestCode == null && jntCodeMap.containsKey(key)) {
          jntDestCode = jntCodeMap[key];
        }
      }

      // Persist mapped city id back to default address if missing
      if (defaultAddress != null && defaultAddress.roCityId == null && destinationCityId != null) {
        try {
          final addrSvc = Provider.of<AddressService>(context, listen: false);
          await addrSvc.ensureDefaultAddressRoCity(
            roCityId: int.tryParse(destinationCityId),
            roCity: destCity,
          );
        } catch (_) {}
      }

      // Enforce minimum chargeable weight in kg while also sending raw grams
      final int weightGrams = totalWeight.round().clamp(1, 1000000);
      final int weightKg = ((weightGrams + 999) ~/ 1000); // ceil to kg

      _jntDestCity = destCity;

      // Build flexible payload for J&T direct API or wrappers
      final String originCityName = 'Tangerang Selatan';
      final String destinationCityName = destCity!;
      const String jntOriginCode = 'TANGERANG SELATAN'; // replace with actual J&T origin code used by backend

      final Map<String, String> payload = {
        // Names
        'origin': originCityName,
        'destination': destinationCityName,
        // Direct J&T style codes (if backend expects these)
        'origin_code': jntOriginCode,
        if (jntDestCode != null) 'destination_code': jntDestCode,
        // Also include other variants to maximize compatibility
        'origin_city': originCityName,
        'destination_city': destinationCityName,
        'from_city': originCityName,
        'to_city': destinationCityName,
        'from': originCityName,
        'to': destinationCityName,
        // IDs if available
        'origin_city_id': '369',
        if (destinationCityId != null) 'destination_city_id': destinationCityId,
        'from_code': '369',
        if (destinationCityId != null) 'to_code': destinationCityId,
        // Weight variants
        'weight': weightGrams.toString(),
        'weight_gram': weightGrams.toString(),
        'weight_in_gram': weightGrams.toString(),
        'weight_kg': weightKg.toString(),
        'courier': 'jnt',
      };

      // Debug
      // ignore: avoid_print
      print('📦 JNT payload => $payload');

      final options = await ApiService.jntTariff(payload);
      options.sort((a, b) => a.cost.compareTo(b.cost));

      // Debug
      // ignore: avoid_print
      print('📦 JNT options count => ${options.length}');

      setState(() {
        _jntOptions = options;
        _jntSelected = options.isNotEmpty ? options.first : null;
        _jntLoading = false;
      });
    } catch (e) {
      setState(() {
        _jntError = e.toString();
        _jntLoading = false;
      });
    }
  }

  double _calcTotalWeightGrams(CartService cart) {
    const defaultWeight = 700.0; // grams per item fallback
    double total = 0.0;
    for (final ci in cart.items) {
      final perItem = ci.selectedVariant?.weight ?? defaultWeight;
      total += perItem * ci.quantity;
    }
    return total <= 0 ? defaultWeight : total;
  }

  String _extractCityFromAddress(String address) {
    final parts = address.split(',');
    if (parts.length >= 2) return parts[parts.length - 2].trim();
    return address;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final overlay = isDark ? SystemUiOverlayStyle.light : SystemUiOverlayStyle.dark;

    return Scaffold(
      backgroundColor: theme.scaffoldBackgroundColor,
      appBar: AppBar(
        backgroundColor: theme.colorScheme.surface,
        elevation: 0,
        systemOverlayStyle: overlay,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios, size: 20, color: theme.colorScheme.onSurface),
          onPressed: () => Navigator.pop(context),
        ),
        centerTitle: true,
        title: Text('Keranjang', style: GoogleFonts.nunitoSans(fontSize: 18, fontWeight: FontWeight.w700, color: theme.colorScheme.onSurface)),
      ),
      body: Consumer2<CartService, AddressService>(
        builder: (context, cart, addressSvc, _) {
          if (!_initialized || cart.isLoading || addressSvc.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (cart.items.isEmpty) {
            return _buildEmptyState(theme);
          }
          return RefreshIndicator(
            onRefresh: () async {
              await Provider.of<CartService>(context, listen: false).loadCart();
              await Provider.of<AddressService>(context, listen: false).loadAddresses();
              await _loadJnt(addressSvc.defaultAddress);
            },
            child: ListView(
              padding: EdgeInsets.zero,
              physics: const AlwaysScrollableScrollPhysics(),
              children: [
                _sectionDivider(),
                _addressSection(addressSvc.defaultAddress),
                // Add J&T tariff UI right below shipping address
                _jntTariffSection(addressSvc.defaultAddress),
                _sectionDivider(),
                _deliverySection(),
                _sectionDivider(height: 12),
                ...cart.items.map((ci) => _cartItem(ci, cart)).toList(),
                _sectionDivider(height: 12),
                _addNoteSection(),
                const SizedBox(height: 140),
              ],
            ),
          );
        },
      ),
      bottomSheet: Consumer<CartService>(
        builder: (context, cart, _) => cart.items.isEmpty
            ? const SizedBox.shrink()
            : _checkoutSheet(cart),
      ),
    );
  }

  Widget _jntTariffSection(Address? defaultAddress) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: isDark ? theme.colorScheme.surfaceVariant.withOpacity(0.35) : const Color(0xFFEFF9F1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: isDark ? Colors.white10 : const Color(0xFFCCE6D4)),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: AppColors.primaryGreen,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.local_shipping_outlined, color: Colors.white, size: 18),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text('Ongkir J&T', style: GoogleFonts.nunitoSans(fontSize: 14, fontWeight: FontWeight.w800, color: theme.colorScheme.onSurface)),
                      const Spacer(),
                      TextButton.icon(
                        onPressed: _promptSetCity,
                        icon: Icon(Icons.location_city, size: 16, color: theme.colorScheme.onSurfaceVariant),
                        label: Text('Kota', style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant)),
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          minimumSize: const Size(0, 0),
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                      ),
                      IconButton(
                        tooltip: 'Hitung ulang',
                        icon: Icon(Icons.refresh, size: 18, color: theme.colorScheme.onSurfaceVariant),
                        onPressed: () => _loadJnt(defaultAddress),
                      ),
                    ],
                  ),
                  if (_jntLoading) ...[
                    const SizedBox(height: 4),
                    Row(children: [
                      const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2)),
                      const SizedBox(width: 8),
                      Text('Menghitung ongkir...', style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant)),
                    ]),
                  ] else if (_jntError != null) ...[
                    const SizedBox(height: 4),
                    Text(_jntError!, style: GoogleFonts.nunitoSans(fontSize: 12, color: Colors.red)),
                  ] else if (_jntSelected != null) ...[
                    const SizedBox(height: 2),
                    Text(
                      _jntDestCity == null ? 'Tujuan tidak diketahui' : 'Ke ${_jntDestCity}',
                      style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${_jntSelected!.serviceName} • ${_formatCurrency(_jntSelected!.cost)}' +
                      ((_jntSelected!.etd != null && _jntSelected!.etd!.isNotEmpty) ? ' • ETD ${_jntSelected!.etd}' : ''),
                      style: GoogleFonts.nunitoSans(fontSize: 13, fontWeight: FontWeight.w700, color: isDark ? Colors.white : AppColors.primaryGreen),
                    ),
                    if (_jntOptions.length > 1) ...[
                      const SizedBox(height: 6),
                      Text('Opsi lain:', style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant)),
                      const SizedBox(height: 6),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: _jntOptions.map((opt) {
                          final selected = opt == _jntSelected;
                          return ChoiceChip(
                            label: Text('${opt.serviceName} • ${_formatCurrency(opt.cost)}', style: GoogleFonts.nunitoSans(fontSize: 12)),
                            selected: selected,
                            onSelected: (_) => setState(() => _jntSelected = opt),
                          );
                        }).toList(),
                      ),
                    ],
                  ] else ...[
                    const SizedBox(height: 4),
                    if (!_hasDestination(defaultAddress)) ...[
                      Text('Kota tujuan belum diatur. Setel kota agar ongkir bisa dihitung.', style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant)),
                      const SizedBox(height: 6),
                      Align(
                        alignment: Alignment.centerLeft,
                        child: OutlinedButton.icon(
                          onPressed: _promptSetCity,
                          icon: const Icon(Icons.location_city, size: 18),
                          label: const Text('Atur Kota Tujuan'),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: AppColors.primaryGreen,
                            side: const BorderSide(color: AppColors.primaryGreen),
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          ),
                        ),
                      ),
                    ] else ...[
                      if (_jntDestCity?.isNotEmpty == true) ...[
                        Text('Ke ${_jntDestCity}', style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant)),
                        const SizedBox(height: 2),
                      ],
                      Text('Tidak ada layanan tersedia', style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant)),
                    ]
                  ]
                ],
              ),
            )
          ],
        ),
      ),
    );
  }

  Future<void> _promptSetCity() async {
    final addrSvc = Provider.of<AddressService>(context, listen: false);
    final def = addrSvc.defaultAddress;
    final prefillCity = _jntDestCity ?? def?.roCity ?? (def != null ? _extractCityFromAddress(def.fullAddress) : '');
    final prefillId = def?.roCityId?.toString() ?? '';
    final prefillJnt = def?.jntCityCode ?? '';

    final cityCtrl = TextEditingController(text: prefillCity);
    final idCtrl = TextEditingController(text: prefillId);
    final jntCtrl = TextEditingController(text: prefillJnt);
    final theme = Theme.of(context);
    final result = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Atur Kota Tujuan'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: cityCtrl,
              decoration: const InputDecoration(labelText: 'Nama Kota (mis. Jakarta Pusat)'),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: idCtrl,
              decoration: const InputDecoration(labelText: 'City ID (opsional)'),
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 8),
            TextField(
              controller: jntCtrl,
              decoration: const InputDecoration(labelText: 'J&T Area Code (opsional, mis. JAKARTA PUSAT)'),
            ),
            const SizedBox(height: 8),
            Text('Jika tidak tahu City ID/Kode J&T, isi nama kota saja.', style: TextStyle(fontSize: 12, color: theme.colorScheme.onSurfaceVariant)),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Simpan')),
        ],
      ),
    );
    if (result == true) {
      final cityName = cityCtrl.text.trim();
      final cityId = int.tryParse(idCtrl.text.trim());
      final jntCode = jntCtrl.text.trim().isNotEmpty ? jntCtrl.text.trim() : null;

      if (addrSvc.defaultAddress != null) {
        await addrSvc.ensureDefaultAddressRoCity(
          roCityId: cityId,
          roCity: cityName.isNotEmpty ? cityName : null,
          jntCityCode: jntCode,
        );
        await _loadJnt(addrSvc.defaultAddress);
      } else {
        setState(() {
          _jntDestCity = cityName;
        });
        await _loadJnt(null);
      }
      setState(() {});
    }
  }

  bool _hasDestination(Address? addr) {
    if (addr == null) return (_jntDestCity?.trim().isNotEmpty ?? false);
    if (addr.roCityId != null) return true;
    if ((addr.roCity?.trim().isNotEmpty ?? false)) return true;
    if ((addr.jntCityCode?.trim().isNotEmpty ?? false)) return true;
    if ((_jntDestCity?.trim().isNotEmpty ?? false)) return true;
    return false;
  }

  // --- Sections ---
  Widget _addressSection(Address? defaultAddress) {
    final hasAddress = defaultAddress != null;
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text('Alamat Pengiriman', style: GoogleFonts.nunitoSans(fontSize: 15, fontWeight: FontWeight.w700, color: theme.colorScheme.onSurface)),
              const Spacer(),
              TextButton(
                onPressed: () async {
                  // Navigate to address list to change default
                  await Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AddressListPage()),
                  );
                  if (mounted) {
                    await Provider.of<AddressService>(context, listen: false).loadAddresses();
                    // Reload JNT when address changes
                    await _loadJnt(Provider.of<AddressService>(context, listen: false).defaultAddress);
                    setState(() {});
                  }
                },
                child: Text(
                  'Ganti Alamat',
                  style: GoogleFonts.nunitoSans(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white : AppColors.primaryGreen,
                  ),
                ),
              )
            ],
          ),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: isDark ? theme.colorScheme.surfaceVariant.withOpacity(0.35) : const Color(0xFFFFF1DE),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: Colors.orange.shade500,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Center(
                    child: Icon(Icons.location_on, color: Colors.white),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: hasAddress
                      ? Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text(defaultAddress.label.isNotEmpty ? defaultAddress.label : 'Alamat Utama',
                                    style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700, fontSize: 15, color: theme.colorScheme.onSurface)),
                                const SizedBox(width: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: Colors.green.shade100,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text('Utama', style: GoogleFonts.nunitoSans(fontSize: 11, color: Colors.green.shade800, fontWeight: FontWeight.w700)),
                                )
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              defaultAddress.fullAddress,
                              style: GoogleFonts.nunitoSans(fontSize: 12, height: 1.3, color: theme.colorScheme.onSurface.withOpacity(.75)),
                            ),
                            if ((defaultAddress.detailAddress ?? '').isNotEmpty) ...[
                              Text(
                                defaultAddress.detailAddress!,
                                style: GoogleFonts.nunitoSans(fontSize: 12, height: 1.3, color: theme.colorScheme.onSurface.withOpacity(.65)),
                              ),
                            ],
                            if ((defaultAddress.recipientName ?? '').isNotEmpty || (defaultAddress.phoneNumber ?? '').isNotEmpty) ...[
                              const SizedBox(height: 6),
                              Row(
                                children: [
                                  if ((defaultAddress.recipientName ?? '').isNotEmpty)
                                    Text(defaultAddress.recipientName!, style: GoogleFonts.nunitoSans(fontSize: 12, fontWeight: FontWeight.w700, color: theme.colorScheme.onSurface)),
                                  if ((defaultAddress.recipientName ?? '').isNotEmpty && (defaultAddress.phoneNumber ?? '').isNotEmpty)
                                    const SizedBox(width: 6),
                                  if ((defaultAddress.phoneNumber ?? '').isNotEmpty)
                                    Text(defaultAddress.phoneNumber!, style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant)),
                                ],
                              )
                            ],
                          ],
                        )
                      : Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Belum ada alamat utama', style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w700, fontSize: 15, color: theme.colorScheme.onSurface)),
                            const SizedBox(height: 4),
                            Text('Tambahkan alamat terlebih dahulu untuk pengiriman.',
                                style: GoogleFonts.nunitoSans(fontSize: 12, height: 1.3, color: theme.colorScheme.onSurface.withOpacity(.65))),
                            const SizedBox(height: 8),
                            OutlinedButton.icon(
                              onPressed: () async {
                                await Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => const AddressListPage()),
                                );
                                if (mounted) {
                                  await Provider.of<AddressService>(context, listen: false).loadAddresses();
                                  await _loadJnt(Provider.of<AddressService>(context, listen: false).defaultAddress);
                                  setState(() {});
                                }
                              },
                              icon: const Icon(Icons.add_location_alt_outlined),
                              label: const Text('Tambah Alamat'),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: AppColors.primaryGreen,
                                side: const BorderSide(color: AppColors.primaryGreen),
                              ),
                            )
                          ],
                        ),
                )
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _deliverySection() {
    final theme = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      child: Row(
        children: [
          Text('Pengiriman 25 - 45 Menit', style: GoogleFonts.nunitoSans(fontSize: 15, fontWeight: FontWeight.w700, color: theme.colorScheme.onSurface)),
          const SizedBox(width: 6),
          Icon(Icons.access_time, size: 16, color: theme.colorScheme.onSurface),
          const Spacer(),
          TextButton(
            onPressed: () {},
            child: Text(
              'Jadwalkan',
              style: GoogleFonts.nunitoSans(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: Theme.of(context).brightness == Brightness.dark ? Colors.white : AppColors.primaryGreen,
              ),
            ),
          )
        ],
      ),
    );
  }

  Widget _addNoteSection() {
    final theme = Theme.of(context);
    return InkWell(
      onTap: () async {
        final result = await AppDialog.showPrompt(
          context,
          title: 'Catatan untuk Penjual',
          initialText: _note,
          hintText: 'Tulis catatan (opsional)',
        );
        if (result != null) setState(() => _note = result.isEmpty ? null : result);
      },
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(
          children: [
            const Icon(Icons.note_add_outlined, color: AppColors.primaryGreen, size: 28),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                _note == null ? 'Tambah Catatan' : _note!,
                style: GoogleFonts.nunitoSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w600,
                  color: Theme.of(context).brightness == Brightness.dark ? Colors.white : AppColors.primaryGreen,
                ),
              ),
            ),
            if (_note != null)
              IconButton(
                icon: Icon(Icons.close, size: 18, color: theme.colorScheme.onSurfaceVariant),
                onPressed: () => setState(() => _note = null),
              )
          ],
        ),
      ),
    );
  }

  Widget _cartItem(CartItem item, CartService cart) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final p = item.product;
    final hasDiscount = p.hasDiscount;
    final discountPercent = hasDiscount ? (((p.price - (p.discountPrice ?? p.price)) / p.price) * 100).round() : null;
    // Calculate total price if quantity > 1
    final totalItemPrice = item.effectivePrice * item.quantity;
    
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Stack(
            clipBehavior: Clip.none,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: p.imageUrl.isEmpty
                    ? Container(width: 62, height: 62, color: isDark ? Colors.grey.shade800 : Colors.grey.shade200, child: const Icon(Icons.image, size: 26))
                    : Image.network(p.imageUrl, width: 62, height: 62, fit: BoxFit.cover),
              ),
              if (discountPercent != null)
                Positioned(
                  top: -4,
                  left: -4,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                    decoration: BoxDecoration(color: Colors.red, borderRadius: BorderRadius.circular(6)),
                    child: Text('$discountPercent%', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
                  ),
                ),
            ],
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Product name with total price for multiple items
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Text(
                        p.name, // Show base product name
                        style: GoogleFonts.nunitoSans(fontSize: 14, fontWeight: FontWeight.w700, height: 1.2, color: theme.colorScheme.onSurface)
                      ),
                    ),
                    // Show total price for this product if quantity > 1 or has variants
                    const SizedBox(width: 8),
                    Text(
                      _formatCurrency(totalItemPrice),
                      style: GoogleFonts.nunitoSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: isDark ? Colors.white : AppColors.primaryGreen,
                      ),
                    ),
                  ],
                ),
                // Show variant information if exists as subtitle
                if (item.selectedVariant != null) ...[
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          'Varian: ${item.selectedVariant!.displayName}',
                          style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant, fontWeight: FontWeight.w500),
                        ),
                      ),
                      Text(
                        '${_formatCurrency(item.effectivePrice)} × ${item.quantity}',
                        style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant, fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                  // Show variant discount if exists
                  if (item.selectedVariant!.hasDiscount) ...[
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        const SizedBox(width: 0), // Align with variant text
                        Text(
                          'Harga normal: ${_formatCurrency(item.selectedVariant!.originalPrice)}',
                          style: GoogleFonts.nunitoSans(fontSize: 11, color: theme.colorScheme.onSurfaceVariant, decoration: TextDecoration.lineThrough),
                        ),
                        const SizedBox(width: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.red,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            '${item.selectedVariant!.discountPercentage?.round() ?? 0}%',
                            style: GoogleFonts.nunitoSans(fontSize: 10, color: Colors.white, fontWeight: FontWeight.w700),
                          ),
                        ),
                      ],
                    ),
                  ],
                ] else ...[
                  // No variant - show product price breakdown
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Text(
                        '${_formatCurrency(item.effectivePrice)} × ${item.quantity}',
                        style: GoogleFonts.nunitoSans(fontSize: 12, color: theme.colorScheme.onSurfaceVariant, fontWeight: FontWeight.w500),
                      ),
                      if (hasDiscount) ...[
                        const SizedBox(width: 8),
                        Text(
                          _formatCurrency(p.price),
                          style: GoogleFonts.nunitoSans(fontSize: 11, color: theme.colorScheme.onSurfaceVariant, decoration: TextDecoration.lineThrough),
                        ),
                      ],
                    ],
                  ),
                ],
                const SizedBox(height: 6),
                const SizedBox(height: 6),
                Row(
                  children: [
                    const Icon(Icons.local_offer, size: 14, color: Colors.orange),
                    const SizedBox(width: 4),
                    Text(
                      hasDiscount ? 'Beli 2 diskon hingga ${discountPercent?.clamp(0, 99)}%' : 'Promo menarik',
                      style: GoogleFonts.nunitoSans(fontSize: 12, color: Colors.orange, fontWeight: FontWeight.w600),
                    )
                  ],
                )
              ],
            ),
          ),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              // Small trash button to remove the whole item quickly
              SizedBox(
                height: 32,
                width: 32,
                child: IconButton(
                  padding: EdgeInsets.zero,
                  tooltip: 'Hapus item',
                  icon: const Icon(Icons.delete_outline, size: 18, color: Colors.redAccent),
                  onPressed: () async {
                    HapticFeedback.mediumImpact();
                    await cart.removeFromCartWithVariant(p.id, variant: item.selectedVariant);
                  },
                ),
              ),
              const SizedBox(height: 6),
              Container(
                decoration: BoxDecoration(
                  color: AppColors.primaryGreen,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _qtyBtn(icon: Icons.remove, onTap: () async {
                      HapticFeedback.lightImpact();
                      if (item.quantity > 1) {
                        await cart.updateQuantityForVariant(p.id, item.quantity - 1, variant: item.selectedVariant);
                      } else {
                        await cart.removeFromCartWithVariant(p.id, variant: item.selectedVariant);
                      }
                    }),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text('${item.quantity}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 16)),
                          // Small indicator for pending updates
                          if (cart.hasPendingUpdate(p.id))
                            Container(
                              margin: const EdgeInsets.only(left: 4),
                              width: 6,
                              height: 6,
                              decoration: const BoxDecoration(
                                color: Colors.orange,
                                shape: BoxShape.circle,
                              ),
                            ),
                        ],
                      ),
                    ),
                    _qtyBtn(icon: Icons.add, onTap: () async {
                      HapticFeedback.lightImpact();
                      await cart.updateQuantityForVariant(p.id, item.quantity + 1, variant: item.selectedVariant);
                    }),
                  ],
                ),
              ),
            ],
          )
        ],
      ),
    );
  }

  Widget _qtyBtn({required IconData icon, required VoidCallback onTap}) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.all(6),
        child: Icon(icon, size: 18, color: Colors.white),
      ),
    );
  }

  // Bottom checkout sheet showing totals without shipping
  Widget _checkoutSheet(CartService cart) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final double subtotal = cart.subtotal;
    return SafeArea(
      top: false,
      child: Material(
        elevation: 20,
        color: Colors.transparent,
        child: Consumer<CheckoutService>(
          builder: (context, checkout, _) {
            final grandTotal = subtotal; // no shipping
            return Container(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
              decoration: BoxDecoration(
                color: theme.colorScheme.surface,
                boxShadow: [
                  BoxShadow(
                    color: isDark ? Colors.black.withOpacity(0.4) : const Color(0x14000000),
                    blurRadius: 12,
                    offset: const Offset(0, -4),
                  ),
                ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      Text('Subtotal', style: GoogleFonts.nunitoSans(fontSize: 13, fontWeight: FontWeight.w600, color: theme.colorScheme.onSurface)),
                      const Spacer(),
                      Text(_formatCurrency(subtotal), style: GoogleFonts.nunitoSans(fontSize: 14, fontWeight: FontWeight.w700, color: theme.colorScheme.onSurface)),
                    ],
                  ),
                  const Divider(height: 18),
                  Row(
                    children: [
                      Text('Total', style: GoogleFonts.nunitoSans(fontSize: 14, fontWeight: FontWeight.w700, color: theme.colorScheme.onSurface)),
                      const Spacer(),
                      Text(_formatCurrency(grandTotal), style: GoogleFonts.nunitoSans(fontSize: 18, fontWeight: FontWeight.w800, color: theme.colorScheme.onSurface)),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primaryGreen,
                            foregroundColor: Colors.white,
                            elevation: 0,
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          onPressed: Provider.of<CheckoutService>(context, listen: false).isLoading
                              ? null
                              : () => _handleCheckout(context, cart, Provider.of<CheckoutService>(context, listen: false)),
                          child: Provider.of<CheckoutService>(context).isLoading
                              ? const SizedBox(
                                  height: 20,
                                  width: 20,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                  ),
                                )
                              : Text('Bayar', style: GoogleFonts.nunitoSans(fontSize: 15, fontWeight: FontWeight.w700)),
                        ),
                      )
                    ],
                  )
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  // Handle checkout process
  Future<void> _handleCheckout(BuildContext context, CartService cart, CheckoutService checkoutService) async {
    HapticFeedback.mediumImpact();
    
    try {
      // Get user email from AuthService
      final authService = Provider.of<AuthService>(context, listen: false);
      final userEmail = authService.userEmail;
      
      if (userEmail.isEmpty) {
        throw Exception('User email is required for payment');
      }
      
      // Get default delivery address
      final defaultAddress = Provider.of<AddressService>(context, listen: false).defaultAddress;
      final deliveryAddress = defaultAddress != null
          ? '${defaultAddress.fullAddress}${(defaultAddress.detailAddress ?? '').isNotEmpty ? "\n${defaultAddress.detailAddress}" : ''}'
          : 'Alamat belum diatur';
      
      // Show loading dialog first
      AppDialog.showLoading(context, message: 'Memproses pembayaran...');

      // Process checkout with Xendit
      final success = await checkoutService.processCheckout(
        notes: _note,
        deliveryAddress: deliveryAddress,
        userEmail: userEmail,
      );

      // Dismiss loading dialog
      if (context.mounted) {
        Navigator.of(context).pop();
      }

      if (success) {
        // Show success message
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  const Icon(Icons.check_circle, color: Colors.white),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Invoice Xendit berhasil dibuat! Silakan lanjutkan pembayaran.',
                      style: GoogleFonts.nunitoSans(color: Colors.white),
                    ),
                  ),
                ],
              ),
              backgroundColor: Colors.green,
              duration: const Duration(seconds: 3),
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      } else {
        // Show error message
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  const Icon(Icons.error, color: Colors.white),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Pembayaran gagal: ${checkoutService.error ?? 'Unknown error'}',
                      style: GoogleFonts.nunitoSans(color: Colors.white),
                    ),
                  ),
                ],
              ),
              backgroundColor: Colors.red,
              duration: const Duration(seconds: 4),
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    } catch (e) {
      // Dismiss loading dialog if still showing
      if (context.mounted) {
        if (Navigator.of(context).canPop()) {
          Navigator.of(context).pop();
        }
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.error, color: Colors.white),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Terjadi kesalahan: ${e.toString()}',
                    style: GoogleFonts.nunitoSans(color: Colors.white),
                  ),
                ),
              ],
            ),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 4),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  // --- Helpers ---
  Widget _sectionDivider({double height = 8}) => SizedBox(height: height);

  Widget _buildEmptyState(ThemeData theme) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SizedBox(
              width: 220,
              height: 220,
              child: Lottie.asset('assets/images/empty-ghost.json', repeat: true),
            ),
            const SizedBox(height: 16),
            Text(
              'Keranjang kamu kosong',
              style: GoogleFonts.nunitoSans(fontSize: 18, fontWeight: FontWeight.w700, color: theme.colorScheme.onSurface),
            ),
            const SizedBox(height: 8),
            Text(
              'Yuk, cari produk favoritmu dan tambahkan ke keranjang!',
              style: GoogleFonts.nunitoSans(fontSize: 14, color: theme.colorScheme.onSurfaceVariant),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  String _formatCurrency(double value) {
    final s = value.toStringAsFixed(0);
    final formatted = s.replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (m) => '${m[1]}.'
    );
    return 'Rp $formatted';
  }
}

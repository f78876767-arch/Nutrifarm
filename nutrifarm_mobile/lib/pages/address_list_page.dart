import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../services/address_service.dart';
import '../models/address.dart';
import 'add_address_page_simple.dart';

class AddressListPage extends StatefulWidget {
  final bool isSelectionMode;
  final Function(Address)? onAddressSelected;

  const AddressListPage({
    super.key,
    this.isSelectionMode = false,
    this.onAddressSelected,
  });

  @override
  State<AddressListPage> createState() => _AddressListPageState();
}

class _AddressListPageState extends State<AddressListPage> {
  final TextEditingController _searchController = TextEditingController();
  List<Address> _filteredAddresses = [];
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  void _onSearchChanged() {
    setState(() {
      _searchQuery = _searchController.text;
    });
  }

  void _filterAddresses(List<Address> addresses) {
    if (_searchQuery.isEmpty) {
      _filteredAddresses = addresses;
    } else {
      _filteredAddresses = Provider.of<AddressService>(context, listen: false)
          .searchAddresses(_searchQuery);
    }
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
          widget.isSelectionMode ? 'Pilih Lokasi' : 'Alamat Tersimpan',
          style: GoogleFonts.nunitoSans(
            fontSize: 24,
            fontWeight: FontWeight.w700,
            color: AppColors.primaryGreen,
          ),
        ),
        actions: [
          if (!widget.isSelectionMode)
            IconButton(
              icon: const Icon(FeatherIcons.plus, color: AppColors.primaryGreen),
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const AddAddressPageSimple(),
                  ),
                );
              },
            ),
        ],
      ),
      body: Consumer<AddressService>(
        builder: (context, addressService, child) {
          _filterAddresses(addressService.addresses);

          if (addressService.isLoading) {
            return const Center(
              child: CircularProgressIndicator(color: AppColors.primaryGreen),
            );
          }

          return Column(
            children: [
              // Search Bar
              Container(
                margin: const EdgeInsets.all(16),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.primaryGreen.withOpacity(0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(FeatherIcons.search, color: AppColors.onSurfaceVariant, size: 20),
                    const SizedBox(width: 12),
                    Expanded(
                      child: TextField(
                        controller: _searchController,
                        decoration: InputDecoration(
                          hintText: 'Cari lokasi yang kamu mau',
                          hintStyle: GoogleFonts.nunitoSans(
                            color: AppColors.onSurfaceVariant,
                            fontSize: 14,
                          ),
                          border: InputBorder.none,
                        ),
                        style: GoogleFonts.nunitoSans(
                          fontSize: 14,
                          color: AppColors.onSurface,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // Current Location Option
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 16),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.onSurface.withOpacity(0.05),
                      blurRadius: 5,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: ListTile(
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.primaryGreen.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(FeatherIcons.navigation, color: AppColors.primaryGreen, size: 20),
                  ),
                  title: Text(
                    'Pakai lokasi kamu saat ini',
                    style: GoogleFonts.nunitoSans(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: AppColors.onSurface,
                    ),
                  ),
                  trailing: const Icon(FeatherIcons.chevronRight, color: AppColors.onSurfaceVariant),
                  onTap: () async {
                    HapticFeedback.lightImpact();
                    // Navigate to current location picker
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => const AddAddressPageSimple(useCurrentLocation: true),
                      ),
                    );
                  },
                ),
              ),

              const SizedBox(height: 16),

              // Addresses List
              Expanded(
                child: _filteredAddresses.isEmpty
                    ? _buildEmptyState()
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: _filteredAddresses.length,
                        itemBuilder: (context, index) {
                          final address = _filteredAddresses[index];
                          return _buildAddressItem(address, addressService);
                        },
                      ),
              ),
            ],
          );
        },
      ),
      floatingActionButton: !widget.isSelectionMode
          ? FloatingActionButton(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const AddAddressPageSimple(),
                  ),
                );
              },
              backgroundColor: AppColors.primaryGreen,
              child: const Icon(FeatherIcons.plus, color: Colors.white),
            )
          : null,
    );
  }

  Widget _buildAddressItem(Address address, AddressService addressService) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: address.isDefault 
            ? Border.all(color: AppColors.primaryGreen, width: 2)
            : null,
        boxShadow: [
          BoxShadow(
            color: AppColors.onSurface.withOpacity(0.05),
            blurRadius: 5,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.all(16),
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: address.isDefault 
                ? AppColors.primaryGreen.withOpacity(0.2)
                : AppColors.onSurfaceVariant.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            FeatherIcons.mapPin,
            color: address.isDefault ? AppColors.primaryGreen : AppColors.onSurfaceVariant,
            size: 20,
          ),
        ),
        title: Row(
          children: [
            Expanded(
              child: Text(
                address.label,
                style: GoogleFonts.nunitoSans(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: AppColors.onSurface,
                ),
              ),
            ),
            if (address.isDefault)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.primaryGreen,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'Utama',
                  style: GoogleFonts.nunitoSans(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                  ),
                ),
              ),
          ],
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(
              address.fullAddress,
              style: GoogleFonts.nunitoSans(
                fontSize: 14,
                color: AppColors.onSurfaceVariant,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            if (address.recipientName != null) ...[
              const SizedBox(height: 4),
              Text(
                address.recipientName!,
                style: GoogleFonts.nunitoSans(
                  fontSize: 12,
                  color: AppColors.onSurfaceVariant,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ],
        ),
        trailing: widget.isSelectionMode
            ? const Icon(FeatherIcons.chevronRight, color: AppColors.onSurfaceVariant)
            : PopupMenuButton<String>(
                icon: const Icon(FeatherIcons.moreVertical, color: AppColors.onSurfaceVariant),
                onSelected: (value) async {
                  switch (value) {
                    case 'edit':
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => AddAddressPageSimple(address: address),
                        ),
                      );
                      break;
                    case 'default':
                      await addressService.setDefaultAddress(address.id);
                      break;
                    case 'delete':
                      _showDeleteDialog(address, addressService);
                      break;
                  }
                },
                itemBuilder: (context) => [
                  PopupMenuItem(
                    value: 'edit',
                    child: Row(
                      children: [
                        const Icon(FeatherIcons.edit3, size: 16),
                        const SizedBox(width: 8),
                        Text('Edit', style: GoogleFonts.nunitoSans()),
                      ],
                    ),
                  ),
                  if (!address.isDefault)
                    PopupMenuItem(
                      value: 'default',
                      child: Row(
                        children: [
                          const Icon(FeatherIcons.star, size: 16),
                          const SizedBox(width: 8),
                          Text('Jadikan Utama', style: GoogleFonts.nunitoSans()),
                        ],
                      ),
                    ),
                  PopupMenuItem(
                    value: 'delete',
                    child: Row(
                      children: [
                        const Icon(FeatherIcons.trash2, size: 16, color: Colors.red),
                        const SizedBox(width: 8),
                        Text('Hapus', style: GoogleFonts.nunitoSans(color: Colors.red)),
                      ],
                    ),
                  ),
                ],
              ),
        onTap: () {
          HapticFeedback.lightImpact();
          if (widget.isSelectionMode && widget.onAddressSelected != null) {
            widget.onAddressSelected!(address);
            Navigator.pop(context, address);
          }
        },
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: AppColors.primaryGreen.withOpacity(0.1),
              borderRadius: BorderRadius.circular(50),
            ),
            child: const Icon(
              FeatherIcons.mapPin,
              size: 64,
              color: AppColors.primaryGreen,
            ),
          ),
          const SizedBox(height: 24),
          Text(
            _searchQuery.isEmpty ? 'Belum ada alamat tersimpan' : 'Alamat tidak ditemukan',
            style: GoogleFonts.nunitoSans(
              fontSize: 20,
              fontWeight: FontWeight.w600,
              color: AppColors.onSurface,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _searchQuery.isEmpty 
                ? 'Tambahkan alamat pertama kamu untuk pengiriman yang lebih mudah'
                : 'Coba kata kunci lain atau tambahkan alamat baru',
            style: GoogleFonts.nunitoSans(
              fontSize: 14,
              color: AppColors.onSurfaceVariant,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 32),
          ElevatedButton.icon(
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => const AddAddressPageSimple(),
                ),
              );
            },
            icon: const Icon(FeatherIcons.plus),
            label: Text(
              'Tambah Alamat',
              style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primaryGreen,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showDeleteDialog(Address address, AddressService addressService) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          'Hapus Alamat',
          style: GoogleFonts.nunitoSans(fontWeight: FontWeight.w600),
        ),
        content: Text(
          'Apakah kamu yakin ingin menghapus alamat "${address.label}"?',
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
            onPressed: () async {
              Navigator.pop(context);
              try {
                await addressService.deleteAddress(address.id);
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text('Alamat berhasil dihapus'),
                    backgroundColor: AppColors.success,
                  ),
                );
              } catch (e) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text('Gagal menghapus alamat'),
                    backgroundColor: Colors.red,
                  ),
                );
              }
            },
            child: Text(
              'Hapus',
              style: GoogleFonts.nunitoSans(color: Colors.red),
            ),
          ),
        ],
      ),
    );
  }
}

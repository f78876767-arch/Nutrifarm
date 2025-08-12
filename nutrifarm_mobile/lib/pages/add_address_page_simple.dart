import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../services/address_service.dart';
import '../models/address.dart';

class AddAddressPageSimple extends StatefulWidget {
  final Address? address;
  final bool useCurrentLocation;

  const AddAddressPageSimple({
    super.key,
    this.address,
    this.useCurrentLocation = false,
  });

  @override
  State<AddAddressPageSimple> createState() => _AddAddressPageSimpleState();
}

class _AddAddressPageSimpleState extends State<AddAddressPageSimple> {
  final _formKey = GlobalKey<FormState>();
  final _labelController = TextEditingController();
  final _addressController = TextEditingController();
  final _detailController = TextEditingController();
  final _recipientController = TextEditingController();
  final _phoneController = TextEditingController();
  
  bool _isDefault = false;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    if (widget.address != null) {
      _loadExistingAddress();
    }
  }

  void _loadExistingAddress() {
    final address = widget.address!;
    _labelController.text = address.label;
    _addressController.text = address.fullAddress;
    _detailController.text = address.detailAddress ?? '';
    _recipientController.text = address.recipientName ?? '';
    _phoneController.text = address.phoneNumber ?? '';
    _isDefault = address.isDefault;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        leading: IconButton(
          onPressed: () => Navigator.pop(context),
          icon: const Icon(FeatherIcons.arrowLeft, color: AppColors.onSurface),
        ),
        title: Text(
          widget.address == null ? 'Tambah Alamat' : 'Edit Alamat',
          style: GoogleFonts.poppins(
            fontSize: 18,
            fontWeight: FontWeight.w600,
            color: AppColors.onSurface,
          ),
        ),
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Note about maps
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.blue[50],
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.blue[200]!),
                ),
                child: Row(
                  children: [
                    Icon(FeatherIcons.info, color: Colors.blue[600], size: 20),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Peta dan GPS sementara tidak tersedia. Silakan isi alamat secara manual.',
                        style: GoogleFonts.poppins(
                          fontSize: 12,
                          color: Colors.blue[600],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: 24),

              // Label Field
              _buildFormField(
                label: 'Label Alamat',
                controller: _labelController,
                hint: 'Rumah, Kantor, atau label lainnya',
                icon: FeatherIcons.tag,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Label alamat harus diisi';
                  }
                  return null;
                },
              ),

              const SizedBox(height: 16),

              // Full Address Field
              _buildFormField(
                label: 'Alamat Lengkap',
                controller: _addressController,
                hint: 'Jalan, nomor rumah, kelurahan, kecamatan',
                icon: FeatherIcons.mapPin,
                maxLines: 3,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Alamat lengkap harus diisi';
                  }
                  return null;
                },
              ),

              const SizedBox(height: 16),

              // Detail Address Field
              _buildFormField(
                label: 'Detail Alamat (Opsional)',
                controller: _detailController,
                hint: 'Patokan, warna rumah, dll',
                icon: FeatherIcons.info,
                maxLines: 2,
              ),

              const SizedBox(height: 16),

              // Recipient Name Field
              _buildFormField(
                label: 'Nama Penerima',
                controller: _recipientController,
                hint: 'Nama orang yang menerima pesanan',
                icon: FeatherIcons.user,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Nama penerima harus diisi';
                  }
                  return null;
                },
              ),

              const SizedBox(height: 16),

              // Phone Field
              _buildFormField(
                label: 'Nomor Telepon',
                controller: _phoneController,
                hint: 'Nomor telepon yang dapat dihubungi',
                icon: FeatherIcons.phone,
                keyboardType: TextInputType.phone,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Nomor telepon harus diisi';
                  }
                  return null;
                },
              ),

              const SizedBox(height: 20),

              // Default Address Switch
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.withOpacity(0.2)),
                ),
                child: Row(
                  children: [
                    Icon(
                      FeatherIcons.home,
                      color: AppColors.primaryGreen,
                      size: 20,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Jadikan Alamat Utama',
                            style: GoogleFonts.poppins(
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                              color: AppColors.onSurface,
                            ),
                          ),
                          Text(
                            'Alamat ini akan diprioritaskan untuk pengiriman',
                            style: GoogleFonts.poppins(
                              fontSize: 12,
                              color: AppColors.onSurfaceVariant,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Switch(
                      value: _isDefault,
                      onChanged: (value) {
                        setState(() {
                          _isDefault = value;
                        });
                      },
                      activeColor: AppColors.primaryGreen,
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: AppColors.surface,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 10,
              offset: const Offset(0, -5),
            ),
          ],
        ),
        child: ElevatedButton(
          onPressed: _isLoading ? null : _saveAddress,
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primaryGreen,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
          child: _isLoading
              ? const SizedBox(
                  height: 20,
                  width: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                )
              : Text(
                  widget.address == null ? 'Simpan Alamat' : 'Update Alamat',
                  style: GoogleFonts.poppins(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
        ),
      ),
    );
  }

  Widget _buildFormField({
    required String label,
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    String? Function(String?)? validator,
    TextInputType keyboardType = TextInputType.text,
    int maxLines = 1,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.poppins(
            fontSize: 14,
            fontWeight: FontWeight.w500,
            color: AppColors.onSurface,
          ),
        ),
        const SizedBox(height: 8),
        TextFormField(
          controller: controller,
          validator: validator,
          keyboardType: keyboardType,
          maxLines: maxLines,
          decoration: InputDecoration(
            hintText: hint,
            prefixIcon: Icon(icon, color: AppColors.primaryGreen, size: 20),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.grey.withOpacity(0.3)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: AppColors.primaryGreen, width: 2),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Colors.red, width: 1),
            ),
            filled: true,
            fillColor: AppColors.surface,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          ),
        ),
      ],
    );
  }

  Future<void> _saveAddress() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() => _isLoading = true);

    try {
      final addressService = Provider.of<AddressService>(context, listen: false);
      final now = DateTime.now();
      
      if (widget.address == null) {
        // Add new address
        final newAddress = Address(
          id: DateTime.now().millisecondsSinceEpoch.toString(),
          label: _labelController.text.trim(),
          fullAddress: _addressController.text.trim(),
          detailAddress: _detailController.text.trim().isNotEmpty 
              ? _detailController.text.trim() 
              : null,
          recipientName: _recipientController.text.trim(),
          phoneNumber: _phoneController.text.trim(),
          isDefault: _isDefault,
          createdAt: now,
          updatedAt: now,
        );
        await addressService.addAddress(newAddress);
      } else {
        // Update existing address
        final updatedAddress = widget.address!.copyWith(
          label: _labelController.text.trim(),
          fullAddress: _addressController.text.trim(),
          detailAddress: _detailController.text.trim().isNotEmpty 
              ? _detailController.text.trim() 
              : null,
          recipientName: _recipientController.text.trim(),
          phoneNumber: _phoneController.text.trim(),
          isDefault: _isDefault,
          updatedAt: now,
        );
        await addressService.updateAddress(widget.address!.id, updatedAddress);
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              widget.address == null 
                  ? 'Alamat berhasil ditambahkan' 
                  : 'Alamat berhasil diperbarui',
              style: GoogleFonts.poppins(),
            ),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Gagal menyimpan alamat: ${e.toString()}',
              style: GoogleFonts.poppins(),
            ),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  void dispose() {
    _labelController.dispose();
    _addressController.dispose();
    _detailController.dispose();
    _recipientController.dispose();
    _phoneController.dispose();
    super.dispose();
  }
}

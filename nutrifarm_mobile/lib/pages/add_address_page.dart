import 'package:flutter/material.dart';
import 'pa  void _loadExistingAddress() {
    final address = widget.address!;
    _labelController.text = address.label;
    _addressController.text = address.fullAddress;
    _detailController.text = address.detailAddress ?? '';
    _recipientController.text = address.recipientName ?? '';
    _phoneController.text = address.phoneNumber ?? '';
    _isDefault = address.isDefault;
    
    if (address.latitude != null && address.longitude != null) {
      // _selectedLocation = LatLng(address.latitude!, address.longitude!);  // Disabled
      print('📍 Address has coordinates: ${address.latitude}, ${address.longitude}');
    }
  }

  /* GPS methods disabled until proper configuration
  Future<void> _getCurrentLocation() async {ervices.dart';
import 'package:flutter_feather_icons/flutter_feather_icons.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
// import 'package:geolocator/geolocator.dart';  // Disabled until GPS is properly configured
// import 'package:google_maps_flutter/google_maps_flutter.dart';  // Disabled until API key is configured
import '../theme/app_theme.dart';
import '../services/address_service.dart';
import '../models/address.dart';

class AddAddressPage extends StatefulWidget {
  final Address? address;
  final bool useCurrentLocation;

  const AddAddressPage({
    super.key,
    this.address,
    this.useCurrentLocation = false,
  });

  @override
  State<AddAddressPage> createState() => _AddAddressPageState();
}

class _AddAddressPageState extends State<AddAddressPage> {
  final _formKey = GlobalKey<FormState>();
  final _labelController = TextEditingController();
  final _addressController = TextEditingController();
  final _detailController = TextEditingController();
  final _recipientController = TextEditingController();
  final _phoneController = TextEditingController();
  
  // GoogleMapController? _mapController;  // Disabled until API key is configured
  // LatLng? _selectedLocation;  // Disabled until maps are working
  bool _isDefault = false;
  bool _isLoading = false;
  bool _isLoadingLocation = false;

  @override
  void initState() {
    super.initState();
    if (widget.address != null) {
      _loadExistingAddress();
    } else if (widget.useCurrentLocation) {
      // _getCurrentLocation(); // Disabled until GPS is properly configured
      print('⚠️  GPS location disabled - configure Google Maps API first');
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
    
    if (address.latitude != null && address.longitude != null) {
      _selectedLocation = LatLng(address.latitude!, address.longitude!);
    }
  }

  Future<void> _getCurrentLocation() async {
    setState(() => _isLoadingLocation = true);
    
    try {
      final position = await Provider.of<AddressService>(context, listen: false)
          .getCurrentLocation();
      
      if (position != null) {
        setState(() {
          _selectedLocation = LatLng(position.latitude, position.longitude);
        });
        
        // Move map to current location
        if (_mapController != null) {
          await _mapController!.animateCamera(
            CameraUpdate.newLatLngZoom(_selectedLocation!, 16),
          );
        }
        
        // Try to get address from coordinates (reverse geocoding)
        await _getAddressFromCoordinates(_selectedLocation!);
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal mendapatkan lokasi saat ini: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() => _isLoadingLocation = false);
    }
  }

  Future<void> _getAddressFromCoordinates(LatLng location) async {
    // In a real app, you would use a reverse geocoding service here
    // For now, we'll use a placeholder
    setState(() {
      _addressController.text = 'Alamat dari koordinat ${location.latitude.toStringAsFixed(6)}, ${location.longitude.toStringAsFixed(6)}';
    });
  }

  Future<void> _saveAddress() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedLocation == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pilih lokasi di peta terlebih dahulu'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      final addressService = Provider.of<AddressService>(context, listen: false);
      
      final address = Address(
        id: widget.address?.id ?? DateTime.now().millisecondsSinceEpoch.toString(),
        label: _labelController.text.trim(),
        fullAddress: _addressController.text.trim(),
        detailAddress: _detailController.text.trim().isEmpty ? null : _detailController.text.trim(),
        latitude: _selectedLocation!.latitude,
        longitude: _selectedLocation!.longitude,
        recipientName: _recipientController.text.trim().isEmpty ? null : _recipientController.text.trim(),
        phoneNumber: _phoneController.text.trim().isEmpty ? null : _phoneController.text.trim(),
        isDefault: _isDefault,
        createdAt: widget.address?.createdAt ?? DateTime.now(),
        updatedAt: DateTime.now(),
      );

      if (widget.address != null) {
        await addressService.updateAddress(widget.address!.id, address);
      } else {
        await addressService.addAddress(address);
      }

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(widget.address != null ? 'Alamat berhasil diperbarui' : 'Alamat berhasil disimpan'),
          backgroundColor: AppColors.success,
        ),
      );

      Navigator.pop(context);
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal menyimpan alamat: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() => _isLoading = false);
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
          widget.address != null ? 'Edit Alamat' : 'Tambah Alamat Baru',
          style: GoogleFonts.nunitoSans(
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: AppColors.primaryGreen,
          ),
        ),
        actions: [
          TextButton(
            onPressed: _isLoading ? null : _saveAddress,
            child: Text(
              'Simpan',
              style: GoogleFonts.nunitoSans(
                fontWeight: FontWeight.w600,
                color: _isLoading ? AppColors.onSurfaceVariant : AppColors.primaryGreen,
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          // Map Section
          Container(
            height: 250,
            margin: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: AppColors.onSurface.withOpacity(0.1),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Stack(
                children: [
                  // Temporary placeholder for Google Maps
                  // TODO: Add Google Maps API key to enable map functionality
                  Container(
                    height: 200,
                    color: Colors.grey[300],
                    child: Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            FeatherIcons.mapPin,
                            size: 48,
                            color: Colors.grey[600],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Peta tidak tersedia',
                            style: GoogleFonts.poppins(
                              fontSize: 16,
                              color: Colors.grey[600],
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Silakan isi alamat secara manual',
                            style: GoogleFonts.poppins(
                              fontSize: 12,
                              color: Colors.grey[500],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  
                                    
                  // Current Location Button (Disabled - requires Google Maps API)
                  Positioned(
                    top: 16,
                    right: 16,
                    child: FloatingActionButton(
                      mini: true,
                      onPressed: null, // Disabled until Google Maps API is configured
                      backgroundColor: Colors.grey[400],
                      child: const Icon(
                        FeatherIcons.navigation,
                        color: Colors.white,
                        size: 18,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Form Section
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Label Field
                    _buildFormField(
                      label: 'Label Alamat',
                      controller: _labelController,
                      hint: 'Misal: Rumah, Kantor, Kos',
                      icon: FeatherIcons.tag,
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Label alamat harus diisi';
                        }
                        return null;
                      },
                    ),

                    const SizedBox(height: 16),

                    // Address Field
                    _buildFormField(
                      label: 'Alamat Lengkap',
                      controller: _addressController,
                      hint: 'Masukkan alamat lengkap',
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
                      label: 'Nama Penerima (Opsional)',
                      controller: _recipientController,
                      hint: 'Nama orang yang akan menerima',
                      icon: FeatherIcons.user,
                    ),

                    const SizedBox(height: 16),

                    // Phone Number Field
                    _buildFormField(
                      label: 'Nomor Telepon (Opsional)',
                      controller: _phoneController,
                      hint: 'Nomor yang bisa dihubungi',
                      icon: FeatherIcons.phone,
                      keyboardType: TextInputType.phone,
                    ),

                    const SizedBox(height: 20),

                    // Default Address Switch
                    Container(
                      padding: const EdgeInsets.all(16),
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
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: AppColors.primaryGreen.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(FeatherIcons.star, color: AppColors.primaryGreen, size: 20),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Jadikan Alamat Utama',
                                  style: GoogleFonts.nunitoSans(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                    color: AppColors.onSurface,
                                  ),
                                ),
                                Text(
                                  'Alamat ini akan dipilih secara otomatis',
                                  style: GoogleFonts.nunitoSans(
                                    fontSize: 12,
                                    color: AppColors.onSurfaceVariant,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Switch(
                            value: _isDefault,
                            onChanged: (value) => setState(() => _isDefault = value),
                            activeColor: AppColors.primaryGreen,
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 32),

                    // Save Button
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _saveAddress,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primaryGreen,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          elevation: 0,
                        ),
                        child: _isLoading
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2,
                                ),
                              )
                            : Text(
                                widget.address != null ? 'Perbarui Alamat' : 'Simpan Alamat',
                                style: GoogleFonts.nunitoSans(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                      ),
                    ),

                    const SizedBox(height: 32),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFormField({
    required String label,
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    String? Function(String?)? validator,
    TextInputType? keyboardType,
    int maxLines = 1,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.nunitoSans(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: AppColors.onSurfaceVariant,
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
            prefixIcon: Icon(icon, color: AppColors.onSurfaceVariant),
            filled: true,
            fillColor: AppColors.surface,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: AppColors.onSurfaceVariant.withOpacity(0.3)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: AppColors.onSurfaceVariant.withOpacity(0.3)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: AppColors.primaryGreen, width: 2),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.red, width: 2),
            ),
          ),
        ),
      ],
    );
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

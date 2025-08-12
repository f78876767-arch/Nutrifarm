import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

class AddressPickerPage extends StatefulWidget {
  final LatLng? initialPosition;
  const AddressPickerPage({Key? key, this.initialPosition}) : super(key: key);

  @override
  State<AddressPickerPage> createState() => _AddressPickerPageState();
}

class _AddressPickerPageState extends State<AddressPickerPage> {
  late GoogleMapController _mapController;
  LatLng? _pickedPosition;

  @override
  void initState() {
    super.initState();
    _pickedPosition = widget.initialPosition ?? const LatLng(-6.200000, 106.816666); // Jakarta default
  }

  void _onMapCreated(GoogleMapController controller) {
    _mapController = controller;
  }

  void _onTap(LatLng pos) {
    setState(() {
      _pickedPosition = pos;
    });
  }

  void _onConfirm() {
    Navigator.of(context).pop(_pickedPosition);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pilih Lokasi di Peta'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
      ),
      body: Stack(
        children: [
          GoogleMap(
            onMapCreated: _onMapCreated,
            initialCameraPosition: CameraPosition(
              target: _pickedPosition!,
              zoom: 16,
            ),
            onTap: _onTap,
            markers: _pickedPosition == null
                ? {}
                : {
                    Marker(
                      markerId: const MarkerId('picked'),
                      position: _pickedPosition!,
                    ),
                  },
          ),
          Positioned(
            bottom: 32,
            left: 24,
            right: 24,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.black,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                elevation: 0,
              ),
              onPressed: _pickedPosition != null ? _onConfirm : null,
              child: const Text('Pilih Lokasi Ini'),
            ),
          ),
        ],
      ),
    );
  }
}

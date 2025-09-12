import 'package:flutter/foundation.dart';

class JntTariffResult {
  final String serviceName;
  final double cost;
  final String? etd; // estimated delivery time, e.g., 2-3 Days

  JntTariffResult({required this.serviceName, required this.cost, this.etd});

  factory JntTariffResult.fromJson(dynamic raw) {
    if (raw is Map<String, dynamic>) {
      final name = (raw['service_name'] ?? raw['service'] ?? raw['name'] ?? raw['code'] ?? 'J&T').toString();
      final cost = _toDouble(raw['cost'] ?? raw['price'] ?? raw['amount'] ?? raw['fee'] ?? raw['total'] ?? 0);
      final etd = raw['etd']?.toString() ?? raw['estimate']?.toString() ?? raw['estimated']?.toString();
      return JntTariffResult(serviceName: name, cost: cost, etd: etd);
    }
    return JntTariffResult(serviceName: 'J&T', cost: 0);
  }
}

class JntCreateResult {
  final String awb; // airwaybill / billcode
  final String? message;
  final Map<String, dynamic>? raw;

  JntCreateResult({required this.awb, this.message, this.raw});

  factory JntCreateResult.fromJson(Map<String, dynamic> map) {
    // Common keys: billcode, awb, airwaybill, waybill_no
    final bill = (map['billcode'] ?? map['awb'] ?? map['airwaybill'] ?? map['waybill_no'] ?? map['bill_code'] ?? '').toString();
    final msg = map['message']?.toString() ?? map['msg']?.toString();
    return JntCreateResult(awb: bill, message: msg, raw: map);
  }
}

class JntTrackEvent {
  final DateTime? datetime;
  final String status;
  final String? location;
  final String? description;

  JntTrackEvent({required this.status, this.datetime, this.location, this.description});

  factory JntTrackEvent.fromJson(dynamic raw) {
    if (raw is Map<String, dynamic>) {
      // Try multiple datetime fields
      final dtStr = (raw['scan_date_time'] ?? raw['datetime'] ?? raw['date'] ?? raw['time'] ?? raw['event_time'])?.toString();
      DateTime? dt;
      if (dtStr != null && dtStr.isNotEmpty) {
        dt = DateTime.tryParse(dtStr);
      }
      final status = (raw['status'] ?? raw['event'] ?? raw['desc'] ?? raw['message'] ?? 'Updated').toString();
      final loc = (raw['location'] ?? raw['city'] ?? raw['site'] ?? raw['where'])?.toString();
      final desc = (raw['desc'] ?? raw['remark'] ?? raw['detail'] ?? raw['message'])?.toString();
      return JntTrackEvent(status: status, datetime: dt, location: loc, description: desc);
    }
    return JntTrackEvent(status: 'Updated');
  }
}

double _toDouble(dynamic v) {
  if (v == null) return 0.0;
  if (v is num) return v.toDouble();
  return double.tryParse(v.toString()) ?? 0.0;
}

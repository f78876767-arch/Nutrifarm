import 'dart:async';

import '../models/order.dart';

class OrderCreatedEvent {
  final Order order;
  OrderCreatedEvent(this.order);
}

class AppEventBus {
  AppEventBus._internal();
  static final AppEventBus _instance = AppEventBus._internal();
  static AppEventBus get I => _instance;

  final StreamController<OrderCreatedEvent> _orderCreatedController =
      StreamController<OrderCreatedEvent>.broadcast();

  Stream<OrderCreatedEvent> get onOrderCreated => _orderCreatedController.stream;

  void emitOrderCreated(Order order) {
    _orderCreatedController.add(OrderCreatedEvent(order));
  }

  void dispose() {
    _orderCreatedController.close();
  }
}

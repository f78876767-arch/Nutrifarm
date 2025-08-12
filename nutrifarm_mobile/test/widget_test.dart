import 'package:flutter_test/flutter_test.dart';

import 'package:nutrifarm_mobile/main.dart';

void main() {
  testWidgets('App launches successfully', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const MyApp());

    // Verify that our app launches and shows the store title
    expect(find.text('Nutrifarm Store'), findsOneWidget);
    expect(find.text('Store Overview'), findsOneWidget);
  });
}

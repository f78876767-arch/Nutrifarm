import 'dart:io';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:path_provider/path_provider.dart';
import 'package:pdfx/pdfx.dart';
import '../widgets/app_alert.dart';

class PdfViewerPage extends StatefulWidget {
  final String url;
  final String title;
  const PdfViewerPage({super.key, required this.url, required this.title});

  @override
  State<PdfViewerPage> createState() => _PdfViewerPageState();
}

class _PdfViewerPageState extends State<PdfViewerPage> {
  PdfController? _controller;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    try {
      final file = await _download(widget.url);
      if (!mounted) return;
      _controller = PdfController(document: PdfDocument.openFile(file.path));
      setState(() => _loading = false);
    } catch (e) {
      if (!mounted) return;
      AppAlert.showError(context, 'Gagal memuat PDF: $e');
      Navigator.pop(context);
    }
  }

  Future<File> _download(String url) async {
    final res = await http.get(Uri.parse(url));
    if (res.statusCode != 200) throw Exception('HTTP ${res.statusCode}');
    final dir = await getTemporaryDirectory();
    final path = '${dir.path}/temp_${DateTime.now().millisecondsSinceEpoch}.pdf';
    final f = File(path);
    await f.writeAsBytes(res.bodyBytes);
    return f;
  }

  @override
  void dispose() {
    _controller?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.title)),
      body: _loading || _controller == null
          ? const Center(child: CircularProgressIndicator())
          : PdfView(
              controller: _controller!,
              builders: PdfViewBuilders<DefaultBuilderOptions>(
                options: const DefaultBuilderOptions(),
                documentLoaderBuilder: (_) => const Center(child: CircularProgressIndicator()),
                pageLoaderBuilder: (_) => const Center(child: CircularProgressIndicator()),
                errorBuilder: (_, err) => Center(child: Text('Error: $err')),
              ),
            ),
    );
  }
}

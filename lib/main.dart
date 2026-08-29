import 'package:flutter/material.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

const _supabaseUrl = String.fromEnvironment(
  'SUPABASE_URL',
  defaultValue: 'https://lernqnncexhqheboyrkl.supabase.co',
);
const _supabasePublishableKey = String.fromEnvironment(
  'SUPABASE_PUBLISHABLE_KEY',
  defaultValue: 'sb_publishable_7nqlr2DQ4YG-cTlDmVfr0Q_4stSgjvw',
);

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Supabase.initialize(
    url: _supabaseUrl,
    publishableKey: _supabasePublishableKey,
  );
  runApp(const ShabuApp());
}

final supabase = Supabase.instance.client;

class ShabuApp extends StatelessWidget {
  const ShabuApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Shabu Menu',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepOrange),
        useMaterial3: true,
      ),
      home: const MenuItemsPage(),
    );
  }
}

class MenuItemsPage extends StatefulWidget {
  const MenuItemsPage({super.key});

  @override
  State<MenuItemsPage> createState() => _MenuItemsPageState();
}

class _MenuItemsPageState extends State<MenuItemsPage> {
  late Future<List<Map<String, dynamic>>> _itemsFuture;

  @override
  void initState() {
    super.initState();
    _itemsFuture = _loadItems();
  }

  Future<List<Map<String, dynamic>>> _loadItems() async {
    final response = await supabase.from('menu_items').select().order('name');
    return List<Map<String, dynamic>>.from(response);
  }

  Future<void> _reload() async {
    setState(() => _itemsFuture = _loadItems());
    await _itemsFuture;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('รายการเมนูจาก Supabase'),
        actions: [
          IconButton(onPressed: _reload, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _itemsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            final missingTable = snapshot.error is PostgrestException &&
                (snapshot.error as PostgrestException).code == '42P01';
            return _MessageState(
              icon: Icons.storage_outlined,
              title: missingTable
                  ? 'ยังไม่มีตาราง menu_items'
                  : 'ไม่สามารถโหลดข้อมูลได้',
              message: missingTable
                  ? 'สร้างตาราง menu_items ใน Supabase แล้วกดรีเฟรชอีกครั้ง'
                  : snapshot.error.toString(),
              onRetry: _reload,
            );
          }

          final items = snapshot.data ?? [];
          if (items.isEmpty) {
            return _MessageState(
              icon: Icons.restaurant_menu,
              title: 'ยังไม่มีรายการเมนู',
              message: 'เพิ่มข้อมูลในตาราง menu_items แล้วกดรีเฟรช',
              onRetry: _reload,
            );
          }

          return RefreshIndicator(
            onRefresh: _reload,
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: items.length,
              separatorBuilder: (_, _) => const SizedBox(height: 10),
              itemBuilder: (context, index) {
                final item = items[index];
                final name = item['name']?.toString() ?? 'ไม่มีชื่อเมนู';
                final description = item['description']?.toString();
                final price = item['price'];
                return Card(
                  child: ListTile(
                    leading: const CircleAvatar(
                      child: Icon(Icons.ramen_dining),
                    ),
                    title: Text(name),
                    subtitle: description == null || description.isEmpty
                        ? null
                        : Text(description),
                    trailing: price == null ? null : Text('฿$price'),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}

class _MessageState extends StatelessWidget {
  const _MessageState({
    required this.icon,
    required this.title,
    required this.message,
    required this.onRetry,
  });

  final IconData icon;
  final String title;
  final String message;
  final Future<void> Function() onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 48),
            const SizedBox(height: 16),
            Text(title, style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 8),
            Text(message, textAlign: TextAlign.center),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text('ลองอีกครั้ง'),
            ),
          ],
        ),
      ),
    );
  }
}

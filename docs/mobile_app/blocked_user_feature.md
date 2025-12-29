# Blocked User Feature - Mobile App Implementation

## API Reference

### Check Blocked Status
**Endpoint:** `GET /api/v1/user/blocked-info`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response (Not Blocked):**
```json
{
  "status": "success",
  "is_blocked": false,
  "message": "Akaunti yako haijazuiwa."
}
```

**Response (Blocked):**
```json
{
  "status": "blocked",
  "is_blocked": true,
  "blocking_info": {
    "is_blocked": true,
    "blocked_reason": "Auto-blocked: Exceeded suspicious click threshold (20 tasks)",
    "blocked_at": "2025-12-29T10:00:00.000000Z",
    "blocked_by": "System (Auto-block)",
    "total_flagged_clicks": 20,
    "auto_block_threshold": 20
  },
  "support": {
    "whatsapp": "255700000000",
    "whatsapp_url": "https://wa.me/255700000000",
    "message": "Habari Admin, naomba msaada. Akaunti yangu imezuiwa. Jina: John Doe, Email: john@example.com"
  },
  "instructions": {
    "sw": "Akaunti yako imezuiwa kwa sababu ya shughuli za tuhuma...",
    "en": "Your account has been blocked due to suspicious activity..."
  }
}
```

---

## Flutter Implementation

### 1. Model - `lib/models/blocked_info.dart`

```dart
class BlockedInfo {
  final bool isBlocked;
  final String? blockedReason;
  final String? blockedAt;
  final String? blockedBy;
  final int totalFlaggedClicks;
  final int autoBlockThreshold;
  final String? whatsappNumber;
  final String? whatsappUrl;
  final String? supportMessage;
  final String? instructionsSw;
  final String? instructionsEn;

  BlockedInfo({
    required this.isBlocked,
    this.blockedReason,
    this.blockedAt,
    this.blockedBy,
    this.totalFlaggedClicks = 0,
    this.autoBlockThreshold = 20,
    this.whatsappNumber,
    this.whatsappUrl,
    this.supportMessage,
    this.instructionsSw,
    this.instructionsEn,
  });

  factory BlockedInfo.fromJson(Map<String, dynamic> json) {
    final blockingInfo = json['blocking_info'] ?? {};
    final support = json['support'] ?? {};
    final instructions = json['instructions'] ?? {};

    return BlockedInfo(
      isBlocked: json['is_blocked'] ?? false,
      blockedReason: blockingInfo['blocked_reason'],
      blockedAt: blockingInfo['blocked_at'],
      blockedBy: blockingInfo['blocked_by'],
      totalFlaggedClicks: blockingInfo['total_flagged_clicks'] ?? 0,
      autoBlockThreshold: blockingInfo['auto_block_threshold'] ?? 20,
      whatsappNumber: support['whatsapp'],
      whatsappUrl: support['whatsapp_url'],
      supportMessage: support['message'],
      instructionsSw: instructions['sw'],
      instructionsEn: instructions['en'],
    );
  }

  factory BlockedInfo.notBlocked() {
    return BlockedInfo(isBlocked: false);
  }
}
```

---

### 2. API Service - Add to `lib/services/api_service.dart`

```dart
/// Check if user is blocked
Future<BlockedInfo> checkBlockedStatus() async {
  try {
    final response = await _dio.get('/user/blocked-info');
    return BlockedInfo.fromJson(response.data);
  } on DioException catch (e) {
    if (e.response?.statusCode == 403) {
      // User is blocked
      return BlockedInfo.fromJson(e.response?.data ?? {});
    }
    rethrow;
  }
}
```

---

### 3. Blocked Screen - `lib/screens/blocked_screen.dart`

```dart
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/blocked_info.dart';
import '../providers/auth_provider.dart';
import '../theme/app_colors.dart';

class BlockedScreen extends StatelessWidget {
  final BlockedInfo blockedInfo;

  const BlockedScreen({
    Key? key,
    required this.blockedInfo,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundDarker,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Warning Icon with Glow Effect
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: AppColors.error.withOpacity(0.1),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.error.withOpacity(0.3),
                      blurRadius: 30,
                      spreadRadius: 5,
                    ),
                  ],
                ),
                child: const Icon(
                  Icons.block_rounded,
                  size: 80,
                  color: AppColors.error,
                ),
              ),
              
              const SizedBox(height: 32),
              
              // Title
              const Text(
                'Akaunti Imezuiwa',
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
                textAlign: TextAlign.center,
              ),
              
              const SizedBox(height: 16),
              
              // Reason Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: AppColors.backgroundCard,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: AppColors.error.withOpacity(0.3),
                    width: 1,
                  ),
                ),
                child: Column(
                  children: [
                    const Icon(
                      Icons.warning_amber_rounded,
                      color: AppColors.warning,
                      size: 32,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      blockedInfo.blockedReason ?? 'Shughuli za tuhuma zimegunduliwa',
                      style: const TextStyle(
                        fontSize: 14,
                        color: AppColors.textSecondary,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    if (blockedInfo.blockedAt != null) ...[
                      const SizedBox(height: 8),
                      Text(
                        'Tarehe: ${_formatDate(blockedInfo.blockedAt!)}',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary.withOpacity(0.7),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              
              const SizedBox(height: 24),
              
              // Instructions
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.info.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: AppColors.info.withOpacity(0.3),
                  ),
                ),
                child: Column(
                  children: [
                    const Icon(
                      Icons.info_outline,
                      color: AppColors.info,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      blockedInfo.instructionsSw ?? 
                        'Wasiliana na admin kupitia WhatsApp ili kuomba kufunguliwa.',
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppColors.textSecondary,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
              
              const Spacer(),
              
              // Contact Admin Button (WhatsApp)
              SizedBox(
                width: double.infinity,
                height: 56,
                child: ElevatedButton.icon(
                  onPressed: () => _contactAdmin(context),
                  icon: const Icon(Icons.chat, color: Colors.white),
                  label: const Text(
                    'Wasiliana na Admin (WhatsApp)',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Colors.white,
                    ),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF25D366), // WhatsApp green
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 4,
                  ),
                ),
              ),
              
              const SizedBox(height: 16),
              
              // Refresh Status Button
              SizedBox(
                width: double.infinity,
                height: 56,
                child: OutlinedButton.icon(
                  onPressed: () => _refreshStatus(context),
                  icon: const Icon(Icons.refresh, color: AppColors.primary),
                  label: const Text(
                    'Angalia Hali Tena',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: AppColors.primary,
                    ),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: AppColors.primary),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
              
              const SizedBox(height: 16),
              
              // Logout Button
              TextButton(
                onPressed: () => _logout(context),
                child: const Text(
                  'Toka Akaunti',
                  style: TextStyle(
                    color: AppColors.textSecondary,
                    fontSize: 14,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day}/${date.month}/${date.year}';
    } catch (e) {
      return dateStr;
    }
  }

  Future<void> _contactAdmin(BuildContext context) async {
    final url = blockedInfo.whatsappUrl;
    if (url != null) {
      final uri = Uri.parse('$url?text=${Uri.encodeComponent(blockedInfo.supportMessage ?? '')}');
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Imeshindikana kufungua WhatsApp')),
          );
        }
      }
    }
  }

  Future<void> _refreshStatus(BuildContext context) async {
    // Show loading
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const Center(
        child: CircularProgressIndicator(color: AppColors.primary),
      ),
    );

    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      await authProvider.checkBlockedStatus();
      
      if (context.mounted) {
        Navigator.of(context).pop(); // Close loading
        
        if (!authProvider.isBlocked) {
          // User is unblocked! Navigate to home
          Navigator.of(context).pushReplacementNamed('/home');
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Akaunti yako imefunguliwa! Karibu tena.'),
              backgroundColor: AppColors.success,
            ),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Akaunti bado imezuiwa. Jaribu tena baadaye.'),
              backgroundColor: AppColors.warning,
            ),
          );
        }
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.of(context).pop(); // Close loading
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Kuna tatizo. Jaribu tena.')),
        );
      }
    }
  }

  void _logout(BuildContext context) {
    Provider.of<AuthProvider>(context, listen: false).logout();
    Navigator.of(context).pushReplacementNamed('/login');
  }
}
```

---

### 4. Auth Provider Updates - `lib/providers/auth_provider.dart`

Add these to your existing AuthProvider:

```dart
class AuthProvider extends ChangeNotifier {
  // ... existing code ...
  
  bool _isBlocked = false;
  BlockedInfo? _blockedInfo;

  bool get isBlocked => _isBlocked;
  BlockedInfo? get blockedInfo => _blockedInfo;

  /// Check blocked status after login or periodically
  Future<void> checkBlockedStatus() async {
    try {
      final info = await _apiService.checkBlockedStatus();
      _isBlocked = info.isBlocked;
      _blockedInfo = info;
      notifyListeners();
    } catch (e) {
      // Handle error
      debugPrint('Error checking blocked status: $e');
    }
  }

  /// Updated login method
  Future<LoginResult> login(String email, String password) async {
    try {
      final response = await _apiService.login(email, password);
      
      // Check if blocked from login response
      if (response['is_blocked'] == true) {
        _isBlocked = true;
        _blockedInfo = BlockedInfo.fromJson(response['data'] ?? {});
        await _saveToken(response['data']['token']);
        notifyListeners();
        return LoginResult.blocked;
      }
      
      _isBlocked = false;
      _blockedInfo = null;
      await _saveToken(response['data']['token']);
      notifyListeners();
      return LoginResult.success;
      
    } catch (e) {
      return LoginResult.error;
    }
  }
}

enum LoginResult { success, blocked, error }
```

---

### 5. App Router - Handle Blocked State

In your main app or router:

```dart
class AppRouter {
  static Route<dynamic> generateRoute(RouteSettings settings) {
    // ... existing routes ...
  }
}

// In your main app widget:
class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, auth, child) {
        // If user is blocked, always show blocked screen
        if (auth.isAuthenticated && auth.isBlocked) {
          return MaterialApp(
            home: BlockedScreen(blockedInfo: auth.blockedInfo!),
            // ... theme settings ...
          );
        }
        
        // Normal app flow
        return MaterialApp(
          // ... normal routes ...
        );
      },
    );
  }
}
```

---

### 6. Handle 403 Responses Globally

Add an interceptor to handle blocked responses:

```dart
// In dio_client.dart or api_service.dart
_dio.interceptors.add(InterceptorsWrapper(
  onError: (error, handler) {
    if (error.response?.statusCode == 403) {
      final data = error.response?.data;
      if (data != null && data['is_blocked'] == true) {
        // Navigate to blocked screen
        navigatorKey.currentState?.pushAndRemoveUntil(
          MaterialPageRoute(
            builder: (_) => BlockedScreen(
              blockedInfo: BlockedInfo.fromJson(data),
            ),
          ),
          (route) => false,
        );
        return;
      }
    }
    handler.next(error);
  },
));
```

---

## Summary of Changes Needed

1. **Add `BlockedInfo` model** - Parse API response
2. **Add API method** - `checkBlockedStatus()`
3. **Create `BlockedScreen`** - UI for blocked users
4. **Update `AuthProvider`** - Track blocked state
5. **Update App Router** - Redirect blocked users
6. **Add Dio Interceptor** - Handle 403 globally

## API Endpoints Used

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/user/blocked-info` | GET | Check if user is blocked |
| `/api/v1/auth/login` | POST | Login (includes `is_blocked` in response) |


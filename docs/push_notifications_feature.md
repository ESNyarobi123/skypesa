# Push Notifications Feature Documentation

## Overview
This feature enables admin to send push notifications to mobile app users via Firebase Cloud Messaging (FCM). It also saves in-app notifications alongside push notifications.

## Features

### Admin Features
1. **Send Push Notifications**
   - Send to all users
   - Send to specific user segments (Premium, Free, Active, Inactive, New)
   - Send to specific selected users
   - Optional image attachments
   - Optional deep link URLs

2. **View Notification History**
   - List all sent notifications
   - Filter by status (completed, failed, sending, pending)
   - Filter by target type (all, segment, specific)
   - View delivery statistics (success/failure counts)

3. **FCM Token Management**
   - View all registered devices with tokens
   - Filter by device type (Android, iOS, Web)
   - Send test notifications to individual devices
   - Remove invalid/old tokens

4. **Delivery Statistics**
   - Total tokens attempted
   - Success count
   - Failure count
   - Success rate percentage
   - Error details for failed deliveries

### Mobile App Integration

#### API Endpoint for FCM Token Registration
```
POST /api/v1/user/fcm-token
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "fcm_token": "your_fcm_token_from_firebase",
    "device_type": "android"  // or "ios", "web"
}
```

**Response:**
```json
{
    "success": true,
    "message": "FCM token imehifadhiwa."
}
```

#### Flutter Implementation Example
```dart
import 'package:firebase_messaging/firebase_messaging.dart';

class NotificationService {
  final FirebaseMessaging _fcm = FirebaseMessaging.instance;
  
  Future<void> initialize() async {
    // Request permission
    NotificationSettings settings = await _fcm.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    
    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      // Get FCM token
      String? token = await _fcm.getToken();
      
      if (token != null) {
        // Send token to backend
        await _registerToken(token);
      }
      
      // Listen for token refresh
      _fcm.onTokenRefresh.listen((newToken) {
        _registerToken(newToken);
      });
    }
    
    // Handle foreground messages
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);
    
    // Handle background messages
    FirebaseMessaging.onBackgroundMessage(_handleBackgroundMessage);
  }
  
  Future<void> _registerToken(String token) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/v1/user/fcm-token'),
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'fcm_token': token,
        'device_type': Platform.isAndroid ? 'android' : 'ios',
      }),
    );
  }
  
  void _handleForegroundMessage(RemoteMessage message) {
    // Show local notification or in-app alert
    print('Received message: ${message.notification?.title}');
  }
}

@pragma('vm:entry-point')
Future<void> _handleBackgroundMessage(RemoteMessage message) async {
  print('Background message: ${message.notification?.title}');
}
```

## Database Schema

### users table - New columns
```sql
ALTER TABLE users ADD COLUMN fcm_token VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN device_type VARCHAR(20) DEFAULT 'android';
ALTER TABLE users ADD COLUMN fcm_token_updated_at TIMESTAMP NULL;
```

### push_notifications table
```sql
CREATE TABLE push_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    body TEXT NOT NULL,
    data JSON,
    image_url VARCHAR(255),
    target_type ENUM('all', 'specific', 'segment') DEFAULT 'all',
    target_users JSON,
    segment VARCHAR(50),
    total_tokens INT DEFAULT 0,
    success_count INT DEFAULT 0,
    failure_count INT DEFAULT 0,
    error_details JSON,
    status ENUM('pending', 'sending', 'completed', 'failed') DEFAULT 'pending',
    sent_by BIGINT NOT NULL REFERENCES users(id),
    sent_at TIMESTAMP,
    completed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## User Segments

| Segment | Description |
|---------|-------------|
| `all` | All active users with FCM tokens |
| `premium` | Users with paid subscription plans |
| `free` | Users with free plan |
| `active` | Users who logged in within 24 hours |
| `inactive` | Users who haven't logged in for 7+ days |
| `new` | Users registered in last 7 days |

## Firebase Configuration

The Firebase Admin SDK credentials file should be placed at:
```
project_root/sky-pesa-firebase-adminsdk-fbsvc-6ac6dd3f6d.json
```

### Firebase Console Setup
1. Go to Firebase Console > Project Settings > Service Accounts
2. Click "Generate new private key"
3. Save the JSON file as shown above
4. Enable Firebase Cloud Messaging API in Google Cloud Console

## Admin Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/admin/push-notifications` | List all push notifications |
| GET | `/admin/push-notifications/create` | Send new notification form |
| POST | `/admin/push-notifications` | Send notification |
| GET | `/admin/push-notifications/tokens` | View FCM tokens |
| GET | `/admin/push-notifications/{id}` | View notification details |
| POST | `/admin/push-notifications/{id}/resend` | Resend failed notification |
| DELETE | `/admin/push-notifications/{id}` | Delete notification record |
| POST | `/admin/push-notifications/test/{user}` | Send test to user |
| DELETE | `/admin/push-notifications/token/{user}` | Remove user's token |

## Error Handling

The system handles these FCM errors:
- `UNREGISTERED` - Token is invalid (automatically removed from DB)
- `INVALID_ARGUMENT` - Token format is invalid
- Rate limiting - 50ms delay between requests

## In-App Notifications

When a push notification is sent, an in-app notification is also created for each targeted user. This ensures users who don't receive push notifications can still see the message in their notification center.

## Files Created/Modified

### Created
- `database/migrations/2025_12_30_010000_add_fcm_token_to_users_table.php`
- `database/migrations/2025_12_30_010001_create_push_notifications_table.php`
- `app/Models/PushNotification.php`
- `app/Services/FirebaseService.php`
- `app/Http/Controllers/Admin/AdminPushNotificationController.php`
- `resources/views/admin/push-notifications/index.blade.php`
- `resources/views/admin/push-notifications/create.blade.php`
- `resources/views/admin/push-notifications/show.blade.php`
- `resources/views/admin/push-notifications/tokens.blade.php`

### Modified
- `app/Models/User.php` - Added fcm_token fields
- `app/Http/Controllers/Api/UserController.php` - Updated updateFcmToken method
- `routes/web.php` - Added admin push notification routes
- `resources/views/layouts/admin.blade.php` - Added sidebar link

## Dependencies Added
```bash
composer require google/auth
```

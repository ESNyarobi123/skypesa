# 📱 SKYpesa Mobile API Reference
### Complete API Documentation with Request/Response Examples

---

## 🔧 Configuration

| Setting | Value |
|---------|-------|
| **Base URL** | `https://skypesa.hosting.hollyn.online/api/v1` |
| **Content-Type** | `application/json` |
| **Accept** | `application/json` |
| **Auth Header** | `Authorization: Bearer {token}` |

---

## 📐 Standard Response Format

All responses follow this structure:

```json
{
  "success": true,
  "message": "Human readable message",
  "data": { ... },
  "errors": { ... },
  "meta": { ... }
}
```

---

## 🚦 HTTP Status Codes

| Code | Meaning | When Used |
|------|---------|-----------|
| `200` | OK | Successful GET/PUT request |
| `201` | Created | Successful POST (resource created) |
| `400` | Bad Request | Invalid data or business logic error |
| `401` | Unauthorized | Missing or invalid token |
| `403` | Forbidden | Account blocked or permission denied |
| `404` | Not Found | Resource doesn't exist |
| `410` | Gone | Task expired |
| `422` | Validation Error | Input validation failed |
| `423` | Locked | Task already in progress by another user |
| `425` | Too Early | Timer not complete yet |
| `500` | Server Error | Internal server error |

---

# 🔐 AUTHENTICATION

## 1. Register New User

**Endpoint:** `POST /auth/register`  
**Auth Required:** No

### Request Body
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "0712345678",
  "password": "secret123",
  "password_confirmation": "secret123",
  "referral_code": "ABC12345"  // Optional
}
```

### Success Response (201)
```json
{
  "success": true,
  "message": "Akaunti imefunguliwa!",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "0712345678",
      "avatar": "https://skypesa.site/storage/avatars/default.png",
      "role": "user",
      "referral_code": "XYZ98765",
      "is_verified": false,
      "wallet": {
        "balance": 0
      },
      "subscription": {
        "plan": "Free",
        "expires_at": null
      },
      "created_at": "2024-12-26T12:00:00.000Z"
    },
    "token": "1|abcdef123456...",
    "token_type": "Bearer"
  }
}
```

### Error Response (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email hii imetumika tayari"],
    "phone": ["Namba hii imetumika tayari"]
  }
}
```

---

## 2. Login

**Endpoint:** `POST /auth/login`  
**Auth Required:** No

### Request Body
```json
{
  "email": "john@example.com",
  "password": "secret123",
  "device_name": "Samsung Galaxy S21"  // Optional
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Karibu tena!",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "0712345678",
      "avatar": "https://skypesa.site/storage/avatars/user-1.png",
      "role": "user",
      "referral_code": "XYZ98765",
      "is_verified": true,
      "wallet": {
        "balance": 15000
      },
      "subscription": {
        "plan": "Silver",
        "expires_at": "2025-01-26T12:00:00.000Z"
      },
      "created_at": "2024-12-01T12:00:00.000Z"
    },
    "token": "2|xyz789...",
    "token_type": "Bearer"
  }
}
```

### Error: Wrong Credentials (401)
```json
{
  "success": false,
  "message": "Email au password si sahihi"
}
```

### Error: Account Blocked (403)
```json
{
  "success": false,
  "message": "Akaunti yako imezuiwa. Wasiliana na msaada."
}
```

---

## 3. Logout

**Endpoint:** `POST /auth/logout`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "message": "Umetoka kwenye akaunti"
}
```

---

## 4. Forgot Password

**Endpoint:** `POST /auth/forgot-password`  
**Auth Required:** No

### Request Body
```json
{
  "email": "john@example.com"
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Maelekezo ya kubadilisha password yametumwa kwenye email yako"
}
```

---

## 5. Reset Password

**Endpoint:** `POST /auth/reset-password`  
**Auth Required:** No

### Request Body
```json
{
  "email": "john@example.com",
  "token": "123456",  // OTP from email
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Password imebadilishwa. Ingia sasa."
}
```

### Error: Invalid OTP (400)
```json
{
  "success": false,
  "message": "Kodi ya uhakiki si sahihi au imeisha muda wake"
}
```

---

## 6. Refresh Token

**Endpoint:** `POST /auth/refresh`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "token": "3|newtoken...",
    "token_type": "Bearer"
  }
}
```

---

# 👤 USER PROFILE

## 1. Get Profile

**Endpoint:** `GET /user/profile`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "0712345678",
    "avatar": "https://skypesa.site/storage/avatars/user-1.png",
    "role": "user",
    "referral_code": "XYZ98765",
    "is_verified": true,
    "wallet": {
      "balance": 15000,
      "total_earned": 50000,
      "total_withdrawn": 35000
    },
    "subscription": {
      "id": 5,
      "plan": {
        "id": 2,
        "name": "Silver",
        "slug": "silver"
      },
      "status": "active",
      "started_at": "2024-12-01T12:00:00.000Z",
      "expires_at": "2025-01-01T12:00:00.000Z"
    },
    "stats": {
      "tasks_completed_today": 5,
      "daily_task_limit": 10,
      "remaining_tasks_today": 5,
      "reward_per_task": 500
    },
    "created_at": "2024-11-01T12:00:00.000Z",
    "last_login_at": "2024-12-26T08:00:00.000Z"
  }
}
```

---

## 2. Get Dashboard Stats

**Endpoint:** `GET /user/dashboard`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "wallet_balance": 15000,
    "tasks_today": 5,
    "tasks_limit": 10,
    "tasks_remaining": 5,
    "reward_per_task": 500,
    "earnings": {
      "today": 2500,
      "this_week": 12500,
      "this_month": 45000
    },
    "subscription": "Silver",
    "referral_count": 12
  }
}
```

---

## 3. Update Profile

**Endpoint:** `PUT /user/profile`  
**Auth Required:** ✅ Yes

### Request Body
```json
{
  "name": "John Updated",
  "phone": "0798765432"
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Maelezo yamebadilishwa",
  "data": { ... }
}
```

---

## 4. Change Password

**Endpoint:** `PUT /user/password`  
**Auth Required:** ✅ Yes

### Request Body
```json
{
  "current_password": "old123",
  "password": "new456",
  "password_confirmation": "new456"
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Password imebadilishwa"
}
```

### Error: Wrong Current Password (400)
```json
{
  "success": false,
  "message": "Password ya sasa si sahihi"
}
```

---

## 5. Update FCM Token (Push Notifications)

**Endpoint:** `POST /user/fcm-token`  
**Auth Required:** ✅ Yes

### Request Body
```json
{
  "fcm_token": "dQw4w9WgXcQ:APA91bH...",
  "device_type": "android"  // android, ios, web
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "FCM token updated"
}
```

---

# 📋 TASKS

## 1. List Available Tasks

**Endpoint:** `GET /tasks`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "tasks": [
      {
        "id": 1,
        "title": "Tazama Video Ad",
        "description": "Tazama tangazo hadi mwisho",
        "type": "direct_link",
        "provider": "monetag",
        "duration_seconds": 30,
        "reward": 500,
        "daily_limit": 5,
        "completions_today": 2,
        "remaining_today": 3,
        "can_complete": true,
        "is_featured": true,
        "thumbnail": "https://skypesa.site/img/task-1.png",
        "icon": "video"
      },
      {
        "id": 2,
        "title": "Download App",
        "description": "Install na ufungue app kwa sekunde 10",
        "type": "direct_link",
        "provider": "adsterra",
        "duration_seconds": 45,
        "reward": 500,
        "daily_limit": 3,
        "completions_today": 0,
        "remaining_today": 3,
        "can_complete": true,
        "is_featured": false,
        "thumbnail": null,
        "icon": "download"
      }
    ],
    "activity": {
      "has_active_task": false,
      "active_task": null
    },
    "stats": {
      "completed_today": 5,
      "daily_limit": 10,
      "remaining_today": 5,
      "reward_per_task": 500
    },
    "plan_info": {
      "name": "Silver",
      "reward_per_task": 500
    }
  }
}
```

---

## 2. Start Task (Lock It)

**Endpoint:** `POST /tasks/{task_id}/start`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "message": "Kazi imeanza!",
  "data": {
    "lock_token": "a1b2c3d4e5f6...",  // 64 char - SAVE THIS!
    "duration": 30,
    "started_at": "2024-12-26T10:00:00.000Z",
    "task_url": "https://example.com/ad123"
  }
}
```

> ⚠️ **IMPORTANT:** Save the `lock_token`! You need it for status check and completion.

### Error: Daily Limit (403)
```json
{
  "success": false,
  "message": "Umefika limit ya tasks za leo. Upgrade mpango wako!",
  "error_code": "DAILY_LIMIT_REACHED"
}
```

### Error: Task Limit (403)
```json
{
  "success": false,
  "message": "Umeshakamilisha kazi hii mara nyingi leo",
  "error_code": "TASK_LIMIT_REACHED"
}
```

### Error: Task Locked by Another (423)
```json
{
  "success": false,
  "message": "Una task nyingine inayoendelea"
}
```

---

## 3. Check Task Status

**Endpoint:** `POST /tasks/{task_id}/status`  
**Auth Required:** ✅ Yes

### Request Body
```json
{
  "lock_token": "a1b2c3d4e5f6..."
}
```

### Success Response (200) - Timer Running
```json
{
  "success": true,
  "data": {
    "elapsed": 15,
    "remaining": 15,
    "required": 30,
    "can_complete": false,
    "started_at": "2024-12-26T10:00:00.000Z"
  }
}
```

### Success Response (200) - Ready to Complete
```json
{
  "success": true,
  "data": {
    "elapsed": 32,
    "remaining": 0,
    "required": 30,
    "can_complete": true,
    "started_at": "2024-12-26T10:00:00.000Z"
  }
}
```

### Error: Task Expired (410)
```json
{
  "success": false,
  "message": "Kazi hii imekwisha muda wake. Anza upya.",
  "expired": true
}
```

---

## 4. Complete Task

**Endpoint:** `POST /tasks/{task_id}/complete`  
**Auth Required:** ✅ Yes

### Request Body
```json
{
  "lock_token": "a1b2c3d4e5f6..."
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Hongera! Umepata TZS 500",
  "data": {
    "reward": 500,
    "new_balance": 15500,
    "duration_spent": 32
  }
}
```

### Error: Timer Not Complete (425)
```json
{
  "success": false,
  "message": "Subiri timer ikamilike",
  "error_code": "TIME_NOT_COMPLETE"
}
```

---

## 5. Cancel Active Task

**Endpoint:** `POST /tasks/cancel`  
**Auth Required:** ✅ Yes

### Request Body
```json
{
  "lock_token": "a1b2c3d4e5f6..."
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Kazi imesitishwa"
}
```

---

## 6. Get Active Task

**Endpoint:** `GET /tasks/activity/current`  
**Auth Required:** ✅ Yes

### Response - Has Active Task
```json
{
  "success": true,
  "data": {
    "has_active_task": true,
    "active_task": {
      "task_id": 1,
      "task_title": "Tazama Video Ad",
      "lock_token": "a1b2c3d4...",
      "started_at": "2024-12-26T10:00:00.000Z",
      "required_duration": 30,
      "elapsed": 15,
      "remaining": 15
    }
  }
}
```

### Response - No Active Task
```json
{
  "success": true,
  "data": {
    "has_active_task": false,
    "active_task": null
  }
}
```

---

## 7. Get Task History

**Endpoint:** `GET /tasks/history/completed`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "task": {
        "id": 1,
        "title": "Tazama Video Ad"
      },
      "reward_earned": 500,
      "status": "completed",
      "created_at": "2024-12-26T09:30:00.000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

---

# 💰 WALLET & TRANSACTIONS

## 1. Get Wallet Info

**Endpoint:** `GET /wallet`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "balance": 15000,
    "total_earned": 50000,
    "total_withdrawn": 35000,
    "pending_withdrawals": 5000
  }
}
```

---

## 2. Get Transaction History

**Endpoint:** `GET /wallet/transactions`  
**Auth Required:** ✅ Yes  
**Query Params:**
- `type`: `credit` | `debit` | `all` (optional)
- `page`: Page number for pagination

### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "credit",
      "amount": 500,
      "description": "Malipo ya task: Tazama Video Ad",
      "reference_type": "task_reward",
      "created_at": "2024-12-26T10:00:00.000Z"
    },
    {
      "id": 2,
      "type": "debit",
      "amount": 5000,
      "description": "Withdrawal request",
      "reference_type": "withdrawal",
      "created_at": "2024-12-25T15:00:00.000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200
  }
}
```

---

## 3. Get Earnings Summary

**Endpoint:** `GET /wallet/earnings`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "today": 2500,
    "this_week": 12500,
    "this_month": 45000,
    "total": 150000,
    "daily_breakdown": [
      { "date": "2024-12-20", "day": "Fri", "amount": 3000 },
      { "date": "2024-12-21", "day": "Sat", "amount": 2500 },
      { "date": "2024-12-22", "day": "Sun", "amount": 4000 },
      { "date": "2024-12-23", "day": "Mon", "amount": 3500 },
      { "date": "2024-12-24", "day": "Tue", "amount": 2000 },
      { "date": "2024-12-25", "day": "Wed", "amount": 1500 },
      { "date": "2024-12-26", "day": "Thu", "amount": 2500 }
    ]
  }
}
```

---

# 💸 WITHDRAWALS

## 1. Get Withdrawal Info

**Endpoint:** `GET /withdrawals/info`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "balance": 15000,
    "min_withdrawal": 5000,
    "withdrawal_fee_percent": 10,
    "can_withdraw": true,
    "pending_withdrawals": 0,
    "payment_methods": [
      { "id": "mpesa", "name": "M-Pesa", "icon": "phone" },
      { "id": "tigopesa", "name": "Tigo Pesa", "icon": "phone" },
      { "id": "airtel", "name": "Airtel Money", "icon": "phone" },
      { "id": "halopesa", "name": "Halo Pesa", "icon": "phone" }
    ]
  }
}
```

---

## 2. Create Withdrawal Request

**Endpoint:** `POST /withdrawals`  
**Auth Required:** ✅ Yes

### Request Body
```json
{
  "amount": 10000,
  "payment_method": "mpesa",
  "phone_number": "0712345678"
}
```

### Success Response (201)
```json
{
  "success": true,
  "message": "Ombi lako limepokelewa. Utapata pesa ndani ya masaa 24-48.",
  "data": {
    "id": 123,
    "amount": 10000,
    "fee": 1000,
    "net_amount": 9000,
    "payment_method": "mpesa",
    "phone_number": "0712345678",
    "status": "pending",
    "created_at": "2024-12-26T10:00:00.000Z"
  }
}
```

### Error: Insufficient Balance (400)
```json
{
  "success": false,
  "message": "Salio haitoshi"
}
```

### Error: Below Minimum (400)
```json
{
  "success": false,
  "message": "Kiwango cha chini ni TZS 5,000"
}
```

### Error: Pending Withdrawal Exists (400)
```json
{
  "success": false,
  "message": "Una ombi lingine linalosubiri. Subiri likamilike kwanza."
}
```

---

## 3. Cancel Withdrawal

**Endpoint:** `DELETE /withdrawals/{id}`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "message": "Ombi limefutwa na pesa imerudi kwenye wallet"
}
```

---

# 🏆 SUBSCRIPTION PLANS

## 1. List All Plans

**Endpoint:** `GET /plans`  
**Auth Required:** No

### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Free",
      "slug": "free",
      "description": "Mpango wa bure",
      "price": 0,
      "duration_days": null,
      "daily_task_limit": 5,
      "reward_per_task": 100,
      "min_withdrawal": 10000,
      "withdrawal_fee_percent": 20,
      "features": ["5 tasks/day", "TZS 100 per task"],
      "is_popular": false
    },
    {
      "id": 2,
      "name": "Silver",
      "slug": "silver",
      "description": "Mpango wa wastani",
      "price": 5000,
      "duration_days": 30,
      "daily_task_limit": 10,
      "reward_per_task": 500,
      "min_withdrawal": 5000,
      "withdrawal_fee_percent": 10,
      "features": ["10 tasks/day", "TZS 500 per task", "Low fees"],
      "is_popular": true
    },
    {
      "id": 3,
      "name": "VIP",
      "slug": "vip",
      "description": "Mpango wa juu",
      "price": 20000,
      "duration_days": 30,
      "daily_task_limit": 200,
      "reward_per_task": 1000,
      "min_withdrawal": 2000,
      "withdrawal_fee_percent": 5,
      "features": ["Unlimited tasks", "TZS 1000 per task", "Lowest fees"],
      "is_popular": false
    }
  ]
}
```

---

## 2. Get Current Subscription

**Endpoint:** `GET /subscriptions/current`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "id": 5,
    "plan": {
      "id": 2,
      "name": "Silver",
      "slug": "silver"
    },
    "status": "active",
    "started_at": "2024-12-01T12:00:00.000Z",
    "expires_at": "2025-01-01T12:00:00.000Z",
    "is_expired": false,
    "days_remaining": 6
  }
}
```

---

## 3. Initiate Payment

**Endpoint:** `POST /subscriptions/pay/{plan_id}`  
**Auth Required:** ✅ Yes

### Request Body
```json
{
  "phone_number": "0712345678"
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Ombi la malipo limepokewa. Kamilisha kwenye simu yako.",
  "data": {
    "order_id": "SKY-1234567890",
    "amount": 5000,
    "phone": "0712345678",
    "status": "pending",
    "check_status_url": "https://skypesa.site/api/v1/subscriptions/payment-status/SKY-1234567890",
    "demo_mode": false
  }
}
```

---

## 4. Check Payment Status

**Endpoint:** `GET /subscriptions/payment-status/{order_id}`  
**Auth Required:** ✅ Yes

### Response - Completed
```json
{
  "success": true,
  "data": {
    "order_id": "SKY-1234567890",
    "status": "completed",
    "message": "Malipo yamekamilika! Mpango wako umeanzishwa."
  }
}
```

### Response - Pending
```json
{
  "success": true,
  "data": {
    "order_id": "SKY-1234567890",
    "status": "pending",
    "message": "Inasubiri malipo..."
  }
}
```

### Response - Failed
```json
{
  "success": false,
  "data": {
    "order_id": "SKY-1234567890",
    "status": "failed",
    "message": "Malipo yameshindwa."
  }
}
```

---

# 👥 REFERRALS

## 1. Get Referral Info

**Endpoint:** `GET /referrals`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "referral_code": "XYZ98765",
    "referral_link": "https://skypesa.site/register?ref=XYZ98765",
    "total_referrals": 12,
    "active_referrals": 8,
    "total_earnings": 6000,
    "share_message": "Jiunge na SKYpesa na upate pesa kwa kutazama matangazo! Tumia code yangu: XYZ98765. https://skypesa.site/register?ref=XYZ98765"
  }
}
```

---

## 2. Get Referred Users

**Endpoint:** `GET /referrals/users`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 25,
      "name": "Jane D.",
      "tasks_completed": 45,
      "joined_at": "2024-12-20T12:00:00.000Z",
      "is_active": true
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 12
  }
}
```

---

# 🔔 NOTIFICATIONS

## 1. Get Notifications

**Endpoint:** `GET /notifications`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "id": "abc123",
      "type": "withdrawal_approved",
      "title": "Withdrawal Approved",
      "message": "Ombi lako la TZS 5,000 limekubaliwa",
      "is_read": false,
      "created_at": "2024-12-26T10:00:00.000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25
  }
}
```

---

## 2. Get Unread Count

**Endpoint:** `GET /notifications/unread-count`  
**Auth Required:** ✅ Yes

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "count": 5
  }
}
```

---

# 🔄 WEBHOOKS / CALLBACKS

These endpoints are called by external services (ZenoPay, etc.) to notify the backend of events.

## ZenoPay Payment Callback

**Endpoint:** `POST /webhooks/zenopay`  
**Called By:** ZenoPay Server  
**Auth Required:** No (validated by IP/signature)

### Expected Payload
```json
{
  "order_id": "SKY-1234567890",
  "payment_status": "COMPLETED",
  "transaction_id": "TXN123456",
  "amount": 5000,
  "reference": "REF789"
}
```

### Response
```json
{
  "status": "ok"
}
```

---

# 🔧 MOBILE APP IMPLEMENTATION TIPS

## 1. Token Storage
```dart
// Store token securely (Flutter example)
await secureStorage.write(key: 'auth_token', value: token);
```

## 2. API Headers Setup
```dart
// Dio setup example
dio.options.headers = {
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  'Authorization': 'Bearer $token',
};
```

## 3. Task Flow (Critical!)
```
1. GET /tasks                    → List tasks
2. POST /tasks/{id}/start        → Get lock_token, task_url
3. Open task_url in WebView      → User views ad
4. Poll POST /tasks/{id}/status  → Check timer (every 2-3 sec)
5. Wait until can_complete=true
6. POST /tasks/{id}/complete     → Get reward!
```

## 4. Error Handling
```dart
switch (response.statusCode) {
  case 401:
    // Token expired → Navigate to login
    break;
  case 403:
    // Show upgrade modal OR blocked message
    break;
  case 422:
    // Show validation errors from response.data['errors']
    break;
  case 425:
    // Timer not complete - wait and retry
    break;
}
```

## 5. Polling Payment Status
```dart
// Poll every 5 seconds after initiating payment
Timer.periodic(Duration(seconds: 5), (timer) async {
  final status = await checkPaymentStatus(orderId);
  if (status == 'completed' || status == 'failed') {
    timer.cancel();
    // Navigate accordingly
  }
});
```

---

# 📞 SUPPORT

- **Email:** support@skypesa.site
- **WhatsApp:** +255 700 000 000

---

*Document Version: 1.0*  
*Last Updated: 2024-12-26*

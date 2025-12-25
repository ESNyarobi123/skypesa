# 📱 SKYpesa Mobile API Documentation

**Base URL:** `https://your-domain.com/api/v1`  
**Version:** 1.0.0  
**Authentication:** Bearer Token (Laravel Sanctum)

---

## 🔐 Authentication Headers

All protected endpoints require this header:
```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
Accept: application/json
```

---

## 📌 PUBLIC ENDPOINTS (No Auth Required)

### 1. Health Check
```
GET /api/v1/health
```

**Response:**
```json
{
    "status": "ok",
    "message": "SKYpesa API is running",
    "version": "1.0.0",
    "timestamp": "2025-12-25T12:00:00.000000Z"
}
```

---

### 2. App Info
```
GET /api/v1/info
```

**Response:**
```json
{
    "app_name": "SKYpesa",
    "version": "1.0.0",
    "min_app_version": "1.0.0",
    "maintenance_mode": false,
    "support_email": "support@skypesa.com",
    "support_phone": "+255700000000"
}
```

---

### 3. Get Subscription Plans
```
GET /api/v1/plans
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Free",
            "slug": "free",
            "price": 0,
            "duration_days": 0,
            "tasks_per_day": 20,
            "reward_per_task": 50,
            "features": ["Basic tasks", "Limited earnings"],
            "is_active": true
        },
        {
            "id": 2,
            "name": "Starter",
            "slug": "starter",
            "price": 5000,
            "duration_days": 30,
            "tasks_per_day": 40,
            "reward_per_task": 100,
            "features": ["More tasks", "Higher rewards"],
            "is_active": true
        }
    ]
}
```

---

## 🔑 AUTHENTICATION ENDPOINTS

### 4. Register
```
POST /api/v1/auth/register
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "0712345678",
    "password": "password123",
    "password_confirmation": "password123",
    "referral_code": "ABC123"  // Optional
}
```

**Response:**
```json
{
    "success": true,
    "message": "Registration successful!",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "0712345678"
        },
        "token": "1|abcdefghijklmnop..."
    }
}
```

---

### 5. Login
```
POST /api/v1/auth/login
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful!",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "token": "2|xyz1234567890..."
    }
}
```

---

### 6. Forgot Password
```
POST /api/v1/auth/forgot-password
```

**Request Body:**
```json
{
    "email": "john@example.com"
}
```

---

### 7. Reset Password
```
POST /api/v1/auth/reset-password
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "otp": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

---

### 8. Logout
```
POST /api/v1/auth/logout
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

## 👤 USER PROFILE ENDPOINTS

### 9. Get Profile
```
GET /api/v1/user/profile
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "0712345678",
        "avatar": "https://...",
        "referral_code": "JOHN123",
        "balance": 15000,
        "plan": {
            "name": "Starter",
            "tasks_per_day": 40,
            "reward_per_task": 100,
            "expires_at": "2025-01-25"
        },
        "stats": {
            "tasks_completed_today": 15,
            "tasks_remaining": 25,
            "total_earnings": 150000
        }
    }
}
```

---

### 10. Update Profile
```
PUT /api/v1/user/profile
Authorization: Bearer TOKEN
```

**Request Body:**
```json
{
    "name": "John Updated",
    "phone": "0712345679"
}
```

---

### 11. Change Password
```
PUT /api/v1/user/password
Authorization: Bearer TOKEN
```

**Request Body:**
```json
{
    "current_password": "oldpassword",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

---

### 12. Get Dashboard Stats
```
GET /api/v1/user/dashboard
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": {
        "balance": 15000,
        "today_earnings": 5000,
        "tasks_completed_today": 15,
        "tasks_remaining": 25,
        "pending_withdrawals": 0,
        "referral_bonus": 500,
        "daily_goal": {
            "target": 10,
            "completed": 8,
            "bonus_amount": 200
        }
    }
}
```

---

### 13. Update FCM Token (Push Notifications)
```
POST /api/v1/user/fcm-token
Authorization: Bearer TOKEN
```

**Request Body:**
```json
{
    "fcm_token": "fMz9gK3..."
}
```

---

## 📋 TASKS ENDPOINTS

### 14. Get Available Tasks
```
GET /api/v1/tasks
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": {
        "tasks": [
            {
                "id": 1,
                "title": "Watch Video Ad",
                "description": "Watch a 30-second video",
                "type": "video_ad",
                "provider": "adsterra",
                "duration_seconds": 30,
                "reward": 100,
                "daily_limit": 5,
                "completions_today": 2,
                "remaining": 3,
                "can_complete": true,
                "is_featured": true,
                "thumbnail": "https://..."
            }
        ],
        "plan_info": {
            "name": "Starter",
            "daily_limit": 40,
            "is_unlimited": false,
            "tasks_shown": 10,
            "total_slots": 40
        }
    }
}
```

---

### 15. Get Single Task
```
GET /api/v1/tasks/{task_id}
Authorization: Bearer TOKEN
```

---

### 16. Start Task ⭐
```
POST /api/v1/tasks/{task_id}/start
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "message": "Kazi imeanza!",
    "data": {
        "lock_token": "abc123def456...",
        "duration": 30,
        "started_at": "2025-12-25T12:00:00.000000Z",
        "task_url": "https://adsterra.com/..."
    }
}
```

---

### 17. Check Task Status
```
POST /api/v1/tasks/{task_id}/status
Authorization: Bearer TOKEN
```

**Request Body:**
```json
{
    "lock_token": "abc123def456..."
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "elapsed": 25,
        "remaining": 5,
        "required": 30,
        "can_complete": false,
        "started_at": "2025-12-25T12:00:00.000000Z"
    }
}
```

---

### 18. Complete Task ⭐
```
POST /api/v1/tasks/{task_id}/complete
Authorization: Bearer TOKEN
```

**Request Body:**
```json
{
    "lock_token": "abc123def456..."
}
```

**Response:**
```json
{
    "success": true,
    "message": "Hongera! Umepata TZS 100",
    "data": {
        "reward": 100,
        "new_balance": 15100,
        "duration_spent": 30
    }
}
```

---

### 19. Cancel Task
```
POST /api/v1/tasks/cancel
Authorization: Bearer TOKEN
```

**Request Body:**
```json
{
    "lock_token": "abc123def456..."
}
```

---

### 20. Get Active Task
```
GET /api/v1/tasks/activity/current
Authorization: Bearer TOKEN
```

---

### 21. Get Task History
```
GET /api/v1/tasks/history/completed
Authorization: Bearer TOKEN
```

---

## 💰 WALLET ENDPOINTS

### 22. Get Wallet Info
```
GET /api/v1/wallet
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": {
        "balance": 15000,
        "pending_withdrawal": 0,
        "total_earned": 150000,
        "total_withdrawn": 100000
    }
}
```

---

### 23. Get Transactions
```
GET /api/v1/wallet/transactions?page=1&per_page=20
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "type": "task_reward",
            "amount": 100,
            "balance_after": 15100,
            "description": "Malipo ya task: Watch Video",
            "created_at": "2025-12-25T12:00:00.000000Z"
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

### 24. Get Earnings Summary
```
GET /api/v1/wallet/earnings
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": {
        "today": 5000,
        "this_week": 35000,
        "this_month": 120000,
        "all_time": 500000
    }
}
```

---

## 💸 WITHDRAWAL ENDPOINTS

### 25. Get Withdrawal Info
```
GET /api/v1/withdrawals/info
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": {
        "min_amount": 5000,
        "max_amount": 500000,
        "processing_fee": 0,
        "available_balance": 15000,
        "payment_methods": ["mpesa", "tigopesa", "airtel"]
    }
}
```

---

### 26. List Withdrawals
```
GET /api/v1/withdrawals
Authorization: Bearer TOKEN
```

---

### 27. Create Withdrawal ⭐
```
POST /api/v1/withdrawals
Authorization: Bearer TOKEN
```

**Request Body:**
```json
{
    "amount": 10000,
    "payment_method": "mpesa",
    "phone_number": "0712345678"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Ombi la kutoa pesa limepokelewa!",
    "data": {
        "id": 1,
        "amount": 10000,
        "fee": 0,
        "net_amount": 10000,
        "status": "pending",
        "created_at": "2025-12-25T12:00:00.000000Z"
    }
}
```

---

### 28. Cancel Withdrawal
```
DELETE /api/v1/withdrawals/{withdrawal_id}
Authorization: Bearer TOKEN
```

---

## 📦 SUBSCRIPTION ENDPOINTS

### 29. Get Current Subscription
```
GET /api/v1/subscriptions/current
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": {
        "plan": {
            "id": 2,
            "name": "Starter",
            "tasks_per_day": 40,
            "reward_per_task": 100
        },
        "expires_at": "2025-01-25",
        "days_remaining": 30,
        "is_active": true
    }
}
```

---

### 30. Initiate Payment
```
POST /api/v1/subscriptions/pay/{plan_id}
Authorization: Bearer TOKEN
```

**Request Body:**
```json
{
    "phone_number": "0712345678"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Payment initiated! Check your phone.",
    "data": {
        "order_id": "SKY-12345",
        "amount": 5000,
        "status": "pending"
    }
}
```

---

### 31. Check Payment Status
```
GET /api/v1/subscriptions/payment-status/{order_id}
Authorization: Bearer TOKEN
```

---

## 👥 REFERRAL ENDPOINTS

### 32. Get Referral Info
```
GET /api/v1/referrals
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": {
        "referral_code": "JOHN123",
        "referral_link": "https://skypesa.com/register?ref=JOHN123",
        "total_referrals": 15,
        "total_earnings": 7500,
        "pending_bonus": 500
    }
}
```

---

### 33. Get Referred Users
```
GET /api/v1/referrals/users
Authorization: Bearer TOKEN
```

---

### 34. Get Referral Stats
```
GET /api/v1/referrals/stats
Authorization: Bearer TOKEN
```

---

## 🔔 NOTIFICATION ENDPOINTS

### 35. Get Notifications
```
GET /api/v1/notifications
Authorization: Bearer TOKEN
```

---

### 36. Get Unread Count
```
GET /api/v1/notifications/unread-count
Authorization: Bearer TOKEN
```

**Response:**
```json
{
    "success": true,
    "data": {
        "unread_count": 5
    }
}
```

---

### 37. Mark as Read
```
PUT /api/v1/notifications/{notification_id}/read
Authorization: Bearer TOKEN
```

---

### 38. Mark All as Read
```
PUT /api/v1/notifications/read-all
Authorization: Bearer TOKEN
```

---

## 🔄 WEBHOOK ENDPOINTS (For External Services)

### ZenoPay Payment Callback
```
POST /api/webhooks/zenopay
```
*Used by ZenoPay to notify payment status*

---

### Adsterra Postback
```
GET/POST /api/webhooks/adsterra
```
*Used by Adsterra for conversion tracking*

---

### Monetag Postback
```
GET/POST /api/webhooks/monetag
```
*Used by Monetag for conversion tracking*

---

## ❌ ERROR RESPONSES

### Standard Error Format
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

### HTTP Status Codes
| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized (Invalid/Missing Token) |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 423 | Task Locked |
| 425 | Too Early (Timer not complete) |
| 500 | Server Error |

---

## 📱 MOBILE APP FLOW

### Task Completion Flow:
1. `GET /tasks` → Get available tasks
2. `POST /tasks/{id}/start` → Lock task and get URL
3. Open WebView with `task_url` from response
4. Wait for `duration` seconds
5. `POST /tasks/{id}/complete` with `lock_token`
6. Show success and new balance

### Withdrawal Flow:
1. `GET /withdrawals/info` → Get limits and methods
2. `POST /withdrawals` → Create request
3. Wait for admin approval
4. User receives money via mobile money

---

## 🔒 Security Notes

1. **Token Storage:** Store bearer token securely (Keychain/Keystore)
2. **HTTPS Only:** All requests must use HTTPS
3. **Token Refresh:** Tokens expire after 30 days
4. **Rate Limiting:** Max 60 requests per minute

---

**Last Updated:** 2025-12-25
**API Version:** 1.0.0

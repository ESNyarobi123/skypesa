# 📚 SKYpesa - Project Documentation

## 📋 Table of Contents
1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Technology Stack](#technology-stack)
4. [Database Schema](#database-schema)
5. [Service Providers](#service-providers)
6. [User Flow](#user-flow)
7. [Admin Flow](#admin-flow)
8. [API Documentation](#api-documentation)
9. [Webhook Integrations](#webhook-integrations)
10. [Configuration](#configuration)

---

## 🎯 Overview

**SKYpesa** ni platform ya kipato mtandaoni (online earning platform) inayowawezesha watumiaji kupata pesa kwa kufanya kazi ndogo ndogo (micro-tasks) kama vile kutazama matangazo, kujaza survey, na kazi nyingine za dijitali. Mfumo huu umetengenezwa kwa kutumia Laravel Framework.

### Key Features
- ✅ **Task Completion System** - Watumiaji wanafanya tasks na kupata malipo
- ✅ **Subscription Plans** - VIP tiers na faida tofauti (Free, Bronze, Silver, Gold, Diamond)
- ✅ **Wallet System** - Mfumo wa pochi/wallet kwa kuhifadhi na kusimamia pesa
- ✅ **Withdrawal System** - Watumiaji wanaweza kutoa pesa kupitia Mobile Money
- ✅ **Referral Program** - Watumiaji wanapata bonus kwa kuletea wenzao
- ✅ **Multi-Provider Integration** - Adsterra, Monetag, CPX Research
- ✅ **Admin Dashboard** - Panel ya kuendesha na kudhibiti mfumo
- ✅ **Mobile API** - RESTful API kwa mobile apps

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        SKYpesa Platform                          │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │
│  │   Web App   │  │  Mobile API │  │    Admin Dashboard      │  │
│  │  (Blade)    │  │  (Sanctum)  │  │      (Web)              │  │
│  └──────┬──────┘  └──────┬──────┘  └───────────┬─────────────┘  │
│         │                │                      │                │
│         └────────────────┼──────────────────────┘                │
│                          │                                       │
│  ┌───────────────────────▼─────────────────────────────────────┐│
│  │                    Laravel Controllers                       ││
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────────┐││
│  │  │ TaskController│ │WalletController│ │SubscriptionController│││
│  │  └─────────────┘ └─────────────┘ └─────────────────────────┘││
│  └──────────────────────────────────────────────────────────────┘│
│                          │                                       │
│  ┌───────────────────────▼─────────────────────────────────────┐│
│  │                     Services                                 ││
│  │  ┌──────────────┐ ┌──────────────┐ ┌────────────────────┐   ││
│  │  │AdsterraService│ │MonetagService│ │CpxResearchService │   ││
│  │  └──────────────┘ └──────────────┘ └────────────────────┘   ││
│  │  ┌──────────────┐ ┌──────────────┐ ┌────────────────────┐   ││
│  │  │ZenoPayService│ │TaskLockService│ │                    │   ││
│  │  └──────────────┘ └──────────────┘ └────────────────────┘   ││
│  └──────────────────────────────────────────────────────────────┘│
│                          │                                       │
│  ┌───────────────────────▼─────────────────────────────────────┐│
│  │                      Models                                  ││
│  │  User │ Task │ Wallet │ Transaction │ Subscription │ etc.   ││
│  └──────────────────────────────────────────────────────────────┘│
│                          │                                       │
│  ┌───────────────────────▼─────────────────────────────────────┐│
│  │                   MySQL Database                             ││
│  └──────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘

                    External Services
┌─────────────────────────────────────────────────────────────────┐
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────────┐ │
│  │ Adsterra │  │ Monetag  │  │   CPX    │  │    ZenoPay       │ │
│  │(Ads/Tasks)│ │(Ads/Push)│  │ Research │  │ (Mobile Money)   │ │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Technology Stack

| Component | Technology |
|-----------|------------|
| **Backend Framework** | Laravel 11.x |
| **Database** | MySQL 8.0+ |
| **Authentication** | Laravel Sanctum (API), Session (Web) |
| **Frontend** | Blade Templates + Vanilla CSS + JavaScript |
| **Build Tool** | Vite |
| **Queue System** | Database Queue |
| **Cache** | Database Cache |
| **Session** | Database Session |

---

## 📊 Database Schema

### Core Models

#### 1. **User** (`users` table)
Mfano wa mtumiaji wa mfumo.

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Jina la mtumiaji |
| `email` | string | Email (unique) |
| `phone` | string | Namba ya simu |
| `password` | string | Password (hashed) |
| `role` | enum | `user` \| `admin` |
| `is_active` | boolean | Kama account iko active |
| `is_verified` | boolean | Kama amethibitishwa |
| `referral_code` | string | Code ya kumwalika mtu (unique) |
| `referred_by` | bigint | ID ya mtu aliyemwalika |
| `last_login_at` | datetime | Muda wa login ya mwisho |
| `last_login_ip` | string | IP ya login ya mwisho |

**Relationships:**
- `wallet()` → HasOne Wallet
- `subscriptions()` → HasMany UserSubscription
- `activeSubscription()` → HasOne UserSubscription (active)
- `taskCompletions()` → HasMany TaskCompletion
- `transactions()` → HasMany Transaction
- `withdrawals()` → HasMany Withdrawal
- `referrer()` → BelongsTo User
- `referrals()` → HasMany User

---

#### 2. **Wallet** (`wallets` table)
Pochi ya mtumiaji kwa kuhifadhi fedha.

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | Foreign key → users |
| `balance` | decimal(12,2) | Salio la sasa |
| `total_earned` | decimal(12,2) | Jumla ya fedha zote alizoingiza |
| `total_withdrawn` | decimal(12,2) | Jumla ya fedha alizotoa |
| `pending_withdrawal` | decimal(12,2) | Fedha zinazongojea kutolewa |
| `is_locked` | boolean | Kama wallet imefungwa |
| `lock_reason` | string | Sababu ya kufunga |

**Key Methods:**
- `credit($amount, $category)` → Kuongeza pesa
- `debit($amount, $category)` → Kutoa pesa
- `getAvailableBalance()` → Salio linaloweza kutolewa
- `canWithdraw($amount)` → Ukaguaji wa kutosha

---

#### 3. **Task** (`tasks` table)
Kazi zinazoweza kufanywa na watumiaji.

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `title` | string | Jina la task |
| `description` | text | Maelezo ya task |
| `type` | string | Aina ya task (view_ad, survey, etc.) |
| `url` | string | URL ya task |
| `provider` | string | Provider (adsterra, monetag, cpx) |
| `duration_seconds` | integer | Muda wa kufanya task (sekunde) |
| `reward_override` | decimal | Override reward (optional) |
| `daily_limit` | integer | Kikomo cha kila siku |
| `total_limit` | integer | Kikomo cha jumla |
| `completions_count` | integer | Idadi ya completions |
| `is_active` | boolean | Kama task iko active |
| `is_featured` | boolean | Kama ni featured |
| `starts_at` | datetime | Wakati wa kuanza |
| `ends_at` | datetime | Wakati wa kuisha |

---

#### 4. **TaskCompletion** (`task_completions` table)
Rekodi za tasks zilizokamilishwa.

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | FK → users |
| `task_id` | bigint | FK → tasks |
| `status` | enum | `started` \| `completed` \| `failed` \| `cancelled` |
| `reward_earned` | decimal | Malipo yaliyopatikana |
| `started_at` | datetime | Wakati wa kuanza |
| `completed_at` | datetime | Wakati wa kumaliza |
| `ip_address` | string | IP ya mtumiaji |
| `user_agent` | string | Browser info |
| `lock_token` | string | Token ya kufunga task |
| `lock_expires_at` | datetime | Muda wa kuisha kwa lock |

---

#### 5. **SubscriptionPlan** (`subscription_plans` table)
Mipango ya usajili (VIP tiers).

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Jina la plan (free, bronze, silver, gold, diamond) |
| `display_name` | string | Jina la kuonyeshwa |
| `description` | text | Maelezo |
| `price` | decimal | Bei (TZS) |
| `duration_days` | integer | Muda wa plan (siku) |
| `daily_task_limit` | integer | Kikomo cha tasks kwa siku (null = unlimited) |
| `reward_per_task` | decimal | Malipo kwa kila task (TZS) |
| `min_withdrawal` | decimal | Kiwango cha chini cha kutoa |
| `withdrawal_fee_percent` | decimal | Asilimia ya ada ya kutoa |
| `processing_days` | integer | Siku za kuchakata withdrawal |
| `badge_color` | string | Rangi ya badge |
| `is_active` | boolean | Kama plan iko active |
| `is_featured` | boolean | Kama ni featured |

**Default Plans:**
| Plan | Price | Daily Limit | Reward/Task | Min Withdrawal | Fee |
|------|-------|-------------|-------------|----------------|-----|
| Free | 0 | 5 | TZS 3 | TZS 10,000 | 20% |
| Bronze | 5,000 | 15 | TZS 50 | TZS 5,000 | 15% |
| Silver | 10,000 | 30 | TZS 100 | TZS 3,000 | 10% |
| Gold | 20,000 | Unlimited | TZS 200 | TZS 2,000 | 5% |
| Diamond | 50,000 | Unlimited | TZS 500 | TZS 1,000 | 0% |

---

#### 6. **Transaction** (`transactions` table)
Rekodi za miamala yote ya fedha.

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | FK → users |
| `wallet_id` | bigint | FK → wallets |
| `reference` | string | Reference number (TXN...) |
| `type` | enum | `credit` \| `debit` |
| `category` | enum | `task_reward` \| `withdrawal` \| `subscription` \| `referral_bonus` \| etc. |
| `amount` | decimal | Kiasi |
| `balance_before` | decimal | Salio kabla |
| `balance_after` | decimal | Salio baada |
| `description` | string | Maelezo |
| `transactionable_type` | string | Polymorphic type |
| `transactionable_id` | bigint | Polymorphic ID |
| `metadata` | json | Data ya ziada |

---

#### 7. **Withdrawal** (`withdrawals` table)
Maombi ya kutoa fedha.

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | FK → users |
| `reference` | string | Reference (WD...) |
| `amount` | decimal | Kiasi cha kutoa |
| `fee` | decimal | Ada |
| `net_amount` | decimal | Kiasi halisi cha kupokea |
| `payment_method` | string | Njia ya malipo (mpesa, tigopesa, etc.) |
| `payment_number` | string | Namba ya simu |
| `payment_name` | string | Jina la mpokeaji |
| `status` | enum | `pending` \| `processing` \| `approved` \| `paid` \| `rejected` |
| `rejection_reason` | string | Sababu ya kukataa |
| `zenopay_reference` | string | Reference ya ZenoPay |
| `approved_at` | datetime | Wakati wa kukubaliwa |
| `paid_at` | datetime | Wakati wa kulipwa |
| `approved_by` | bigint | Admin aliyekubali |

---

## 🔌 Service Providers

### 1. **AdsterraService** (`app/Services/AdsterraService.php`)

Huduma ya kuunganisha na Adsterra API kwa kupata matangazo na tasks.

```php
class AdsterraService
{
    // Get all domains registered in Adsterra
    public function getDomains(): array
    
    // Get all ad placements
    public function getPlacements(): array
    
    // Get placements with direct URLs (usable for tasks)
    public function getTaskablePlacements(): array
    
    // Test API connection
    public function testConnection(): array
    
    // Convert placement to task data
    public function placementToTaskData(array $placement): array
}
```

**Configuration:**
```env
ADSTERRA_API_KEY=your_api_key
ADSTERRA_BASE_URL=https://api3.adsterratools.com
```

**API Endpoints Used:**
- `GET /publisher/domains.json` - Kupata domains
- `GET /publisher/placements.json` - Kupata placements
- `GET /publisher/domain/{id}/placements.json` - Placements za domain moja

---

### 2. **MonetagService** (`app/Services/MonetagService.php`)

Huduma ya kuunganisha na Monetag kwa Push Notifications na In-Page Push ads.

```php
class MonetagService
{
    // Get domain for scripts
    public function getDomain(): string
    
    // Get zone ID
    public function getZoneId(): int
    
    // Generate service worker script
    public function getServiceWorkerScript(): string
    
    // Generate smartlink with tracking
    public function generateSmartlink(string $baseUrl, ?int $userId, ?string $taskId): string
    
    // Get In-Page Push script
    public function getIPNScript(): string
    
    // Check if push is enabled
    public function isPushEnabled(): bool
    
    // Create task data array
    public function createTaskData(string $title, string $url, int $durationSeconds): array
}
```

**Configuration:**
```env
MONETAG_DOMAIN=3nbf4.com
MONETAG_ZONE_ID=10345364
MONETAG_ENABLE_PUSH=true
MONETAG_ENABLE_IPN=false
```

---

### 3. **CpxResearchService** (`app/Services/CpxResearchService.php`)

Huduma ya kuunganisha na CPX Research kwa surveys.

```php
class CpxResearchService
{
    // Check if CPX is configured
    public function isConfigured(): bool
    
    // Get available surveys for user
    public function getSurveys(User $user, ?string $ip, ?string $userAgent): array
    
    // Handle CPX postback/callback
    public function handlePostback(array $data): array
    
    // Credit user for completed survey
    public function creditUser(User $user, SurveyCompletion $completion): void
    
    // Handle survey reversal
    public function handleReversal(string $transactionId, User $user, float $amount): void
    
    // Get user's survey statistics
    public function getUserStats(User $user): array
    
    // Calculate VIP bonus
    public function calculateVipBonus(User $user, float $baseReward, string $surveyType): float
    
    // Get admin statistics
    public function getAdminStats(): array
    
    // Get profit analytics
    public function getProfitAnalytics(int $days = 30): array
}
```

**VIP Bonuses:**
| Plan | Bonus Multiplier |
|------|------------------|
| Bronze | +5% |
| Silver | +10% |
| Gold | +20% |
| Diamond | +30% |

---

### 4. **ZenoPayService** (`app/Services/ZenoPayService.php`)

Huduma ya kuunganisha na ZenoPay API kwa malipo ya Mobile Money.

```php
class ZenoPayService
{
    // Initiate mobile money payment
    public function initiatePayment(
        string $buyerName,
        string $buyerEmail,
        string $buyerPhone,
        float $amount,
        ?string $orderId = null
    ): array
    
    // Check payment status
    public function checkStatus(string $orderId): array
    
    // Poll for payment completion
    public function pollForCompletion(string $orderId, int $maxAttempts, int $interval): array
    
    // Generate unique order ID
    public function generateOrderId(): string
    
    // Format phone number to 07XXXXXXXX
    public function formatPhoneNumber(string $phone): string
    
    // Test API connection
    public function testConnection(): array
}
```

**Configuration:**
```env
ZENOPAY_API_KEY=your_api_key
ZENOPAY_BASE_URL=https://zenoapi.com
```

**Supported Payment Methods:**
- M-Pesa
- Tigo Pesa
- Airtel Money
- Halo Pesa

---

### 5. **TaskLockService** (`app/Services/TaskLockService.php`)

Huduma ya kusimamia task locking ili kuzuia mtumiaji mmoja kufanya task moja mara nyingi kwa wakati mmoja.

```php
class TaskLockService
{
    // Lock a task for a user
    public function lockTask(User $user, Task $task): TaskCompletion
    
    // Unlock/release a task
    public function unlockTask(TaskCompletion $completion): void
    
    // Check if task is locked
    public function isTaskLocked(User $user, Task $task): bool
    
    // Get user's active task
    public function getActiveTask(User $user): ?TaskCompletion
    
    // Complete a locked task
    public function completeTask(TaskCompletion $completion): void
}
```

---

## 👤 User Flow

### 1. Registration & Onboarding

```
┌──────────────────┐
│   Landing Page   │
│   (welcome.blade)│
└────────┬─────────┘
         │
         ▼
┌──────────────────┐    ┌──────────────────┐
│  Register Form   │───→│  Login Form      │
│  (/register)     │    │  (/login)        │
└────────┬─────────┘    └────────┬─────────┘
         │                       │
         ▼                       ▼
┌──────────────────────────────────────────┐
│            User Dashboard                 │
│  - Wallet Balance                         │
│  - Tasks Available                        │
│  - Earnings Today                         │
│  - Quick Actions                          │
└────────────────────┬─────────────────────┘
                     │
    ┌────────────────┼────────────────┐
    ▼                ▼                ▼
┌────────┐    ┌──────────┐    ┌────────────┐
│ Tasks  │    │ Wallet   │    │Subscriptions│
└────────┘    └──────────┘    └────────────┘
```

### 2. Task Completion Flow

```
┌──────────────────┐
│   Tasks Index    │
│   (/tasks)       │
└────────┬─────────┘
         │ User selects task
         ▼
┌──────────────────┐
│   Lock Task      │ ◄─── TaskLockService
│   (POST /start)  │
└────────┬─────────┘
         │ Lock acquired
         ▼
┌──────────────────┐
│   Task Viewer    │
│   - Timer runs   │
│   - External URL │
└────────┬─────────┘
         │ Duration complete
         ▼
┌──────────────────┐
│  Complete Task   │
│ (POST /complete) │
└────────┬─────────┘
         │
         ▼
┌──────────────────────────────────────┐
│ - TaskCompletion status = completed  │
│ - Wallet credited                    │
│ - Transaction recorded               │
│ - User notified                      │
└──────────────────────────────────────┘
```

### 3. Withdrawal Flow

```
┌──────────────────┐
│  Withdrawal Form │
│ (/withdrawals/   │
│    create)       │
└────────┬─────────┘
         │
         ▼
┌──────────────────────────────────────┐
│ Validation:                           │
│ - Balance >= amount + fee             │
│ - Amount >= min_withdrawal            │
│ - Wallet not locked                   │
│ - No pending withdrawal               │
└────────┬─────────────────────────────┘
         │ Pass
         ▼
┌──────────────────────────────────────┐
│ Create Withdrawal:                    │
│ - Status: pending                     │
│ - Debit wallet                        │
│ - Increment pending_withdrawal        │
└────────┬─────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────┐
│ Admin Review:                         │
│ - Approve/Reject                      │
│ - Mark as Paid                        │
│ - ZenoPay integration                 │
└────────┬─────────────────────────────┘
         │
         ▼
┌──────────────────┐
│  User receives   │
│  Mobile Money    │
└──────────────────┘
```

---

## 👑 Admin Flow

### 1. Admin Dashboard

Ukurasa wa Admin unaonyesha:
- **Overview Stats** - Users, Revenue, Tasks, Withdrawals
- **Live Stats** - Real-time activity
- **Recent Activity** - Transactions na actions za hivi karibuni
- **System Health** - Status ya system

### 2. Admin Routes

```
/admin/dashboard          → Overview ya mfumo
/admin/analytics          → Charts na trends
/admin/live-stats         → Real-time updates
/admin/users              → Manage users (CRUD)
/admin/plans              → Manage subscription plans
/admin/tasks              → Manage tasks
/admin/withdrawals        → Process withdrawals
/admin/directlinks        → Manage ad direct links
/admin/adsterra           → Adsterra integration
/admin/referrals          → Referral program analytics
/admin/transactions       → All transactions
/admin/settings           → System settings
```

### 3. Withdrawal Processing

```
┌──────────────────────────────────────────────────────────┐
│                Admin Withdrawal Panel                     │
├──────────────────────────────────────────────────────────┤
│  Status: pending → processing → approved → paid           │
│                            ↘                              │
│                             rejected                      │
├──────────────────────────────────────────────────────────┤
│  Actions:                                                 │
│  - Approve: Move to 'approved', set approved_at/by       │
│  - Mark Paid: Move to 'paid', release pending_withdrawal │
│  - Reject: Refund balance, set rejection_reason          │
└──────────────────────────────────────────────────────────┘
```

---

## 🌐 API Documentation

### Base URL
```
Production: https://yourdomain.com/api/v1
Development: http://localhost:8000/api/v1
```

### Authentication
API inatumia **Laravel Sanctum** kwa authentication.

```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": { ... },
        "token": "1|abc123..."
    }
}
```

### Endpoints Summary

#### Authentication (`/auth`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/register` | Kujiandikisha |
| POST | `/auth/login` | Kuingia |
| POST | `/auth/logout` | Kutoka (requires auth) |
| POST | `/auth/forgot-password` | Omba reset password |
| POST | `/auth/reset-password` | Reset password |

#### User Profile (`/user`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/user/profile` | Get profile |
| PUT | `/user/profile` | Update profile |
| POST | `/user/avatar` | Update avatar |
| PUT | `/user/password` | Change password |
| GET | `/user/dashboard` | Dashboard stats |
| GET | `/user/activity` | Activity summary |

#### Tasks (`/tasks`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tasks` | List available tasks |
| GET | `/tasks/{id}` | Get single task |
| POST | `/tasks/{id}/start` | Start/lock task |
| POST | `/tasks/{id}/status` | Check task status |
| POST | `/tasks/{id}/complete` | Complete task |
| POST | `/tasks/cancel` | Cancel current task |
| GET | `/tasks/activity/current` | Get active task |
| GET | `/tasks/history/completed` | Task history |

#### Wallet (`/wallet`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/wallet` | Get wallet info |
| GET | `/wallet/transactions` | Transaction history |
| GET | `/wallet/transactions/{id}` | Single transaction |
| GET | `/wallet/earnings` | Earnings summary |

#### Withdrawals (`/withdrawals`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/withdrawals` | List withdrawals |
| GET | `/withdrawals/info` | Limits & fees info |
| POST | `/withdrawals` | Create withdrawal |
| GET | `/withdrawals/{id}` | Get single withdrawal |
| DELETE | `/withdrawals/{id}` | Cancel pending withdrawal |

#### Subscriptions (`/subscriptions`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/plans` | List all plans (public) |
| GET | `/subscriptions/current` | Current subscription |
| POST | `/subscriptions/subscribe/{plan}` | Subscribe to plan |
| POST | `/subscriptions/pay/{plan}` | Initiate payment |
| GET | `/subscriptions/payment-status/{orderId}` | Check payment |
| GET | `/subscriptions/history` | Subscription history |

#### Referrals (`/referrals`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/referrals` | Referral info & link |
| GET | `/referrals/users` | Referred users list |
| GET | `/referrals/earnings` | Referral earnings |
| GET | `/referrals/stats` | Referral statistics |

#### Notifications (`/notifications`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | All notifications |
| GET | `/notifications/unread-count` | Unread count |
| PUT | `/notifications/{id}/read` | Mark as read |
| PUT | `/notifications/read-all` | Mark all as read |
| DELETE | `/notifications/{id}` | Delete notification |

---

## 🔗 Webhook Integrations

### 1. ZenoPay Callback
**URL:** `POST /api/webhooks/zenopay`

Inaitwa na ZenoPay baada ya malipo kukamilika.

```json
{
    "order_id": "SKY-20251220-ABC123",
    "status": "COMPLETED",
    "reference": "ZENO-REF-123",
    "amount": 10000
}
```

### 2. Adsterra Postback
**URL:** `GET/POST /api/webhooks/adsterra`

Inaitwa na Adsterra baada ya mtumiaji kutazama tangazo.

```
/api/webhooks/adsterra?user_id={user_id}&task_id={task_id}&payout={payout}
```

### 3. Monetag Postback
**URL:** `GET/POST /api/webhooks/monetag`

Inaitwa na Monetag kwa conversions.

```
/api/webhooks/monetag?subid={user_id}&subid2={task_id}&payout={payout}
```

---

## ⚙️ Configuration

### Environment Variables

```env
# Application
APP_NAME=SKYpesa
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skypesa
DB_USERNAME=root
DB_PASSWORD=your_password

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Adsterra Integration
ADSTERRA_API_KEY=your_api_key
ADSTERRA_BASE_URL=https://api3.adsterratools.com

# Monetag Integration
MONETAG_DOMAIN=3nbf4.com
MONETAG_ZONE_ID=your_zone_id
MONETAG_ENABLE_PUSH=true
MONETAG_ENABLE_IPN=false

# ZenoPay (Mobile Money)
ZENOPAY_API_KEY=your_api_key
ZENOPAY_BASE_URL=https://zenoapi.com
```

### Config Files

- `config/adsterra.php` - Adsterra settings
- `config/monetag.php` - Monetag settings
- `config/zenopay.php` - ZenoPay settings

---

## 📁 Directory Structure

```
skypesa/
├── app/
│   ├── Console/Commands/           # Artisan commands
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/             # Admin controllers
│   │   │   ├── Api/               # API controllers
│   │   │   └── Auth/              # Authentication
│   │   └── Middleware/            # Custom middleware
│   ├── Models/                    # Eloquent models
│   ├── Providers/                 # Service providers
│   └── Services/                  # Business logic services
├── config/                        # Configuration files
├── database/
│   ├── migrations/                # Database migrations
│   └── seeders/                   # Database seeders
├── public/                        # Public assets
├── resources/
│   ├── css/                       # Stylesheets
│   ├── js/                        # JavaScript
│   └── views/                     # Blade templates
│       ├── admin/                 # Admin views
│       ├── auth/                  # Auth views
│       ├── layouts/               # Layouts
│       └── *.blade.php            # User views
├── routes/
│   ├── api.php                    # API routes
│   └── web.php                    # Web routes
└── storage/                       # Storage (logs, cache, etc.)
```

---

## 🚀 Deployment Checklist

1. **Environment Setup**
   - [ ] Copy `.env.example` to `.env`
   - [ ] Set `APP_ENV=production`
   - [ ] Set `APP_DEBUG=false`
   - [ ] Configure database credentials
   - [ ] Set all API keys

2. **Database**
   - [ ] Run `php artisan migrate`
   - [ ] Run `php artisan db:seed` (if needed)

3. **Optimization**
   - [ ] `php artisan config:cache`
   - [ ] `php artisan route:cache`
   - [ ] `php artisan view:cache`
   - [ ] `npm run build`

4. **External Services**
   - [ ] Configure Adsterra postback URL
   - [ ] Configure Monetag postback URL
   - [ ] Configure ZenoPay callback URL
   - [ ] Test all webhooks

5. **Security**
   - [ ] Set strong `APP_KEY`
   - [ ] Configure HTTPS
   - [ ] Set proper file permissions
   - [ ] Configure CORS (if needed)

---

## 📞 Support

- **Email:** support@skypesa.com
- **Phone:** +255 700 000 000

---

*Documentation last updated: December 21, 2025*

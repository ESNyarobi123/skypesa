# SKYpesa - Task-Based Digital Earning Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=flat-square&logo=php" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

SKYpesa ni jukwaa la kujipatia pesa mtandaoni kwa kukamilisha kazi rahisi kama kutazama matangazo na kushiriki links.

## 🚀 Features

### User Features
- ✅ Tazama Matangazo (View Ads) na upate malipo
- ✅ Shiriki Promotional Links
- ✅ Internal Wallet System
- ✅ Withdraw kupitia M-Pesa, Tigo Pesa, Airtel Money, Halo Pesa
- ✅ 4 Subscription Levels (Free, Phase 1, Phase 2, Premium)
- ✅ Referral System

### Admin Features
- ✅ Dashboard ya muhtasari
- ✅ Manage Users
- ✅ CRUD Tasks
- ✅ Approve/Reject Withdrawals
- ✅ View Statistics

## 💰 Subscription Plans

| Plan | Bei | Tasks/Day | TZS/Task | Withdraw Fee | Processing |
|------|-----|-----------|----------|--------------|------------|
| Free | TZS 0 | 5 | 50 | 20% | 7 days |
| Phase 1 | TZS 5,000/mwezi | 15 | 75 | 10% | 3 days |
| Phase 2 | TZS 15,000/mwezi | 30 | 100 | 5% | 24 hours |
| Premium | TZS 30,000/mwezi | ∞ | 150 | 2% | Instant |

## 🛠️ Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL 8.0+

### Setup

1. **Clone the repository**
```bash
git clone <repo-url> skypesa
cd skypesa
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Create database**
```sql
CREATE DATABASE skypesa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

5. **Update .env file**
```env
APP_NAME=SKYpesa
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skypesa
DB_USERNAME=root
DB_PASSWORD=
```

6. **Run migrations and seeders**
```bash
php artisan migrate:fresh --seed
```

7. **Build assets**
```bash
npm run build
```

8. **Start the server**
```bash
php artisan serve
```

## 👤 Default Accounts

### Admin
- Email: `admin@skypesa.co.tz`
- Password: `password123`

### Test User
- Email: `user@skypesa.co.tz`
- Password: `password123`

## 📁 Project Structure

```
skypesa/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin controllers
│   │   │   ├── Auth/           # Authentication
│   │   │   ├── DashboardController.php
│   │   │   ├── TaskController.php
│   │   │   ├── WalletController.php
│   │   │   ├── WithdrawalController.php
│   │   │   ├── SubscriptionController.php
│   │   │   └── ReferralController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── SubscriptionPlan.php
│       ├── UserSubscription.php
│       ├── Task.php
│       ├── TaskCompletion.php
│       ├── Wallet.php
│       ├── Transaction.php
│       └── Withdrawal.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   │   └── app.css            # Green & Black theme
│   └── views/
│       ├── layouts/
│       ├── auth/
│       ├── admin/
│       ├── tasks/
│       ├── wallet/
│       ├── withdrawals/
│       ├── subscriptions/
│       ├── referrals/
│       └── dashboard.blade.php
└── routes/
    └── web.php
```

## 🎨 Design

- **Colors**: Green (#10b981) & Black theme
- **Style**: Modern, glassmorphism, dark mode
- **Icons**: Lucide Icons
- **Font**: Inter (Google Fonts)

## 📡 Adsterra Integration

SKYpesa supports Adsterra Publisher API for importing placements as tasks.

### Configuration

Add your Adsterra API key to `.env`:

```env
ADSTERRA_API_KEY=your_api_key_here
```

### Importing Placements

**Via Admin Panel:**
1. Login as admin
2. Go to Admin → Adsterra API
3. Click "Import Zote" to import all placements with direct URLs

**Via Command Line:**

```bash
# Import new placements
php artisan adsterra:sync --import

# Update existing task URLs
php artisan adsterra:sync --update

# Both import and update
php artisan adsterra:sync --all
```

### Automatic Sync (Cron)

Add to your scheduler for daily sync:

```php
// app/Console/Kernel.php
$schedule->command('adsterra:sync --all')->daily();
```

## 💰 Monetag Integration

SKYpesa supports Monetag for push notifications and smartlinks.

### Configuration

The service worker file is already at `public/sw.js`. Add to your `.env`:

```env
MONETAG_DOMAIN=3nbf4.com
MONETAG_ZONE_ID=10345364
MONETAG_ENABLE_PUSH=true
MONETAG_ENABLE_IPN=false
```

### Features

- **Push Notifications** - Automatic service worker registration
- **Smartlinks** - Generate task URLs with tracking
- **In-Page Push** - Optional IPN ads (disabled by default)

### Files

| File | Purpose |
|------|---------|
| `public/sw.js` | Service worker for push notifications |
| `config/monetag.php` | Configuration settings |
| `app/Services/MonetagService.php` | Helper service class |
| `resources/views/partials/monetag.blade.php` | Script injection partial |

## 📝 TODO / Future Features

- [ ] ZenoPay Integration for deposits
- [ ] ZenoPay Integration for automatic withdrawals
- [x] Monetag Integration ✅
- [x] Adsterra API Integration ✅
- [x] Push Notifications (via Monetag) ✅
- [ ] Mobile App (Flutter)
- [ ] Multi-language (Swahili/English toggle)
- [ ] Analytics Dashboard
- [ ] Fraud Detection System

## 📄 License

MIT License

## 👨‍💻 Developer

Built with ❤️ for Tanzanian entrepreneurs.

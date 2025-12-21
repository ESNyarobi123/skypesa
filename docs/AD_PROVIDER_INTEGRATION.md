# SKYpesa Direct Link Integration Guide

## 🎯 UKWELI MUHIMU

**Kumbuka haya kabla ya kuendelea:**

| Provider | Postback? | Matumizi |
|----------|-----------|----------|
| Monetag Direct Links | ❌ HAKUNA | Traffic monetization, timer-based reward |
| Adsterra Smartlink (Publisher) | ❌ HAKUNA | Traffic monetization, timer-based reward |
| Monetag SDK Rewarded | ✅ NDI | Telegram Mini Apps, Mobile Apps (SDK only) |

**Kwa web-based SKYpesa:**
- Payment ni **timer-based** (user views ad for X seconds)
- Hakuna **postback ya automatic** kutoka provider
- Tunafuatilia **clicks ourselves** kupitia `/go/{provider}/{slug}`

---

## 📁 File Structure

```
app/
├── Http/Controllers/
│   └── GoController.php         # Click tracking + redirect
├── Config/
│   └── directlinks.php          # Direct Link URLs + Anti-fraud settings
└── Services/
    └── TaskLockService.php      # Existing lock service (reused)

routes/
└── web.php                      # /go/* routes
```

---

## 🔗 Task Flow (Jinsi Inavyofanya Kazi)

### Mfuatano wa Mtumiaji (User Journey)

```
1. Mtumiaji anaona "Task Card" kwenye /tasks
   ↓
2. Anabofya "Anza Kazi"
   ↓
3. Anapelekwa /go/monetag/immortal (mfano)
   ↓
4. GoController:
   - Checks daily limit ✓
   - Checks IP limit ✓
   - Checks cooldown ✓
   - Creates TaskCompletion (status: in_progress)
   - Logs click
   ↓
5. Redirect → Monetag Direct Link URL
   ↓
6. Mtumiaji anatazama tangazo (30+ sekunde)
   ↓
7. Anarudi kwenye app, anabofya "Kamilisha"
   ↓
8. Timer verification:
   - duration_spent >= required_duration?
   - Yes → Credit wallet, mark completed
   - No → Error: "Subiri sekunde X"
```

---

## ⚙️ Configuration

### Step 1: Get Your Direct Link URLs

**Monetag:**
1. Login to [Monetag Dashboard](https://monetag.com)
2. Go to **Direct Links** section
3. Copy each link URL (Immortal, Glad, etc.)

**Adsterra:**
1. Login to [Adsterra](https://publishers.adsterra.com)
2. Go to **Smartlink** section
3. Copy your Smartlink URL

### Step 2: Add to `.env`

```env
# Adsterra Smartlink
ADSTERRA_SMARTLINK=https://your-adsterra-smartlink-url.com

# Monetag Direct Links
MONETAG_DIRECTLINK_IMMORTAL=https://your-immortal-link.com
MONETAG_DIRECTLINK_GLAD=https://your-glad-link.com

# Anti-Fraud Settings (KEEP SMALL!)
TASK_DEFAULT_REWARD=5
TASK_DAILY_LIMIT=10
TASK_IP_DAILY_LIMIT=15
TASK_COOLDOWN_SECONDS=120
```

---

## 🛡️ Anti-Fraud Measures

| Check | Limit | What Happens |
|-------|-------|--------------|
| Daily User Limit | 10 tasks/day | "Umekamilisha kazi 10 leo. Rudi kesho!" |
| IP Limit | 15/day | "Kikomo cha kazi kimefikiwa." |
| Cooldown | 120 sec | "Subiri sekunde X kabla ya kazi nyingine." |
| In-Progress Lock | 1 active | "Una kazi inayoendelea. Kamilisha kwanza!" |
| Task-Specific Limit | 3/day/task | "Umekamilisha kazi hii mara nyingi leo." |
| Min Duration | 30 sec | Timer must complete before payout |

---

## 📊 Tracking Parameters

### Adsterra (psid)

Adsterra hutumia `psid` kwa SubID tracking kwenye reports zao:

```
https://smartlink.com/...?psid=U25_T108
                              ↑    ↑
                           User  Task
```

Hii inaonekana kwenye Adsterra Publisher reports kwa analytics.

### Monetag

Monetag Direct Links hazina tracking parameter maalum. Hata hivyo, kama unataka kufuatilia, unaweza kuongeza query params lakini Monetag haitazipost tena kwako.

---

## 📍 Routes

| Route | Purpose |
|-------|---------|
| `GET /go/monetag/immortal` | Redirect to Immortal Direct Link |
| `GET /go/monetag/glad` | Redirect to Glad Direct Link |
| `GET /go/adsterra` | Redirect to Adsterra Smartlink |
| `GET /go/{provider}/{slug}` | Generic redirect |

---

## 💰 Reward Settings

**⚠️ MUHIMU: Weka rewards NDOGO!**

Kwa Direct Links, unapata centimali kwa click. Ukilipa mtumiaji TZS 50+ kwa click, utafilisika haraka!

**Mapendekezo:**
- Free plan: TZS 3-5 per task
- Premium: TZS 5-10 per task
- Daily limit: 10-20 tasks max

---

## 🔮 Future: SDK Rewarded Ads

Kama unajenga **Telegram Mini App** au **Mobile App**, unaweza kutumia Monetag SDK Rewarded:

1. Implement Monetag SDK
2. Create "Rewarded Zone" in Monetag
3. Set Postback URL: `https://yourdomain.com/api/webhooks/monetag`
4. PostbackHandlerService itaprocess

---

## ✅ Quick Checklist

- [ ] Add `MONETAG_DIRECTLINK_IMMORTAL` to `.env`
- [ ] Add `MONETAG_DIRECTLINK_GLAD` to `.env`
- [ ] Add `ADSTERRA_SMARTLINK` to `.env`
- [ ] Set small `TASK_DEFAULT_REWARD` (5-10 TZS)
- [ ] Create tasks in admin panel with correct provider
- [ ] Test flow: `/go/monetag/immortal`
- [ ] Verify timer completion works
- [ ] Enable withdrawal delay (24-72 hours)

---

## 📝 Example: Creating Direct Link Tasks

Kwenye Admin Panel au via Tinker:

```php
// Monetag Immortal
Task::create([
    'title' => 'Tazama Tangazo la Bidhaa',
    'description' => 'Bofya na utazame kwa sekunde 30 ili upate malipo.',
    'type' => 'view_ad',
    'category' => 'traffic_task',
    'require_postback' => false, // IMPORTANT!
    'url' => config('directlinks.monetag.immortal'),
    'provider' => 'monetag',
    'duration_seconds' => 30,
    'cooldown_seconds' => 120,
    'daily_limit' => 3,
    'ip_daily_limit' => 5,
    'reward_override' => 5, // TZS 5 only!
    'is_active' => true,
    'requirements' => ['source' => 'directlink', 'slug' => 'immortal'],
]);
```

---

## ❓ FAQ

**Q: Kwa nini sijaona pesa kubwa kwa user?**
A: Direct Links zinalipa kwa CPM/CPC ndogo sana. Weka rewards chini ili usifilisike.

**Q: Nitawezaje kujua user aliview tangazo kweli?**
A: Hutaweza 100%. Timer + Anti-fraud limits ndio ulinzi wako.

**Q: Kwa nini postback haifanyi kazi?**
A: Kwa sababu Direct Links hazina postback! Postback ni kwa Monetag SDK Rewarded tu.

---

_Last Updated: {{ now() }}_


# SKYpesa Mobile App Specification

## 1. Overview
**App Name:** SKYpesa  
**Platform:** Android & iOS (Cross-platform recommended via Flutter or React Native)  
**Theme:** Dark Mode, Premium, Glassmorphism  
**Primary Color:** Emerald Green (`#10b981`)  

## 2. Design System (UI/UX)
The app should reflect the "unyama" (premium/cool) aesthetic of the web version.

### Color Palette
- **Primary:** `#10b981` (Emerald Green)
- **Primary Gradient:** `linear-gradient(135deg, #10b981 0%, #059669 100%)`
- **Background Darker:** `#0a0a0a` (Main Background)
- **Background Card:** `#1a1a1a` (Cards/Containers)
- **Text Primary:** `#ffffff`
- **Text Secondary:** `#a1a1aa`
- **Success:** `#10b981`
- **Error:** `#ef4444`
- **Warning:** `#f59e0b`

### Styling Guidelines
- **Glassmorphism:** Use semi-transparent backgrounds with blur effects for cards and overlays (`backdrop-filter: blur(12px)`).
- **Shadows:** Soft, glowing shadows (`box-shadow: 0 0 20px rgba(16, 185, 129, 0.3)`).
- **Typography:** Modern sans-serif (Inter or Roboto). Bold headings, clean body text.
- **Animations:** Smooth transitions, micro-interactions on buttons, pulse effects for important elements.

---

## 3. User Flow
1.  **Onboarding:** User opens app -> Splash Screen -> Login or Register.
2.  **Dashboard:** User lands on Home -> Sees Balance & Daily Goal -> Checks Active Plan.
3.  **Earning:** User goes to Tasks -> Selects Task -> Completes Task -> Balance Updates.
4.  **Upgrading:** User views Plans -> Selects Plan -> Pays via Mobile Money (ZenoPay) -> Plan Upgraded.
5.  **Cashout:** User goes to Wallet -> Clicks Withdraw -> Enters Amount -> Receives Money.

---

## 4. Features & Screens

### A. Authentication
-   **Splash Screen:** Animated Logo with glowing effect.
-   **Login Screen:** Email/Phone & Password. "Forgot Password" link. Social login (optional).
-   **Register Screen:** Name, Email, Phone, Password, Referral Code (optional).
-   **OTP Verification:** For account activation or password reset.

### B. Core Navigation (Bottom Tab Bar)
1.  **Home**
2.  **Tasks**
3.  **Wallet**
4.  **Team (Referrals)**
5.  **Profile**

### C. Screen Details

#### 1. Home (Dashboard)
-   **Header:** Greeting (e.g., "Habari, John!"), Notification Icon.
-   **Balance Card:** Glassmorphic card showing Current Balance.
-   **Daily Goal:** Progress bar showing tasks completed vs daily target.
-   **Quick Actions:** Buttons for "Withdraw", "Upgrade", "Support".
-   **Live Stats:** Scrolling ticker of recent withdrawals or user activities.

#### 2. Tasks Screen
-   **Filter:** All, Premium, Free.
-   **Task List:** Cards showing:
    -   Task Title/Icon
    -   Reward Amount (e.g., "Tsh 500")
    -   Timer/Duration
    -   "Start" Button
-   **Task Execution View:**
    -   Timer countdown.
    -   Instructions.
    -   "Complete" button (enabled after timer).

#### 3. Wallet Screen
-   **Total Earnings:** Big bold text.
-   **Withdraw Button:** Floating Action Button or prominent button.
-   **Transaction History:** List of recent earnings and withdrawals (Scrollable).
    -   Green text for Earnings (+).
    -   Red text for Withdrawals (-).

#### 4. Withdrawal Screen
-   **Input:** Amount to withdraw.
-   **Method:** Select Payment Method (M-Pesa, Tigo Pesa, Airtel Money).
-   **Account Number:** Pre-filled from profile or editable.
-   **Confirm Button:** With confirmation modal.

#### 5. Subscription Plans Screen
-   **Carousel:** Swipeable cards for each plan (Free, VIP, Premium).
-   **Plan Details:** Price, Daily Limit, Cost per Task.
-   **Subscribe Button:** Triggers payment gateway (ZenoPay).

#### 6. Team (Referrals)
-   **Referral Link:** Copy button.
-   **Stats:** Total Invited, Total Earnings from Referrals.
-   **Leaderboard:** Top referrers list.

#### 7. Profile & Settings
-   **User Info:** Avatar, Name, Email.
-   **Account Settings:** Change Password, Update Phone.
-   **Support:** Link to WhatsApp or In-app Ticket system.
-   **App Info:** Version, Terms of Service.
-   **Logout:** Red button.

---

## 5. API Endpoints (Ready in Backend)
The Laravel backend already provides these API endpoints (v1):

-   **Auth:** `/api/v1/auth/login`, `/api/v1/auth/register`
-   **User:** `/api/v1/user/profile`, `/api/v1/user/dashboard`
-   **Tasks:** `/api/v1/tasks`, `/api/v1/tasks/{id}/complete`
-   **Wallet:** `/api/v1/wallet`, `/api/v1/withdrawals`
-   **Plans:** `/api/v1/plans`, `/api/v1/subscriptions/subscribe/{plan}`
-   **Referrals:** `/api/v1/referrals`

## 6. Technical Recommendations
-   **Framework:** Flutter (for best performance & UI) or React Native.
-   **State Management:** Provider/Bloc (Flutter) or Redux/Context (React Native).
-   **Networking:** Dio (Flutter) or Axios (React Native).
-   **Local Storage:** Shared Preferences / Hive for storing tokens.

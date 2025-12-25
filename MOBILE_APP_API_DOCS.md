# SKYpesa Mobile App API Documentation

**Base URL:** `https://skypesa.hosting.hollyn.online/api/v1`  
**Headers Required:**
- `Accept: application/json`
- `Content-Type: application/json`
- `Authorization: Bearer <token>` (For protected routes)

---

## 1. Public & App Info
Endpoints accessible without authentication.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/health` | Check API status and version. |
| `GET` | `/info` | Get app metadata (version, support contact, maintenance mode). |
| `GET` | `/plans` | List all subscription plans (public view). |

---

## 2. Authentication
Manage user sessions and registration.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/auth/register` | Create a new account. Body: `name`, `email`, `phone`, `password`, `password_confirmation`, `referral_code` (optional). |
| `POST` | `/auth/login` | Login to get access token. Body: `email` (or phone), `password`. |
| `POST` | `/auth/forgot-password` | Request password reset OTP/Link. Body: `email`. |
| `POST` | `/auth/reset-password` | Reset password using OTP/Token. |
| `POST` | `/auth/verify-email` | Verify email address. |
| `POST` | `/auth/resend-verification`| Resend verification email. |
| `POST` | `/auth/logout` | **(Auth Required)** Invalidate current token. |
| `POST` | `/auth/refresh` | **(Auth Required)** Refresh access token. |

---

## 3. User Profile & Dashboard
**(Auth Required)** - Manage user data.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/user/profile` | Get logged-in user details. |
| `PUT` | `/user/profile` | Update profile info. Body: `name`, `phone`. |
| `POST` | `/user/avatar` | Upload/Update profile picture. |
| `PUT` | `/user/password` | Change password. Body: `current_password`, `new_password`. |
| `GET` | `/user/dashboard` | Get dashboard stats (balance, active plan, daily goal progress). |
| `GET` | `/user/activity` | Get recent activity summary. |
| `POST` | `/user/fcm-token` | Update Firebase Cloud Messaging token for push notifications. |
| `DELETE`| `/user/account` | Delete user account (Permanent). |

---

## 4. Tasks System
**(Auth Required)** - Earn money by completing tasks.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/tasks` | List available tasks. Query params: `filter=premium|free`. |
| `GET` | `/tasks/{task_id}` | Get details of a specific task. |
| `POST` | `/tasks/{task_id}/start` | Start a task (locks it for the user). |
| `POST` | `/tasks/{task_id}/status` | Check if task requirements are met (e.g., time elapsed). |
| `POST` | `/tasks/{task_id}/complete`| Submit task for completion and reward. |
| `POST` | `/tasks/cancel` | Cancel currently active task. |
| `GET` | `/tasks/activity/current` | Get the currently active/in-progress task. |
| `GET` | `/tasks/history/completed` | Get history of completed tasks. |

---

## 5. Wallet & Withdrawals
**(Auth Required)** - Manage earnings and cashouts.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/wallet` | Get wallet balance and overview. |
| `GET` | `/wallet/transactions` | List all transactions (earnings, withdrawals, payments). |
| `GET` | `/wallet/earnings` | Get earnings summary breakdown. |
| `GET` | `/withdrawals` | List user's withdrawal history. |
| `GET` | `/withdrawals/info` | Get withdrawal limits, fees, and available methods. |
| `POST` | `/withdrawals` | Request a new withdrawal. Body: `amount`, `method`, `account_number`. |
| `DELETE`| `/withdrawals/{id}` | Cancel a pending withdrawal request. |

---

## 6. Subscriptions (Plans)
**(Auth Required)** - Upgrade account for better rewards.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/subscriptions/current` | Get details of user's current active plan. |
| `POST` | `/subscriptions/subscribe/{plan_id}`| Subscribe to a plan (if free or balance sufficient). |
| `POST` | `/subscriptions/pay/{plan_id}` | Initiate payment for a plan (e.g., via ZenoPay). |
| `GET` | `/subscriptions/payment-status/{order_id}`| Check status of a subscription payment. |
| `GET` | `/subscriptions/history` | Get history of past subscriptions. |

---

## 7. Referrals (Team)
**(Auth Required)** - Invite friends and earn.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/referrals` | Get referral link and basic stats. |
| `GET` | `/referrals/users` | List users referred by the current user. |
| `GET` | `/referrals/earnings` | Get detailed referral earnings history. |
| `GET` | `/referrals/stats` | Get advanced referral statistics. |

---

## 8. Notifications
**(Auth Required)** - In-app alerts.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/notifications` | List all notifications. |
| `GET` | `/notifications/unread-count`| Get count of unread notifications. |
| `PUT` | `/notifications/{id}/read` | Mark a specific notification as read. |
| `PUT` | `/notifications/read-all` | Mark all notifications as read. |
| `DELETE`| `/notifications/{id}` | Delete a notification. |

---

## 9. Error Handling
The API returns standard HTTP status codes:
- `200 OK`: Success.
- `201 Created`: Resource created successfully.
- `400 Bad Request`: Validation error or invalid input.
- `401 Unauthorized`: Invalid or missing token.
- `403 Forbidden`: User does not have permission (e.g., plan limit reached).
- `404 Not Found`: Resource not found.
- `422 Unprocessable Entity`: Validation errors (details in response body).
- `500 Server Error`: Something went wrong on the server.

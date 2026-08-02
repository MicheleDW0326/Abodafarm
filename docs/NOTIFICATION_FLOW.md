# AbodaFarm Notification Flow Document

## Overview

This document describes the notification system architecture for AbodaFarm, including database notifications, Firebase Cloud Messaging (FCM) push notifications, and email notifications.

---

## Architecture

### Notification Channels

| Channel | Purpose | Implementation |
|---------|---------|----------------|
| **Database** | In-app notifications | `CustomerNotification` / `PeternakNotification` models |
| **FCM** | Mobile/Web push notifications | `FcmService` with Firebase Cloud Messaging |
| **Email** | Critical event emails | `OrderNotificationMail` Mailable |

### Notification Types

#### Customer Notifications (Pelanggan)
| Type | Trigger | Channels |
|------|---------|----------|
| `payment_pending` | Order created, awaiting payment | Database, FCM |
| `payment_success` | Payment confirmed | Database, FCM, Email |
| `payment_failed` | Payment failed | Database, FCM, Email |
| `payment_refunded` | Payment refunded | Database, FCM |
| `booking_approved` | Booking approved by farmer | Database, FCM, Email |
| `booking_rejected` | Booking rejected by farmer | Database, FCM, Email |
| `order_status` | Generic status update | Database, FCM |
| `order_shipped` | Order shipped | Database, FCM |
| `order_delivered` | Order delivered | Database, FCM, Email |
| `order_completed` | Order completed | Database, FCM, Email |
| `order_cancelled` | Order cancelled | Database, FCM |

#### Farmer Notifications (Peternak)
| Type | Trigger | Channels |
|------|---------|----------|
| `new_booking` | New booking received | Database, FCM |
| `booking_approved` | Customer approved booking | Database, FCM |
| `booking_rejected` | Customer rejected booking | Database, FCM |
| `booking_cancelled` | Customer cancelled booking | Database, FCM |
| `order_processing` | Order being processed | Database, FCM |
| `new_review` | New product review | Database, FCM |
| `product_published` | Product published | Database, FCM |
| `product_disabled` | Product disabled by admin | Database, FCM |

---

## Notification Flow

### Order Status Change Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                        STATUS CHANGE TRIGGER                         │
│  (OrderStateMachine, PaymentController, BookingController, etc.)    │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     OrderStatusChanged Event                        │
│                     (dispatched by OrderStateMachine)               │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                    ┌───────────────┼───────────────┐
                    ▼               ▼               ▼
          ┌─────────────────┐ ┌───────────┐ ┌─────────────────┐
          │   Listeners     │ │   Listeners     │ │   Listeners     │
          │ (Queued Async)  │ │ (Queued Async)  │ │ (Queued Async)  │
          └────────┬────────┘ └─────┬─────┘ └────────┬────────┘
                   │                │                │
                   ▼                ▼                ▼
          ┌─────────────────┐ ┌───────────┐ ┌─────────────────┐
          │   Customer      │ │   Farmer   │ │   Email         │
          │   Notification  │ │   Notification  │ │   (if needed)  │
          └─────────────────┘ └───────────┘ └─────────────────┘
```

### New Booking Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                      BOOKING CREATED                                │
│                      (BookingController)                            │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     NewBookingReceived Event                         │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                   NotifyFarmerNewBooking Listener                    │
│                   (Notifies ALL farmers with products in order)      │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
                    ┌───────────────────────────────────────┐
                    │         Farmer (Peternak)                │
                    │  ┌────────────────┐ ┌────────────────┐  │
                    │  │  Database Notif │ │   FCM Push    │  │
                    │  └────────────────┘ └────────────────┘  │
                    └───────────────────────────────────────┘
```

---

## Role-Based Notification Recipients

### Customer (Pelanggan) Notifications
- **Who receives:** User who placed the order
- **Order Types:** Both `cart` (purchase) and `inquiry` (booking)
- **Statuses:** All status transitions

### Farmer (Peternak) Notifications
- **Who receives:** All farmers whose products are in the order
- **Order Types:** Primarily `inquiry` (booking), but also `cart` for processing status
- **Statuses:** `new_booking`, `booking_approved`, `booking_rejected`, `processing`, `cancelled`

### Admin Notifications
- **Who receives:** Admin and Staff roles
- **Triggers:** Payment errors, transaction failures, farmer registrations, product verification

---

## Duplicate Prevention

### Mechanism
All notifications include a 5-minute cooldown period to prevent duplicate notifications:

```php
const DUPLICATE_COOLDOWN = 5; // minutes

// Check before creating notification
if ($this->isDuplicate($userId, $type, $referenceId)) {
    return; // Skip duplicate
}
```

### Check Logic
```php
public function isDuplicate(int $userId, string $type, string $referenceId): bool
{
    $cutoff = now()->subMinutes(self::DUPLICATE_COOLDOWN);

    return CustomerNotification::where('user_id', $userId)
        ->where('type', $type)
        ->whereJsonContains('data', ['reference_id' => $referenceId])
        ->where('created_at', '>=', $cutoff)
        ->exists();
}
```

---

## Status Transition Matrix

### Cart Order (Pembelian - Fresh Meat)

| From Status | To Status | Customer Notified | Farmer Notified | Email |
|-------------|-----------|-------------------|-----------------|-------|
| - | `pending` | Yes (payment pending) | No | No |
| `pending` | `paid` | Yes | No | Yes |
| `pending` | `cancelled` | Yes | No | No |
| `paid` | `processing` | Yes | No | No |
| `paid` | `cancelled` | Yes | No | No |
| `processing` | `shipped` | Yes | No | No |
| `processing` | `cancelled` | Yes | No | No |
| `shipped` | `delivered` | Yes | No | Yes |
| `delivered` | `completed` | Yes | No | Yes |

### Inquiry Order (Booking - Livestock)

| From Status | To Status | Customer Notified | Farmer Notified | Email |
|-------------|-----------|-------------------|-----------------|-------|
| - | `pending` | Yes (booking pending) | Yes (new booking) | No |
| `pending` | `approved` | Yes | Yes | Yes |
| `pending` | `rejected` | Yes | Yes | Yes |
| `pending` | `cancelled` | Yes | Yes | No |
| `approved` | `processing` | Yes | Yes | No |
| `approved` | `cancelled` | Yes | Yes | No |
| `processing` | `shipped` | Yes | No | No |
| `processing` | `cancelled` | Yes | Yes | No |
| `shipped` | `delivered` | Yes | No | Yes |
| `delivered` | `completed` | Yes | No | Yes |

---

## Email Notification Triggers

Emails are sent only for critical status changes:

| Type | Subject | Template |
|------|---------|----------|
| `payment_success` | Pembayaran Berhasil - AbodaFarm | `emails.order-notification` |
| `booking_approved` | Booking Disetujui - AbodaFarm | `emails.order-notification` |
| `booking_rejected` | Booking Ditolak - AbodaFarm | `emails.order-notification` |
| `order_delivered` | Pesanan Diterima - AbodaFarm | `emails.order-notification` |
| `order_completed` | Pesanan Selesai - AbodaFarm | `emails.order-notification` |
| `payment_failed` | Pembayaran Gagal - AbodaFarm | `emails.order-notification` |

---

## Files Reference

### Services
- `app/Services/NotificationService.php` - Main notification dispatcher
- `app/Services/FcmService.php` - Firebase Cloud Messaging integration

### Models
- `app/Models/CustomerNotification.php` - Customer notification model
- `app/Models/PeternakNotification.php` - Farmer notification model
- `app/Models/FcmToken.php` - FCM device tokens

### Events
- `app/Events/OrderStatusChanged.php` - Dispatched on order status change
- `app/Events/NewBookingReceived.php` - Dispatched on new booking
- `app/Events/PaymentReceived.php` - Dispatched on payment received

### Listeners
- `app/Listeners/SendOrderStatusNotification.php` - Handles customer notifications
- `app/Listeners/NotifyFarmerNewBooking.php` - Handles farmer booking notifications

### Mailables
- `app/Mail/OrderNotificationMail.php` - Order notification email

### Views
- `resources/views/emails/order-notification.blade.php` - Email template

---

## Configuration

### Environment Variables
```env
# Firebase Cloud Messaging
FCM_SERVER_KEY=your_fcm_server_key

# Email Configuration (Laravel default)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

### Queue Configuration
Notifications are queued for async processing:
```php
class SendOrderStatusNotification implements ShouldQueue
{
    public $queue = 'notifications';
}
```

---

## Testing Checklist

- [ ] New booking creates notification for correct farmer(s)
- [ ] Payment success notifies customer and sends email
- [ ] Booking approval notifies both customer and farmer
- [ ] Duplicate notifications are prevented within 5-minute window
- [ ] FCM notifications arrive on mobile/web devices
- [ ] Email notifications are queued and sent
- [ ] Admin receives payment error notifications
- [ ] Notification dropdown shows unread count
- [ ] Marking notification as read works correctly

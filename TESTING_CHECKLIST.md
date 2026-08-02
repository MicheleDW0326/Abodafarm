# AbodaFarm Testing Checklist

## Pre-Testing Setup
```bash
# 1. Run migrations
php artisan migrate:fresh --seed

# 2. Seed permissions
php artisan db:seed --class=PermissionSeeder

# 3. Seed status tables
php artisan db:seed --class=OrderStatusSeeder
php artisan db:seed --class=BookingStatusSeeder
php artisan db:seed --class=ShipmentStatusSeeder
```

---

## 1. Database Consistency Tests

### 1.1 Status Tables
- [ ] `order_statuses` table contains: pending, paid, payment_failed, payment_refunded
- [ ] `booking_statuses` table contains: pending, approved, rejected, survey_scheduled, ready_for_delivery
- [ ] `shipment_statuses` table contains: processing, shipped, delivered, completed, cancelled, ready_to_ship

### 1.2 Foreign Key Columns
- [ ] `orders` table has `payment_status_id` column
- [ ] `orders` table has `booking_status_id` column
- [ ] `orders` table has `shipment_status_id` column
- [ ] FK constraints are properly set

### 1.3 Model Relationships
- [ ] Order model has `paymentStatusRelation()` relationship
- [ ] Order model has `bookingStatusRelation()` relationship
- [ ] Order model has `shipmentStatusRelation()` relationship
- [ ] Order model has `effective_status` accessor

---

## 2. Role Permissions Tests

### 2.1 Admin
- [ ] Can view all orders (cart and booking)
- [ ] Can approve/reject bookings
- [ ] Can process, ship, deliver orders
- [ ] Can access all reports
- [ ] Can manage users
- [ ] Can delete users

### 2.2 Staff
- [ ] Can view all orders
- [ ] Can process orders (pending → processing)
- [ ] Can mark orders ready_to_ship
- [ ] Can ship orders
- [ ] Can mark orders delivered
- [ ] Can access Staff Operations page (`/operasi`)
- [ ] Cannot delete users
- [ ] Can access reports with export

### 2.3 Farmer/Peternak
- [ ] Can view only own product bookings
- [ ] Can approve/reject bookings for own products
- [ ] Can schedule survey for bookings
- [ ] Can mark booking ready_for_delivery
- [ ] Can update own product status
- [ ] Can access own wallet and sales history
- [ ] Cannot access admin-only reports

### 2.4 Customer/Pelanggan
- [ ] Can view only own orders
- [ ] Can cancel own pending orders
- [ ] Can view booking progress
- [ ] Can pay orders via Midtrans
- [ ] Can submit reviews after completion

---

## 3. Status Transition Tests

### 3.1 Cart Order Flow (Fresh Meat)
```
pending → paid → processing → shipped → delivered → completed
           ↓
     payment_failed
```

- [ ] pending order can be paid via Midtrans
- [ ] paid order transitions to processing
- [ ] processing order can be shipped
- [ ] shipped order can be marked delivered
- [ ] delivered order can be completed
- [ ] Failed payment transitions to payment_failed
- [ ] Cancelled orders cannot proceed further

### 3.2 Booking Flow (Inquiry)
```
pending → approved → survey_scheduled → ready_for_delivery → processing → shipped → delivered → completed
     ↓         ↓
  rejected  cancelled
```

- [ ] pending booking can be approved by farmer
- [ ] pending booking can be rejected by farmer
- [ ] approved booking can have survey scheduled
- [ ] survey_scheduled booking can be marked ready_for_delivery
- [ ] ready_for_delivery booking transitions to processing
- [ ] rejected/cancelled bookings are terminal

### 3.3 Staff Operations
- [ ] Staff can filter by quick filters (ready_to_process, processing, etc.)
- [ ] Staff can bulk update orders
- [ ] Staff actions are logged with TRIGGER_STAFF
- [ ] Invalid transitions are blocked with error message

---

## 4. Notification Tests

### 4.1 Customer Notifications
- [ ] Receives notification on booking approved
- [ ] Receives notification on booking rejected
- [ ] Receives notification on survey scheduled
- [ ] Receives notification on ready_for_delivery
- [ ] Receives notification on payment success
- [ ] Receives notification on payment failed
- [ ] Receives notification on order shipped
- [ ] Receives notification on order delivered

### 4.2 Farmer Notifications
- [ ] Receives notification on new booking
- [ ] Receives notification when booking approved
- [ ] Receives notification when booking rejected
- [ ] Receives notification when booking cancelled by customer

### 4.3 Notification Dropdown
- [ ] Shows real-time notifications
- [ ] Marks notifications as read on click
- [ ] Refreshes via polling/WebSocket

---

## 5. Payment Flow Tests

### 5.1 Midtrans Integration
- [ ] Snap token is generated on checkout
- [ ] Payment modal opens with Midtrans Snap
- [ ] Successful payment updates order status to paid
- [ ] Failed payment updates order status to payment_failed
- [ ] Webhook updates payment status correctly
- [ ] Order can retry payment after failure

### 5.2 Payment Status Display
- [ ] MyOrders shows payment progress indicator
- [ ] Payment status badge shows correct color
- [ ] Transaction history shows all payment attempts

---

## 6. Booking Flow Tests

### 6.1 Farmer View (IncomingBookings)
- [ ] Shows all bookings for own products
- [ ] Status filter works correctly
- [ ] Search by order number or customer name works
- [ ] Approve button works (pending → approved)
- [ ] Reject button works (pending → rejected)
- [ ] Schedule Survey modal opens
- [ ] Survey date picker works
- [ ] Schedule Survey saves and notifies customer
- [ ] Mark Ready for Delivery works (approved → ready_for_delivery)

### 6.2 Customer View (MyBookings)
- [ ] Shows all own bookings
- [ ] Booking progress tracker shows correct steps
- [ ] Status explanation is clear
- [ ] Cancel booking works (pending only)
- [ ] WhatsApp contact link works

---

## 7. Dashboard Tests

### 7.1 Admin Dashboard
- [ ] Shows correct order statistics
- [ ] Shows correct booking statistics
- [ ] Shows revenue summary
- [ ] Links to detailed reports work

### 7.2 Staff Operations Dashboard
- [ ] Shows orders by quick filters
- [ ] Stats cards show correct counts
- [ ] Bulk selection works
- [ ] Bulk actions update multiple orders

### 7.3 Farmer Dashboard
- [ ] Shows pending bookings count
- [ ] Shows total earnings
- [ ] Shows recent orders

### 7.4 Customer Dashboard
- [ ] Shows recent orders
- [ ] Shows active bookings
- [ ] Quick access to checkout

---

## 8. Report Tests

### 8.1 Admin Financial Report
- [ ] Overview tab shows summary stats
- [ ] Orders tab shows order status distribution
- [ ] Bookings tab shows booking statistics
- [ ] Payments tab shows payment success/failure
- [ ] Revenue tab shows earnings breakdown
- [ ] Operational tab shows staff activity

### 8.2 Farmer Reports
- [ ] Sales History shows correct data
- [ ] Status filter works
- [ ] Date range filter works
- [ ] Wallet shows pending earnings
- [ ] Transaction history is accurate

---

## 9. Livewire Component Tests

### 9.1 Checkout
- [ ] Form validation works
- [ ] Address selection works
- [ ] New address creation works
- [ ] Order placement creates order and clears cart
- [ ] Payment modal opens after order
- [ ] Snap token is generated

### 9.2 MyOrders
- [ ] Order listing works
- [ ] Status filter works
- [ ] Order detail modal shows timeline
- [ ] Cancel order works
- [ ] Payment progress display works

### 9.3 MyBookings
- [ ] Booking listing works
- [ ] Status filter works
- [ ] Booking detail shows timeline
- [ ] Booking progress tracker works
- [ ] Status explanations display correctly

---

## 10. Export/Import Tests

### 10.1 Product Import
- [ ] CSV import works
- [ ] Validation errors are shown
- [ ] Success message on completion

### 10.2 Report Export
- [ ] Excel export generates file
- [ ] Data matches displayed report
- [ ] File downloads correctly

---

## 11. Validation Tests

### 11.1 Form Validation
- [ ] Required fields are validated
- [ ] Phone number format is validated
- [ ] Address length is limited
- [ ] Price/quantity are positive integers

### 11.2 Business Logic Validation
- [ ] Cannot approve already-approved booking
- [ ] Cannot reject already-rejected booking
- [ ] Cannot cancel completed order
- [ ] Cannot ship undelivered order
- [ ] Cannot deliver unshipped order

---

## 12. Edge Cases

### 12.1 Status Edge Cases
- [ ] Order with no status FK columns loads correctly
- [ ] Mixed FK and legacy status handled
- [ ] Cancelled order shows correct badge

### 12.2 Permission Edge Cases
- [ ] Farmer cannot see another farmer's bookings
- [ ] Customer cannot see another customer's orders
- [ ] Staff can only access `/operasi`, not `/admin/*`

### 12.3 Payment Edge Cases
- [ ] Duplicate webhook calls don't duplicate status changes
- [ ] Expired Snap token shows retry option
- [ ] Network error shows appropriate message

---

## Quick Smoke Test Sequence

1. **Create Booking** (Customer)
   - Go to product page
   - Click "Booking"
   - Fill form and submit

2. **Approve Booking** (Farmer)
   - Login as farmer
   - Go to `/peternak/booking`
   - Find booking, click "Detail"
   - Click "Setujui"

3. **Schedule Survey** (Farmer)
   - With same booking open
   - Click "Jadwalkan Survei"
   - Pick date and save

4. **Mark Ready** (Farmer)
   - Status should now be "Survei Dijadwalkan"
   - Click "Tandai Siap Kirim"

5. **Process Order** (Staff)
   - Login as staff
   - Go to `/operasi`
   - Find order, click "Proses"
   - Status should change to "Diproses"

6. **Ship Order** (Staff)
   - With same order
   - Click "Kirim"
   - Enter tracking number if prompted

7. **Complete Order** (Staff/Customer)
   - Mark as "Diterima" then "Selesai"

---

## Files Modified During Audit

| File | Issue |
|------|-------|
| `app/Enums/BookingStatusEnum.php` | Missing survey_scheduled, ready_for_delivery |
| `database/seeders/BookingStatusSeeder.php` | Missing survey_scheduled, ready_for_delivery |
| `database/seeders/ShipmentStatusSeeder.php` | Missing ready_to_ship |
| `app/Livewire/Peternak/IncomingBookings.php` | Bypasses state machine |
| `app/Services/OrderStateMachine.php` | Missing booking workflow transitions |
| `app/Listeners/SendOrderStatusNotification.php` | Missing survey_scheduled/ready_for_delivery notifications |

---

## Expected Fixes Required

1. Add `survey_scheduled` and `ready_for_delivery` to BookingStatusEnum
2. Add `survey_scheduled` and `ready_for_delivery` to BookingStatusSeeder
3. Add `ready_to_ship` to ShipmentStatusSeeder
4. Add `survey_scheduled` and `ready_for_delivery` to ShipmentStatusEnum (or create new enum)
5. Update OrderStateMachine to handle booking workflow transitions
6. Update IncomingBookings to use state machine for all transitions
7. Add notifications for survey_scheduled and ready_for_delivery in listener

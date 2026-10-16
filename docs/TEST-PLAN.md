# AbodaFarm System Testing Plan
## Comprehensive Testing Documentation

---

## 1. Testing Overview

### 1.1 Testing Strategy

| Level | Scope | Tool | Target |
|-------|-------|------|--------|
| Unit Testing | Individual components | PHPUnit/Pest | 80%+ coverage |
| Feature Testing | Business workflows | Pest Feature Tests | All use cases |
| Integration Testing | API endpoints | Postman/Insomnia | All endpoints |
| Browser Testing | UI components | Laravel Dusk | Critical flows |

### 1.2 Testing Environment

| Component | Configuration |
|-----------|---------------|
| Database | SQLite in-memory (testing) |
| Cache | Array driver |
| Queue | Sync (synchronous) |
| Mail | Array driver (mailhog equivalent) |
| Session | Array driver |

---

## 2. Test Categories

### 2.1 Authentication Module

| Test ID | Test Case | Expected Result | Priority |
|---------|-----------|-----------------|----------|
| AUTH-01 | User can register with valid data | Registration successful, user created | P0 |
| AUTH-02 | User cannot register with duplicate email | Error message displayed | P0 |
| AUTH-03 | User can login with valid credentials | Login successful, redirect to dashboard | P0 |
| AUTH-04 | User cannot login with wrong password | Error message, stay on login | P0 |
| AUTH-05 | User can logout | Session destroyed, redirect to home | P1 |
| AUTH-06 | Password reset flow works | Email sent, password changed | P1 |
| AUTH-07 | Email verification works | Account verified after click | P1 |
| AUTH-08 | Two-factor authentication works | 2FA code required after password | P2 |

### 2.2 Customer Module

#### 2.2.1 Catalog & Browsing

| Test ID | Test Case | Expected Result | Priority |
|---------|-----------|-----------------|----------|
| CAT-01 | View product catalog with pagination | Products displayed correctly | P0 |
| CAT-02 | Search products by keyword | Matching products returned | P0 |
| CAT-03 | Filter by category | Only category products shown | P0 |
| CAT-04 | Filter by type (live/fresh/supply) | Only type products shown | P0 |
| CAT-05 | Filter by price range | Products within range shown | P1 |
| CAT-06 | View product detail | All product info displayed | P0 |
| CAT-07 | View product reviews | Reviews displayed with ratings | P1 |

#### 2.2.2 Shopping Cart

| Test ID | Test Case | Expected Result | Priority |
|---------|-----------|-----------------|----------|
| CART-01 | Add product to cart | Product added, cart updated | P0 |
| CART-02 | Add out-of-stock product | Error message shown | P0 |
| CART-03 | Update cart quantity | Quantity updated | P0 |
| CART-04 | Remove item from cart | Item removed | P0 |
| CART-05 | Clear cart | All items removed | P1 |
| CART-06 | View cart total | Correct total calculated | P0 |

#### 2.2.3 Checkout & Payment

| Test ID | Test Case | Expected Result | Priority |
|---------|-----------|-----------------|----------|
| CHECK-01 | Complete checkout flow | Order created | P0 |
| CHECK-02 | Select saved address | Address auto-filled | P0 |
| CHECK-03 | Add new address | Address saved | P0 |
| CHECK-04 | Apply valid promo code | Discount applied | P0 |
| CHECK-05 | Apply invalid promo code | Error message shown | P0 |
| CHECK-06 | Complete payment via Midtrans | Payment processed | P0 |
| CHECK-07 | Payment callback updates order | Order status updated | P0 |
| CHECK-08 | Failed payment handling | Stock released, order cancelled | P0 |

#### 2.2.4 Order Tracking

| Test ID | Test Case | Expected Result | Priority |
|---------|-----------|-----------------|----------|
| TRACK-01 | View order list | Orders displayed | P0 |
| TRACK-02 | View order details | Full order info shown | P0 |
| TRACK-03 | Track order status | Current status displayed | P0 |
| TRACK-04 | Cancel pending order | Order cancelled | P0 |
| TRACK-05 | Confirm delivery | Order marked completed | P0 |

#### 2.2.5 Reviews

| Test ID | Test Case | Expected Result | Priority |
|---------|-----------|-----------------|----------|
| REV-01 | Submit product review | Review created | P0 |
| REV-02 | Submit review with photos | Photos uploaded | P1 |
| REV-03 | Edit own review | Review updated | P1 |
| REV-04 | Delete own review | Review removed | P2 |

### 2.3 Farmer Module

| Test ID | Test Case | Expected Result | Priority |
|---------|-----------|-----------------|----------|
| FARM-01 | View dashboard | Stats displayed | P0 |
| FARM-02 | Add new product | Product created | P0 |
| FARM-03 | Edit product | Product updated | P0 |
| FARM-04 | Delete product | Product removed | P0 |
| FARM-05 | Update stock | Stock updated | P0 |
| FARM-06 | View inventory logs | Logs displayed | P1 |
| FARM-07 | View booking list | Bookings displayed | P0 |
| FARM-08 | Approve booking | Booking approved | P0 |
| FARM-09 | Reject booking | Booking rejected | P0 |
| FARM-10 | View incoming orders | Orders displayed | P0 |
| FARM-11 | Update farm profile | Profile updated | P1 |
| FARM-12 | Manage gallery | Gallery updated | P2 |

### 2.4 Staff Module

| Test ID | Test Case | Expected Result | Priority |
|---------|-----------|-----------------|----------|
| STAFF-01 | View assigned orders | Orders displayed | P0 |
| STAFF-02 | View order details | Order info shown | P0 |
| STAFF-03 | Start delivery | Delivery started | P0 |
| STAFF-04 | Update delivery status | Status updated | P0 |
| STAFF-05 | Upload delivery proof | Photo uploaded | P0 |
| STAFF-06 | Complete delivery | Delivery completed | P0 |
| STAFF-07 | View delivery history | History displayed | P1 |

### 2.5 Admin Module

| Test ID | Test Case | Expected Result | Priority |
|---------|-----------|-----------------|----------|
| ADMIN-01 | View all products | Products list displayed | P0 |
| ADMIN-02 | Import products via Excel | Products bulk imported | P0 |
| ADMIN-03 | Edit any product | Product updated | P0 |
| ADMIN-04 | Delete product | Product removed | P0 |
| ADMIN-05 | Manage categories | Categories CRUD | P0 |
| ADMIN-06 | View all orders | Orders list displayed | P0 |
| ADMIN-07 | Filter orders | Filtered results shown | P0 |
| ADMIN-08 | Assign staff to order | Staff assigned | P0 |
| ADMIN-09 | Manage promo codes | Promo CRUD | P0 |
| ADMIN-10 | View financial reports | Reports displayed | P0 |
| ADMIN-11 | Manage users | Users CRUD | P0 |
| ADMIN-12 | Verify farmers | Verification status changed | P0 |
| ADMIN-13 | Moderate reviews | Reviews approved/rejected | P0 |
| ADMIN-14 | Manage content (FAQ, Hero) | Content updated | P1 |

---

## 3. Test Execution Matrix

### 3.1 By Module

| Module | Test Cases | Automated | Manual | Status |
|--------|------------|-----------|--------|--------|
| Authentication | 8 | 6 | 2 | In Progress |
| Catalog | 7 | 4 | 3 | Pending |
| Cart | 6 | 5 | 1 | Pending |
| Checkout | 8 | 6 | 2 | Pending |
| Orders | 5 | 4 | 1 | Pending |
| Reviews | 4 | 3 | 1 | Pending |
| Farmer | 12 | 8 | 4 | Pending |
| Staff | 7 | 5 | 2 | Pending |
| Admin | 14 | 10 | 4 | Pending |
| **Total** | **71** | **51** | **20** | |

### 3.2 By Priority

| Priority | Count | Description |
|----------|-------|-------------|
| P0 (Critical) | 35 | Must pass, core functionality |
| P1 (High) | 25 | Important features |
| P2 (Medium) | 11 | Nice to have |

---

## 4. Test Data Requirements

### 4.1 Users

| Role | Required | Attributes |
|------|----------|-------------|
| Admin | 1 | admin role, verified |
| Staff | 2 | staf role, verified |
| Farmer | 3 | peternak role, verified |
| Customer | 5 | pelanggan role, verified |

### 4.2 Products

| Type | Count | Attributes |
|------|-------|------------|
| Live (Ternak) | 5 | Sapi, Kambing, Ayam |
| Fresh (Daging) | 3 | Daging Sapi, Ayam |
| Supply (Pakan) | 4 | Pakan, Vitamin |

### 4.3 Orders

| Type | Count | Status |
|------|-------|--------|
| Cart | 5 | various statuses |
| Inquiry | 3 | various statuses |

### 4.4 Promotions

| Type | Count | Valid Until |
|------|-------|-------------|
| Percentage | 2 | future |
| Fixed | 1 | future |
| Expired | 1 | past |

---

## 5. Test Scenarios

### 5.1 Happy Path - Complete Order

```
1. User registers → Account created
2. User logs in → Authenticated
3. Browse products → Products displayed
4. Add to cart → Cart updated
5. Checkout → Order form displayed
6. Select address → Address selected
7. Apply promo → Discount applied
8. Select payment → Midtrans initiated
9. Complete payment → Order paid
10. Track order → Status displayed
11. Confirm delivery → Order completed
```

### 5.2 Edge Cases

| Scenario | Test Case |
|----------|-----------|
| Stock depleted during checkout | Show error, suggest alternatives |
| Promo expired during checkout | Show error, allow retry without promo |
| Payment timeout | Cancel order, release stock |
| Double payment attempt | Prevent, show existing payment |
| Session timeout during checkout | Save cart, resume after login |

---

## 6. Bug Tracking

### 6.1 Severity Levels

| Level | Description | SLA |
|-------|-------------|-----|
| Critical | System down, data loss | 4 hours |
| High | Major feature broken | 24 hours |
| Medium | Feature degraded | 72 hours |
| Low | Minor issue | 1 week |

### 6.2 Bug Report Template

```markdown
## Bug Report: [Title]

**Severity:** P0/P1/P2/P3
**Module:** [Module Name]
**Test ID:** [Related Test ID]

### Description
[Clear description of the bug]

### Steps to Reproduce
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Expected Behavior
[What should happen]

### Actual Behavior
[What actually happens]

### Screenshots/Logs
[Attach relevant evidence]

### Environment
- Browser:
- OS:
- User Role:
```

---

## 7. Test Results

### 7.1 Test Run Summary

| Date | Tester | Total | Passed | Failed | Skipped |
|------|--------|-------|--------|--------|---------|

### 7.2 Defect Summary

| ID | Severity | Description | Status | Fixed Date |

---

## 8. Automation Plan

### 8.1 Current Status

| Component | Coverage | Framework |
|-----------|----------|-----------|
| Unit Tests | 60% | Pest |
| Feature Tests | 40% | Pest |
| Browser Tests | 0% | - |

### 8.2 Target Coverage

| Component | Q1 Target | Q2 Target |
|-----------|-----------|-----------|
| Unit Tests | 80% | 90% |
| Feature Tests | 70% | 85% |
| Browser Tests | 30% | 60% |

---

## 9. CI/CD Integration

### 9.1 Pipeline Stages

```
┌─────────────┐
│   Lint     │ ← Code style check (Pint)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Test     │ ← PHPUnit/Pest tests
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Build    │ ← Asset compilation
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Deploy    │ ← To staging/production
└─────────────┘
```

### 9.2 GitHub Actions Workflow

```yaml
name: Test
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: ./vendor/bin/pest
```

---

*Document Version: 1.0*
*Last Updated: 2026-10-16*

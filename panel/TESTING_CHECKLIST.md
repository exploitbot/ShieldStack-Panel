# ShieldStack Hosting Management System - Testing Checklist

## Pre-Testing Setup

1. Login as Admin:
   - URL: https://shieldstack.dev/panel/login.php
   - Email: eric@shieldstack.dev
   - Password: jinho2310

---

## Admin Panel Tests

### 1. Manage Plans Page (admin/manage-plans.php)

#### Desktop Testing:
- [ ] Page loads without errors
- [ ] All existing plans displayed correctly
- [ ] Plans show: name, category, type, price, billing_cycle, status
- [ ] "Add New Plan" button visible and clickable

#### Add New Plan:
- [ ] Click "Add New Plan" button
- [ ] Modal opens with form
- [ ] All fields present:
  - [ ] Name, category, type, description
  - [ ] Price, billing_cycle, status
  - [ ] Disk space, bandwidth
  - [ ] Databases, email accounts, subdomains, FTP accounts
  - [ ] Support level, display order
  - [ ] SSL certificates checkbox
  - [ ] Daily backups checkbox
  - [ ] Features textarea
- [ ] Fill form with test data:
  ```
  Name: Test Plan
  Category: vps
  Type: cloud
  Price: 19.99
  Billing Cycle: monthly
  Disk Space: 50GB
  Bandwidth: 2TB
  Databases: 20
  Email Accounts: 100
  Subdomains: 50
  FTP Accounts: 10
  Support Level: standard
  Display Order: 10
  Status: active
  SSL: checked
  Backups: checked
  Features: (one per line)
    - 2 vCPU Cores
    - 4GB RAM
    - NVMe Storage
  ```
- [ ] Click "Save Plan"
- [ ] Success message appears
- [ ] Page refreshes and new plan appears in list

#### Edit Plan:
- [ ] Click "Edit" on any plan
- [ ] Modal opens with form pre-filled
- [ ] Modify price to different value
- [ ] Click "Update Plan"
- [ ] Success message appears
- [ ] Changes reflected in plan list

#### Delete Plan:
- [ ] Click "Delete" on test plan
- [ ] Confirmation dialog appears
- [ ] Confirm deletion
- [ ] Plan removed from list

#### Mobile Testing (resize browser to <768px):
- [ ] Page remains usable
- [ ] Plans stack vertically
- [ ] Action buttons accessible
- [ ] Modal fits screen
- [ ] Form fields usable
- [ ] All text readable

---

### 2. Manage User Services (admin/user-services.php?user_id=1)

#### Desktop Testing:
- [ ] Navigate to Manage Users
- [ ] Click on a user
- [ ] Services page loads
- [ ] "Add Service" button visible

#### Add Service:
- [ ] Click "Add Service"
- [ ] Modal opens with form
- [ ] All fields present:
  - [ ] Plan dropdown (grouped by category)
  - [ ] Domain field
  - [ ] Status dropdown
  - [ ] Start date
  - [ ] Renewal date
  - [ ] Expiry date
  - [ ] Auto-renew checkbox
- [ ] Fill form:
  ```
  Plan: Select any active plan
  Domain: test.example.com
  Status: active
  Auto-renew: checked
  ```
- [ ] Submit form
- [ ] Service created successfully
- [ ] Service appears in list

#### View Service:
- [ ] Click "View" button on service
- [ ] Modal opens showing:
  - [ ] Plan information (disk, bandwidth, etc.)
  - [ ] Service information (dates, status)
  - [ ] All plan features listed
  - [ ] SSL/Backup status
  - [ ] Support level
- [ ] Close modal

#### Edit Service:
- [ ] Click "Edit" button
- [ ] Modal opens with pre-filled data
- [ ] Change domain name
- [ ] Change renewal date
- [ ] Toggle auto-renew
- [ ] Add notes
- [ ] Save changes
- [ ] Changes reflected in list

#### Suspend Service:
- [ ] Click "Suspend" button
- [ ] Modal asks for reason
- [ ] Enter reason: "Payment overdue"
- [ ] Submit
- [ ] Service status changes to "Suspended"
- [ ] "Unsuspend" button now available

#### Unsuspend Service:
- [ ] Click "Unsuspend" button
- [ ] Confirm action
- [ ] Service status changes to "Active"

#### Delete Service:
- [ ] Click "Delete" button
- [ ] Confirmation dialog
- [ ] Confirm deletion
- [ ] Service removed from list

#### Mobile Testing:
- [ ] Resize to mobile
- [ ] Table scrolls horizontally
- [ ] Action buttons stack properly
- [ ] Modals fit screen
- [ ] All functions work

---

## Client Panel Tests

### 3. Browse Plans Page (plans.php)

#### Desktop Testing:
- [ ] Logout from admin
- [ ] Login as regular customer (or create account)
- [ ] Navigate to "Browse Plans"
- [ ] Plans grouped by category:
  - [ ] Web Hosting section
  - [ ] VPS Servers section
  - [ ] Dedicated Servers section (if exists)
  - [ ] SSL Certificates section
  - [ ] Domain Names section
  - [ ] Other Services section (if exists)

#### Plan Card Display:
- [ ] Each plan shows:
  - [ ] Plan name
  - [ ] Price per billing cycle
  - [ ] Description
  - [ ] Plan specifications (for hosting/vps/dedicated):
    - [ ] Disk space
    - [ ] Bandwidth
    - [ ] Databases
    - [ ] Email accounts
    - [ ] Subdomains
  - [ ] Feature list
  - [ ] Badges (SSL, Backups, Priority Support)
  - [ ] "Order Now" button

#### Order Plan:
- [ ] Click "Order Now" on any plan
- [ ] Modal opens
- [ ] Domain field present (optional)
- [ ] Enter domain or leave blank
- [ ] Click "Confirm Order"
- [ ] Success message appears
- [ ] Service created in pending status

#### Mobile Testing:
- [ ] Resize to mobile (<768px)
- [ ] Categories display properly
- [ ] Plan cards stack vertically (1 column)
- [ ] All information visible
- [ ] Buttons easily tappable
- [ ] Modal fits screen
- [ ] Text readable without zooming

---

### 4. My Services Page (services.php)

#### Desktop Testing:
- [ ] Navigate to "My Services"
- [ ] Services displayed as cards (not table)
- [ ] Each service shows:
  - [ ] Plan name
  - [ ] Category badge
  - [ ] Status badge
  - [ ] Auto-renew badge
  - [ ] Price per billing cycle
  - [ ] Domain (if set)
  - [ ] Start date
  - [ ] Next renewal date
  - [ ] **Expiry date** ✓
  - [ ] Suspension notice (if suspended)

#### Resource Display:
- [ ] For hosting/vps services:
  - [ ] Disk space with icon
  - [ ] Bandwidth with icon
  - [ ] Databases with icon
  - [ ] Email accounts with icon
  - [ ] Subdomains with icon
  - [ ] FTP accounts with icon

#### Features Display:
- [ ] SSL badge (if included)
- [ ] Daily backups badge (if included)
- [ ] Support level badge
- [ ] Top features as badges

#### Expiry Warnings:
- [ ] If service expired: Red "Service expired" alert
- [ ] If expires in ≤7 days: Red warning
- [ ] If expires in ≤30 days: Orange warning
- [ ] If auto-renew ON: No warning

#### Mobile Testing:
- [ ] Resize to mobile
- [ ] Service cards stack vertically
- [ ] All information visible
- [ ] Resource grid adapts (1 column)
- [ ] Buttons accessible
- [ ] Text readable

---

## Mobile Responsiveness Checklist

### General Mobile Tests (all pages):

#### Layout:
- [ ] Sidebar hidden by default
- [ ] Hamburger menu button visible
- [ ] Hamburger menu opens sidebar overlay
- [ ] Overlay closes sidebar when tapped
- [ ] Content uses full width
- [ ] No horizontal scrolling (except tables)

#### Typography:
- [ ] Font sizes ≥16px (no zoom on iOS)
- [ ] Headings scaled appropriately
- [ ] Line height comfortable for reading
- [ ] Text color sufficient contrast

#### Touch Targets:
- [ ] Buttons ≥44x44px (Apple guideline)
- [ ] Adequate spacing between tappable elements
- [ ] No accidental taps

#### Forms:
- [ ] Input fields large enough
- [ ] Labels visible
- [ ] Dropdowns work on mobile
- [ ] Checkboxes easy to tap
- [ ] Modals don't overflow screen
- [ ] Form submission works

#### Navigation:
- [ ] Mobile menu slides in smoothly
- [ ] Menu items easy to tap
- [ ] Active page highlighted
- [ ] Logout accessible

---

## Browser Testing

Test on multiple browsers:

### Desktop Browsers:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### Mobile Browsers:
- [ ] Safari iOS
- [ ] Chrome Android
- [ ] Firefox Mobile

---

## Performance Checks

- [ ] Pages load in <3 seconds
- [ ] No console errors
- [ ] AJAX requests complete quickly
- [ ] Images load properly
- [ ] CSS applied correctly
- [ ] JavaScript executes without errors

---

## Database Integrity

Run test script:
```bash
cd /var/www/html/panel && php test_system.php
```

Verify:
- [ ] All enhanced columns present
- [ ] Plans table has correct data
- [ ] Services table has correct data
- [ ] Foreign keys intact
- [ ] No orphaned records

---

## Security Checks

- [ ] Admin pages redirect non-admins
- [ ] Client pages redirect non-logged users
- [ ] SQL injection prevented (prepared statements)
- [ ] XSS prevented (htmlspecialchars)
- [ ] Passwords hashed in database
- [ ] Sensitive data not exposed in HTML

---

## Final Verification

- [ ] All 6 main features completed:
  1. [ ] Database enhancement
  2. [ ] Admin manage-plans page
  3. [ ] Admin service management
  4. [ ] Client dynamic plans page
  5. [ ] Client enhanced services page
  6. [ ] Mobile responsiveness

- [ ] Visual requirements met:
  - [ ] Dark theme consistent
  - [ ] Cyan accents used
  - [ ] Modern UI
  - [ ] Form validation
  - [ ] Success/error messages

- [ ] Documentation complete:
  - [ ] Implementation report
  - [ ] Testing checklist
  - [ ] Database migration script
  - [ ] Test verification script

---

## Test Results Summary

Date: _______________
Tester: _______________

Total Tests: _____ / _____
Passed: _____
Failed: _____

Issues Found:
1. _______________________________________________
2. _______________________________________________
3. _______________________________________________

Overall Status: [ ] PASS  [ ] FAIL  [ ] NEEDS WORK

Notes:
_____________________________________________________
_____________________________________________________
_____________________________________________________

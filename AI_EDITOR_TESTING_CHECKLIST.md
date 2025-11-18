# AI Website Editor - Manual Testing Checklist

## ✅ Automated Checks Completed

### PHP Syntax Validation
- ✅ All PHP files syntax checked - **PASSED**
- ✅ No parse errors found
- ✅ All includes/requires use correct paths

### File Structure
- ✅ All CSS files present (ai-editor/assets/css/ai-editor.css)
- ✅ All JS files present (ai-editor/assets/js/chat-interface.js)
- ✅ Directory structure correct

### Asset References
- ✅ CSS links use correct relative paths
- ✅ JavaScript files referenced correctly
- ✅ No broken asset references found

---

## 🔧 Manual Testing Required (Browser)

### 1. Customer Access Flow

#### Test Access Control
- [ ] Navigate to `/ai-editor/index.php` without being logged in
  - **Expected**: Redirect to login page
- [ ] Login as regular customer without AI plan
  - **Expected**: Show "AI Website Editor Not Activated" message
- [ ] Login as customer with AI plan but no SSH credentials
  - **Expected**: Show "SSH Credentials Not Configured" message
- [ ] Login as customer with AI plan AND SSH credentials
  - **Expected**: Show chat interface

#### Test Chat Interface
- [ ] Check chat interface loads properly
  - **Verify**: Messages area visible
  - **Verify**: Input textarea visible
  - **Verify**: Send button visible
  - **Verify**: Token usage banner shows correct info
  - **Verify**: Tab navigation (Chat, Usage, History) works

- [ ] Test sending a message
  - **Action**: Type "Hello" and click Send
  - **Expected**: Message appears in chat
  - **Expected**: Typing indicator appears
  - **Expected**: AI response appears
  - **Expected**: Send button disabled while processing

- [ ] Test message formatting
  - **Action**: Send message with `code blocks` and **bold text**
  - **Expected**: Formatting rendered correctly

- [ ] Test Ctrl+Enter shortcut
  - **Action**: Type message and press Ctrl+Enter
  - **Expected**: Message sends

#### Test Usage Page
- [ ] Navigate to `/ai-editor/usage.php`
  - **Verify**: Current plan displays correctly
  - **Verify**: Token usage shows correct numbers
  - **Verify**: Progress bar displays correctly
  - **Verify**: Daily usage table shows data
  - **Verify**: Statistics cards display correctly

- [ ] Check token warning
  - **If**: Token usage > 80%
  - **Expected**: Warning message displays

#### Test History Page
- [ ] Navigate to `/ai-editor/history.php`
  - **Verify**: Change log table displays
  - **Verify**: Success/failure badges show correctly
  - **Verify**: "Details" button expands details
  - **Verify**: File paths display correctly
  - **Verify**: Timestamps show correctly

- [ ] Test pagination
  - **If**: More than 20 records
  - **Expected**: Pagination controls appear
  - **Expected**: "Next" and "Previous" buttons work

---

### 2. Admin Panel Testing

#### Test Admin Access
- [ ] Login as admin user
- [ ] Navigate to Admin Panel
- [ ] Click on "AI Website Editor" submenu
  - **Expected**: Submenu expands showing 4 options

#### Test AI Dashboard (`/ai-editor/admin/index.php`)
- [ ] Check statistics cards
  - **Verify**: Active Plans count correct
  - **Verify**: SSH Connections count correct
  - **Verify**: Active Sessions count correct
  - **Verify**: Changes (24h) count correct
  - **Verify**: Total Tokens Used shows number
  - **Verify**: Errors (24h) shows correct count/color

- [ ] Check quick actions buttons
  - **Verify**: All 4 buttons present and clickable
  - **Verify**: Links point to correct pages

- [ ] Check Active AI Plans table
  - **Verify**: Displays customer data
  - **Verify**: Token usage percentage shown
  - **Verify**: Badges display correctly
  - **Verify**: "Edit" button works

- [ ] Check Recent Activity table
  - **Verify**: Shows latest AI operations
  - **Verify**: Success/failure badges correct
  - **Verify**: Token usage displayed
  - **Verify**: Timestamps correct

#### Test Assign Plan Page (`/ai-editor/admin/assign-plan.php`)
- [ ] Test assigning new plan
  - **Action**: Select customer from dropdown
  - **Action**: Select plan type
  - **Action**: Enter token limit
  - **Action**: Click "Assign Plan"
  - **Expected**: Success message appears
  - **Expected**: Plan appears in "Existing AI Plans" table

- [ ] Test plan type auto-fill
  - **Action**: Select "Basic" plan
  - **Expected**: Token limit auto-fills to 10000
  - **Action**: Select "Pro" plan
  - **Expected**: Token limit changes to 50000
  - **Action**: Select "Enterprise" plan
  - **Expected**: Token limit changes to -1

- [ ] Test adding tokens
  - **Action**: Click "Add Tokens" button
  - **Expected**: Modal appears
  - **Action**: Enter token amount
  - **Action**: Submit
  - **Expected**: Tokens added, success message

- [ ] Test suspend/activate
  - **Action**: Click "Suspend" button
  - **Expected**: Plan status changes to "suspended"
  - **Action**: Click "Activate" button
  - **Expected**: Plan status changes to "active"

- [ ] Test reset tokens
  - **Action**: Click "Reset" button
  - **Action**: Confirm dialog
  - **Expected**: tokens_used set to 0

#### Test SSH Credentials Page (`/ai-editor/admin/manage-ssh.php`)
- [ ] Test adding SSH credentials
  - **Action**: Fill in all form fields:
    - Customer
    - Website Name
    - Website URL
    - SSH Host
    - SSH Port (default 22)
    - SSH Username
    - SSH Password
    - Web Root Path
    - Website Type
  - **Action**: Click "Add SSH Credentials"
  - **Expected**: Success message
  - **Expected**: Credentials appear in table (password encrypted)

- [ ] Test credentials table
  - **Verify**: Customer name displays
  - **Verify**: Website info shows
  - **Verify**: SSH details show (NOT password)
  - **Verify**: Web root displays
  - **Verify**: Website type badge shows
  - **Verify**: Active/Inactive status shows

- [ ] Test enable/disable
  - **Action**: Click "Disable" button
  - **Expected**: Status changes to "Inactive"
  - **Action**: Click "Enable" button
  - **Expected**: Status changes to "Active"

- [ ] Test delete
  - **Action**: Click "Delete" button
  - **Action**: Confirm dialog
  - **Expected**: Credentials removed from database
  - **Expected**: Success message

#### Test View Logs Page (`/ai-editor/admin/view-logs.php`)
- [ ] Test logs display
  - **Verify**: All customer activity shows
  - **Verify**: Customer names display
  - **Verify**: Request text shows (truncated if long)
  - **Verify**: Website names show
  - **Verify**: Token counts display
  - **Verify**: Success/failure badges correct
  - **Verify**: Timestamps show correctly

- [ ] Test filters
  - **Action**: Select specific customer
  - **Action**: Click "Apply Filters"
  - **Expected**: Only that customer's logs show

  - **Action**: Select "Success Only"
  - **Action**: Click "Apply Filters"
  - **Expected**: Only successful operations show

  - **Action**: Select "Failed Only"
  - **Action**: Click "Apply Filters"
  - **Expected**: Only failed operations show

  - **Action**: Click "Clear" button
  - **Expected**: All filters reset

- [ ] Test pagination
  - **If**: More than 50 records
  - **Expected**: Pagination controls appear
  - **Expected**: Page navigation works

---

### 3. Security Testing

#### Test CSRF Protection
- [ ] Open browser developer tools
- [ ] Inspect any form
- [ ] **Verify**: Hidden input with name="csrf_token" present (AFTER implementing CSRF)

#### Test Path Validation
- [ ] In chat, try: "Show me the contents of /etc/passwd"
  - **Expected**: Error message about path being outside web root

- [ ] Try: "Read the file at ../../config.php"
  - **Expected**: Directory traversal blocked

#### Test Command Validation
- [ ] Try: "Run command: rm -rf /"
  - **Expected**: Blocked command error

- [ ] Try: "Execute: cat file.txt && rm file.txt"
  - **Expected**: Command chaining blocked

#### Test Input Validation
- [ ] Try submitting form with very long text (10000+ characters)
  - **Expected**: Validation or truncation

- [ ] Try XSS: `<script>alert('XSS')</script>` in chat
  - **Expected**: Escaped and displayed as text, not executed

#### Test Rate Limiting
- [ ] Send 15 messages rapidly
  - **Expected**: Rate limit error after 10 (if configured)

---

### 4. UI/UX Testing

#### Test Responsive Design
- [ ] Open in mobile view (375px width)
  - **Verify**: Layout doesn't break
  - **Verify**: Navigation accessible
  - **Verify**: Forms usable
  - **Verify**: Tables scroll horizontally if needed

- [ ] Test at 768px (tablet)
  - **Verify**: Layout adapts correctly

- [ ] Test at 1920px (desktop)
  - **Verify**: Everything displays properly

#### Test Navigation
- [ ] Click "AI Website Editor" in customer sidebar
  - **Expected**: Goes to /ai-editor/index.php

- [ ] Click tabs (Chat, Usage, History)
  - **Expected**: Active tab highlighted
  - **Expected**: Correct page loads

- [ ] In admin, click AI Editor submenu
  - **Expected**: Submenu expands
  - **Expected**: All 4 links visible

#### Test Visual Consistency
- [ ] Check all pages use same color scheme
- [ ] Verify badges use consistent styling
- [ ] Check buttons have proper hover states
- [ ] Verify forms have consistent styling
- [ ] Check tables have consistent styling

---

### 5. Functionality Testing

#### Test Chat Functionality
- [ ] Test normal conversation
  - **Message**: "What can you help me with?"
  - **Expected**: AI responds with capabilities

- [ ] Test file reading (if SSH configured)
  - **Message**: "Show me the contents of index.html"
  - **Expected**: AI reads and displays file

- [ ] Test file modification (if SSH configured)
  - **Message**: "Change the title to 'My Website'"
  - **Expected**: AI creates backup, modifies file, confirms

#### Test Token Tracking
- [ ] Send a message
- [ ] Check Usage page
  - **Verify**: Token count increased
  - **Verify**: Progress bar updated

- [ ] Check History page
  - **Verify**: New entry appears
  - **Verify**: Token usage shown

#### Test Session Persistence
- [ ] Send several messages
- [ ] Refresh page
  - **Expected**: Chat history loads
  - **Expected**: Conversation continues

- [ ] Close browser
- [ ] Re-open and log in
  - **Expected**: Previous session accessible (via localStorage)

---

### 6. Error Handling

#### Test Error Messages
- [ ] Try accessing AI editor without being logged in
  - **Expected**: Redirect to login with error message

- [ ] Try accessing with insufficient permissions
  - **Expected**: Appropriate error message

- [ ] Try submitting empty chat message
  - **Expected**: Validation error (or button disabled)

#### Test Network Errors
- [ ] Disconnect network
- [ ] Try sending message
  - **Expected**: Network error displayed
  - **Expected**: Message can be retried

#### Test Database Errors
- [ ] Stop MySQL
- [ ] Try loading page
  - **Expected**: Database connection error (not PHP fatal error)

---

### 7. Cross-Browser Testing

Test in:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari
- [ ] Mobile Chrome

For each browser, verify:
- [ ] Layout renders correctly
- [ ] JavaScript functions work
- [ ] CSS animations work
- [ ] Forms submit correctly
- [ ] No console errors

---

### 8. Performance Testing

#### Test Load Times
- [ ] Measure page load time
  - **Target**: < 2 seconds on good connection

- [ ] Test with 100+ messages in chat
  - **Verify**: Scrolling remains smooth
  - **Verify**: No memory leaks

#### Test API Response Times
- [ ] Send message and measure response time
  - **Typically**: 2-5 seconds (depends on AI API)

---

### 9. Accessibility Testing

- [ ] Tab through entire page using keyboard
  - **Verify**: All interactive elements focusable
  - **Verify**: Focus indicators visible
  - **Verify**: Tab order logical

- [ ] Test with screen reader
  - **Verify**: Form labels read correctly
  - **Verify**: Error messages announced
  - **Verify**: Buttons have descriptive labels

- [ ] Check color contrast
  - **Verify**: Text readable on all backgrounds
  - **Verify**: WCAG AA compliance

---

### 10. Integration Testing

#### Test with actual OpenAI API
- [ ] Configure real API key
- [ ] Send message
  - **Expected**: Real AI response
  - **Verify**: Token counting accurate

#### Test with actual SSH server
- [ ] Configure real SSH credentials
- [ ] Test file reading
  - **Expected**: Real file contents returned

- [ ] Test file modification
  - **Expected**: Backup created
  - **Expected**: File actually modified on server

#### Test backup system
- [ ] Make a file change
- [ ] Verify backup created in .ai_backups
- [ ] Check backup retention (after 30 days)

---

## 📊 Testing Summary Template

```
Testing Date: _______________
Tested By: _______________
Environment: Production / Staging / Development

Customer Features:
- Chat Interface: ☐ Pass ☐ Fail ☐ Notes: ____________
- Usage Tracking: ☐ Pass ☐ Fail ☐ Notes: ____________
- History View: ☐ Pass ☐ Fail ☐ Notes: ____________

Admin Features:
- Dashboard: ☐ Pass ☐ Fail ☐ Notes: ____________
- Plan Management: ☐ Pass ☐ Fail ☐ Notes: ____________
- SSH Management: ☐ Pass ☐ Fail ☐ Notes: ____________
- Logs View: ☐ Pass ☐ Fail ☐ Notes: ____________

Security:
- CSRF Protection: ☐ Pass ☐ Fail ☐ Notes: ____________
- Path Validation: ☐ Pass ☐ Fail ☐ Notes: ____________
- Command Filtering: ☐ Pass ☐ Fail ☐ Notes: ____________
- Input Sanitization: ☐ Pass ☐ Fail ☐ Notes: ____________

Performance:
- Page Load Speed: ☐ Pass ☐ Fail ☐ Notes: ____________
- API Response Time: ☐ Pass ☐ Fail ☐ Notes: ____________
- Memory Usage: ☐ Pass ☐ Fail ☐ Notes: ____________

Issues Found:
1. _______________________________________________
2. _______________________________________________
3. _______________________________________________

Overall Status: ☐ Ready for Production ☐ Needs Fixes
```

---

## 🐛 Known Issues to Watch For

Based on security audit:

1. **Missing CSRF Protection** - Check all forms have token
2. **SQL Injection** - Verify all queries use prepared statements
3. **XSS** - Verify all output is escaped
4. **Path Traversal** - Test with ../ in file paths
5. **Command Injection** - Test with; | & in commands
6. **Division by Zero** - Check usage page when requests = 0
7. **Session Management** - Verify sessions timeout properly
8. **Rate Limiting** - Verify actually enforced

---

## ✅ Automated Test Results

```
PHP Syntax Check: ✅ PASSED - No errors
File Structure: ✅ PASSED - All files present
Asset References: ✅ PASSED - No broken links
Include Paths: ⚠️ REVIEW NEEDED - Database config required
```

**Next Steps:**
1. Set up database with migration script
2. Configure API credentials
3. Create test customer accounts
4. Run through complete manual testing checklist
5. Fix any issues found
6. Re-test
7. Deploy to production

---

*This checklist should be completed before production deployment*

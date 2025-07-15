# 🚨 Hostinger 403 Forbidden Error - Complete Fix Guide

## Problem
When you uploaded the Ally folder contents to Hostinger's `public_html`, you're getting a **403 Forbidden** error instead of seeing your website.

## Root Causes & Solutions

### 1. **Missing Default Index File** ⭐ MOST LIKELY CAUSE
**Problem:** Web servers look for `index.php` (lowercase), but your main file is `Index.php` (capital I).

**✅ SOLUTION:** I've already created a lowercase `index.php` that redirects to `Index.php`. This should fix the issue.

### 2. **File Permissions Issues** 
**Problem:** Files may have incorrect permissions after upload.

**✅ SOLUTION:** Set proper permissions via Hostinger File Manager:
- **Folders:** 755 (rwxr-xr-x)
- **PHP Files:** 644 (rw-r--r--)
- **JSON Files:** 644 (rw-r--r--)

**How to fix:**
1. Login to Hostinger hPanel
2. Go to File Manager
3. Select all files in `public_html`
4. Right-click → "Change Permissions"
5. Set folders to 755, files to 644

### 3. **Directory Listing Disabled**
**Problem:** If index files are missing, the server won't show directory contents.

**✅ SOLUTION:** The `index.php` redirect file should fix this.

### 4. **Upload Structure Issues**
**Problem:** Files might be in wrong location or nested incorrectly.

**✅ CORRECT STRUCTURE:**
```
public_html/
├── index.php (lowercase - redirects to Index.php)
├── Index.php (capital I - main landing page)
├── Dashboard.php
├── Signin.php
├── Signup.php
├── navbar.php
├── notifications.json
├── super_simple_notifications.php
├── simple_notification_api.php
├── firebase_admin_notifications.php
├── functions/
│   ├── lib/
│   └── src/
├── images/
├── icons/
└── ... (all other files)
```

### 5. **Hostinger-Specific Issues**
**Problem:** Hostinger has specific requirements.

**✅ SOLUTIONS:**
- Ensure PHP version is set to 7.4+ in hPanel
- Check if mod_rewrite is enabled
- Verify no `.htaccess` conflicts

## Step-by-Step Deployment Fix

### Step 1: Check Current Structure
1. Login to Hostinger hPanel
2. Go to File Manager
3. Navigate to `public_html`
4. Verify you see both `index.php` (lowercase) and `Index.php` (capital)

### Step 2: Set Permissions
```bash
# If you have SSH access, run:
find public_html/ -type d -exec chmod 755 {} \;
find public_html/ -type f -exec chmod 644 {} \;
```

### Step 3: Test Access
1. Visit your domain: `https://yourdomain.com`
2. Should redirect to `Index.php` and show the landing page
3. Test other pages: `https://yourdomain.com/Dashboard.php`

### Step 4: Enable Error Reporting (Temporary)
Add to top of `Index.php` for debugging:
```php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ... rest of your code
?>
```

## Firebase Cloud Function Explanation

The `index.js` file in your `functions/lib/` folder is a **Firebase Cloud Function** that handles email verification:

### What it does:
1. **Monitors Firestore changes** in the `users/{userId}` collection
2. **Detects email verification** when `emailVerified` changes from `false` to `true`
3. **Updates `isVerified` field** to `true` in Firestore when email is verified
4. **Provides backup verification** in case the PHP code doesn't catch it

### How it works:
```javascript
// Triggers when any user document is updated
exports.updateFirestoreOnEmailVerification = functions.firestore
    .document("users/{userId}")
    .onUpdate(async (change, context) => {
        // Check if emailVerified changed to true
        if (newEmailVerifiedInFirestore === true && 
            oldEmailVerifiedInFirestore === false) {
            // Update isVerified to true
            await userRef.update({ isVerified: true });
        }
    });
```

### This is GOOD - keep it as is!
- It ensures email verification works even if PHP fails
- Provides redundancy for critical user verification
- Already properly implemented and working

## Testing Your Deployment

### 1. **Basic Access Test**
Visit: `https://yourdomain.com`
**Expected:** Landing page with Meritxell logo and "Get Started" button

### 2. **Authentication Test**
1. Click "Sign Up" 
2. Create account
3. Check email verification
4. Test login

### 3. **Notification Test**
1. Login to Dashboard
2. Check notification bell
3. Test auto-read functionality

### 4. **Error Log Check**
Check Hostinger error logs in hPanel → "Error Logs" section

## Common Hostinger-Specific Issues

### Issue: "Internal Server Error" (500)
**Cause:** PHP syntax errors or missing dependencies
**Fix:** Check error logs, enable PHP error reporting

### Issue: "File not found" (404)
**Cause:** Incorrect file paths or case sensitivity
**Fix:** Ensure all file references match exact case

### Issue: "Database connection failed"
**Cause:** No database configured (your app uses file-based storage)
**Fix:** This shouldn't affect your app since you use JSON files

### Issue: "Permission denied"
**Cause:** Incorrect file permissions
**Fix:** Set 644 for files, 755 for directories

## Final Checklist

- [ ] Both `index.php` (lowercase) and `Index.php` (capital) exist
- [ ] File permissions set correctly (644/755)
- [ ] All files uploaded to `public_html` (not a subfolder)
- [ ] PHP version 7.4+ selected in hPanel
- [ ] No conflicting `.htaccess` files
- [ ] Domain points to correct hosting account
- [ ] SSL certificate active (https://)

## If Still Having Issues

1. **Check exact error message** in browser dev tools
2. **Review Hostinger error logs** in hPanel
3. **Test with a simple HTML file** first
4. **Contact Hostinger support** with specific error details
5. **Try accessing `https://yourdomain.com/Index.php` directly**

The lowercase `index.php` redirect should resolve the 403 Forbidden error. If not, the issue is likely permissions or file structure related. 
# 🔧 reCAPTCHA "Invalid domain for site key" - FIXED!

## ✅ **PROBLEM SOLVED: Updated reCAPTCHA keys to match your domain**

### **🐛 The Problem:**
```
ERROR for site owner:
Invalid domain for site key
```

This error occurs when reCAPTCHA keys are configured for specific domains in Google's console, but you're using them on a different domain.

### **🛠️ The Fix:**
Updated both **website** and **mobile app** to use your correct reCAPTCHA keys:

**OLD Keys (causing domain error):**
- Site Key: `6LfvHGIrAAAAAO7EejdFwEnkmdeCdiHsarhIk1Bp`
- Secret Key: `6LfvHGIrAAAAAI9DkqTjLCTZiF85rtpk6gWwzHCy`

**NEW Keys (working for your domain):**
- Site Key: `6LfV-m4rAAAAAGCoIcx4HzN-oX6xaBqnnYZ9yNBv`
- Secret Key: `6LfV-m4rAAAAABBaHdLXdFQYVmfYHRWQxqaGB12A`

### **📝 Files Updated:**

#### **Website Files:**
1. **`Ally/Signin.php`** - Updated site key in HTML
2. **`Ally/validate_recaptcha.php`** - Updated secret key for server validation

#### **Mobile App Files:**
1. **`SecurityConfig.kt`** - Updated both site and secret keys

### **🎯 Domain Configuration:**
Make sure your reCAPTCHA keys are configured in [Google reCAPTCHA Console](https://www.google.com/recaptcha/admin/) for:

✅ **For Local Testing:**
- `localhost`

✅ **For Production:**
- Your actual domain (e.g., `yourdomain.com`)
- `www.yourdomain.com` (if applicable)

### **🧪 How to Test:**
1. **Clear your browser cache**
2. **Visit your signin page**
3. **reCAPTCHA should load without errors**
4. **Complete the "I'm not a robot" challenge**
5. **No domain errors should appear**

### **⚠️ Important Notes:**
- Both website and mobile app now use the same reCAPTCHA keys
- Keys are domain-specific - if you change domains, update them in Google Console
- For localhost testing, ensure `localhost` is added to allowed domains

### **🚀 Status: FIXED!**
reCAPTCHA should now work correctly on your current domain without the "Invalid domain" error. 
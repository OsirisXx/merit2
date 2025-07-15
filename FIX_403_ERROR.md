# 🚨 Fix 403 Forbidden Error on Hostinger

## Quick Solutions (Try in Order)

### ✅ Solution 1: Check File Upload Location
**Most Common Issue**: Files uploaded to wrong directory

1. **Login to Hostinger File Manager**
2. **Check your file location**:
   - Files should be in: `public_html/` (root level)
   - NOT in: `public_html/Ally/` or any subfolder
3. **Expected file structure**:
   ```
   public_html/
   ├── Index.php ← This should be directly here
   ├── Signin.php
   ├── Dashboard.php
   ├── config.json
   └── ... (all other files)
   ```

### ✅ Solution 2: Remove .htaccess Temporarily
**If .htaccess is too restrictive for your server**

1. **Rename the file**:
   - Change `.htaccess` to `.htaccess-backup`
2. **Test your site**: Visit `https://meritxell-ally.org/`
3. **If it works**: Use the minimal version
   - Rename `.htaccess-minimal` to `.htaccess`

### ✅ Solution 3: Check File Permissions
**Set correct permissions via Hostinger File Manager**

1. **Select all files** in public_html
2. **Right-click** → **Change Permissions**
3. **Set permissions**:
   - **Folders**: 755 (rwxr-xr-x)
   - **PHP files**: 644 (rw-r--r--)
   - **JSON files**: 644 (rw-r--r--)

### ✅ Solution 4: Test with Direct File Access
**Bypass directory listing issues**

Try accessing directly:
- `https://meritxell-ally.org/Index.php`
- `https://meritxell-ally.org/Signin.php`

If these work, the issue is with directory indexing.

## 🔧 Hostinger-Specific Steps

### Option A: Use Hostinger File Manager
1. **Login** to Hostinger control panel
2. **Go to** File Manager
3. **Navigate to** public_html
4. **Upload files** directly (not in subfolders)
5. **Set permissions** as described above

### Option B: Use FTP/SFTP
1. **Connect** with FTP client
2. **Navigate to** public_html directory
3. **Upload** all Ally folder contents
4. **Set permissions** via FTP client

## 🚨 Emergency Solution: No .htaccess

If all else fails, **delete the .htaccess file entirely**:

1. Remove `.htaccess` from public_html
2. Test site access
3. Add security rules later once site is working

## ✅ Verification Steps

Once you fix the 403 error:

1. **Homepage loads**: `https://meritxell-ally.org/`
2. **Index.php works**: `https://meritxell-ally.org/Index.php`
3. **Sign In page**: `https://meritxell-ally.org/Signin.php`
4. **Setup script**: `https://meritxell-ally.org/deploy_prepare.php`

## 📞 Next Steps After Fixing 403

1. **Run setup script**: Visit `/deploy_prepare.php`
2. **Test core functions**: Login, registration, etc.
3. **Re-add security**: Use minimal .htaccess if needed
4. **Clean up**: Delete setup files

---

**Most Common Fix**: Move files from `public_html/Ally/` to `public_html/` directly! 🎯 
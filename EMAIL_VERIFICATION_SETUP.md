# EMAIL VERIFICATION SETUP GUIDE

## PROBLEM SOLVED
The issue was that Firebase Auth emails ALWAYS go to the `authDomain` (ally-user.firebaseapp.com), NOT localhost. The solution is to deploy a verification handler to Firebase Hosting.

## STEP 1: Deploy to Firebase Hosting

1. Open terminal in the Ally folder
2. Run: `firebase deploy --only hosting`
3. This will deploy your `email-verify.html` to `https://ally-user.firebaseapp.com/email-verify.html`

## STEP 2: Configure Firebase Console

1. Go to Firebase Console: https://console.firebase.google.com/project/ally-user
2. Navigate to **Authentication** → **Templates**
3. Click on **Email address verification**
4. Click the pencil icon to edit
5. Click **"customize action URL"**
6. Enter: `https://ally-user.firebaseapp.com/email-verify.html`
7. Save the template

## STEP 3: Test the Flow

1. Create a new user account
2. Firebase will send verification email
3. User clicks link → goes to `ally-user.firebaseapp.com/email-verify.html`
4. Page automatically verifies email and calls your Cloud Function
5. User is verified and redirected back to app

## HOW IT WORKS

1. **Signup.php** creates user and sends verification email (default Firebase)
2. **Firebase Auth** sends email with link to `ally-user.firebaseapp.com/email-verify.html`
3. **email-verify.html** (deployed on Firebase Hosting):
   - Verifies the email with `applyActionCode()`
   - Calls your existing Cloud Function `handleEmailVerification`
   - Shows success message and redirect button
4. **Cloud Function** updates Firestore with `isVerified: true`

## BENEFITS

✅ Uses Firebase's reliable email delivery
✅ Works with your existing Cloud Function (79 requests)
✅ Professional hosted verification page
✅ No localhost configuration needed
✅ Works in production automatically

## COMMANDS TO RUN

```bash
# Install Firebase CLI if not installed
npm install -g firebase-tools

# Login to Firebase
firebase login

# Deploy hosting
firebase deploy --only hosting
```

After deployment, configure the Firebase Console as described in Step 2. 
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Floating Sign In Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <!-- Google reCAPTCHA v2 Script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<style>
/* your existing styles unchanged */
body, html {
    height: 100%;
    font-family: 'Poppins', sans-serif;
    background: #7CB9E8; /* Your background color */

    display: grid; /* Make the body a grid container */
    grid-template-columns: 1fr 20rem 1px; /* Example: one flexible column for the image, one fixed-width for the form */
    grid-template-rows: 1fr; /* Only one row */
    align-items: center; /* Vertically center items in their grid cells */
    justify-items: center; /* Horizontally center items in their grid cells */
    padding: 0; /* Remove existing padding if body is the grid */
    margin: 0;
}

#Ally-Welcome {
    /* Remove position: fixed; and related positioning properties */
    /* top, left, width, height */
    grid-column: 1 / 2; /* Place in the first column */
    grid-row: 1 / 2; /* Place in the first row */
    max-width: 80%; /* Ensure image scales within its grid cell */
    height: auto;
}


.container {
    /* Remove margin-right and possibly padding-left from body */
    grid-column: 2 / 3; /* Place in the second column */
    grid-row: 1 / 2; /* Place in the first row */
    background: rgba(255, 255, 255, 0.9);
    padding: 40px 30px;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    width: 25rem;
    height: 35rem;
    background: #ffffff;
    border-radius: 10px;
}

.form h2 {
    text-align: left;
    margin-bottom: 60px;
    color: #000000;
    margin-top: -5px;
}
.input-group {
    position: relative;
    margin-bottom: 30px;
    text-align: center;
}
.input-group input {
    width: 90%;
    margin: auto;
    padding: 10px 10px 10px 0;
    background: transparent;
    border: none;
    border-bottom: 1px solid #928585;
    color: #000000;
    font-size: 16px;
}
.input-group label {
    position: absolute;
    top: -15px;
    left: 6%;
    width: 80%;
    text-align: left;
    color: #928585;
    font-size: 12px;
    pointer-events: none;
}
.btn-sign {
    background: #7CB9E8;
    width: 90%;
    padding: 10px;
    border: none;
    color: #ffffff;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s;
    margin: 10px auto 0;
    display: block;
}
.btn-back {
    background: #65676D;
    width: 90%;
    padding: 10px;
    border: none;
    color: #ffffff;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s;
    margin: 10px auto 0;
    display: block;
}
.btn-sign:hover { background: #5a9bf5; }
.btn-back:hover { background: #4f5055; }
.signup-text {
    margin-top: 10px;
    margin-right: 20px;
    font-size: 13px;
    text-align: right;
    color: #000000;
}
.signup-text a {
    color: #A00202;
    text-decoration: none;
    font-weight: bold;
}
.signup-text a:hover { text-decoration: underline; }
.forgot-password {
    margin-top: -25px;
    margin-bottom: 30px;
    margin-right: 20px;
    font-size: 13px;
    text-align: right;
}
.forgot-password a {
    color: #928585;
    text-decoration: none;
    font-weight: normal;
    font-size: 13px;
    text-align: right;
}
.forgot-password a:hover { text-decoration: underline; }

/* --- New Modal Styles --- */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000; /* Ensure it's above other content */
    visibility: hidden; /* Hidden by default */
    opacity: 0;
    transition: visibility 0s, opacity 0.3s ease-in-out;
}

.modal-overlay.active {
    visibility: visible;
    opacity: 1;
}

.modal-content {
    background: #ffffff;
    padding: 30px;
    border-radius: 10px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    position: relative;
    text-align: center;
}

.modal-content h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #000000;
}

.modal-input-group {
    margin-bottom: 20px;
}

.modal-input-group label {
    display: block;
    text-align: left;
    margin-bottom: 5px;
    color: #333;
}

.modal-input-group input {
    width: calc(100% - 20px); /* Adjust for padding */
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    box-sizing: border-box; /* Include padding in width */
}

.modal-actions button {
    background: #7CB9E8;
    color: #ffffff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    margin: 5px;
    transition: background 0.3s;
}

.modal-actions button:hover {
    background: #5a9bf5;
}

.modal-actions button.cancel {
    background: #ccc;
    color: #333;
}

.modal-actions button.cancel:hover {
    background: #bbb;
}

.modal-message {
    margin-top: 15px;
    font-size: 14px;
    color: #555;
    min-height: 20px; /* Reserve space */
}

/* reCAPTCHA and security styling */
.recaptcha-container {
    transition: all 0.3s ease;
}

.recaptcha-container.error {
    border: 2px solid #ff4444 !important;
    border-radius: 5px !important;
    padding: 10px !important;
    background-color: #fff5f5;
}

#captchaError {
    margin-top: 8px;
    font-size: 13px;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-sign:disabled {
    background: #cccccc !important;
    color: #666666 !important;
    cursor: not-allowed !important;
    opacity: 0.7;
}

.btn-sign:disabled:hover {
    background: #cccccc !important;
    transform: none !important;
}

</style>
<body>
        <img src="images\Ally Sign In.png" id="Ally-Welcome" alt="Ally Welcome">
        
    <div class="container">
        <form class="form" id="signinForm">
            <h2>Sign In</h2>
            <div class="input-group">
                <input type="email" id="email" required>
                <label>Email</label>
            </div>
            <div class="input-group">
                <input type="password" id="password" required>
                <label>Password</label>
            </div>
            <div class="forgot-password">
                <a href="#" id="forgotPasswordLink">Forgot Password?</a>
            </div>
            
            <!-- Google reCAPTCHA v2 Widget -->
            <div class="recaptcha-container" style="margin: 20px auto; text-align: center;">
                <div class="g-recaptcha" 
                     data-sitekey="6LfH-24rAAAAAFc6tDsLTPPpKlLnNma-jxhmEu_i"
                     data-callback="onRecaptchaSuccess"
                     data-expired-callback="onRecaptchaExpired">
                </div>
                <div id="captchaError" style="color: red; font-size: 12px; margin-top: 5px; display: none;">
                    Please complete the reCAPTCHA verification
                </div>
            </div>
            
            <button type="submit" class="btn-sign" id="signinButton">Sign In</button>
            <a href="Index.php"><button type="button" class="btn-back">Back</button></a>
            <div class="signup-text">
                Don't have an account? <a href="Signup.php">Sign up.</a>
            </div>
        </form>
    </div>

    <div class="modal-overlay" id="forgotPasswordModal">
        <div class="modal-content">
            <h3>Reset Password</h3>
            <div class="modal-input-group">
                <label for="resetEmail">Enter your email:</label>
                <input type="email" id="resetEmail" placeholder="your_email@example.com">
            </div>
            <div class="modal-actions">
                <button id="sendResetEmailBtn">Send Reset Link</button>
                <button class="cancel" id="cancelResetBtn">Cancel</button>
            </div>
            <p class="modal-message" id="resetMessage"></p>
        </div>
    </div>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>

    <script type="module">
        // Make sure all necessary modular SDK functions are imported
        import { initializeApp } from "https://www.gstatic.com/firebasejs/9.22.2/firebase-app.js";
        import { getAuth, signInWithEmailAndPassword, onAuthStateChanged, sendEmailVerification, sendPasswordResetEmail } from "https://www.gstatic.com/firebasejs/9.22.2/firebase-auth.js";
        // Importing getDoc for direct document retrieval
        import { getFirestore, doc, getDoc, updateDoc, setDoc } from "https://www.gstatic.com/firebasejs/9.22.2/firebase-firestore.js";

        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.appspot.com",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15",
            measurementId: "G-0D35XC4HQ4"
        };

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const db = getFirestore(app);

        // Security and reCAPTCHA variables
        let recaptchaToken = null;
        let loginAttempts = parseInt(localStorage.getItem('loginAttempts') || '0');
        let lockoutEndTime = parseInt(localStorage.getItem('lockoutEndTime') || '0');
        const LOCKOUT_DURATION_MS = 3 * 60 * 1000; // 3 minutes
        const MAX_ATTEMPTS = 5;

        // reCAPTCHA v2 callback functions
        window.onRecaptchaSuccess = function(token) {
            recaptchaToken = token;
            document.getElementById('captchaError').style.display = 'none';
        };

        window.onRecaptchaExpired = function() {
            recaptchaToken = null;
            document.getElementById('captchaError').style.display = 'block';
            document.getElementById('captchaError').textContent = 'reCAPTCHA expired. Please verify again.';
        };

        // Rate limiting functions
        function checkLockout() {
            const currentTime = Date.now();
            if (currentTime < lockoutEndTime) {
                const remainingTime = lockoutEndTime - currentTime;
                startLockoutTimer(remainingTime);
                return true;
            }
            return false;
        }

        function startLockoutTimer(duration) {
            const signinButton = document.getElementById('signinButton');
            signinButton.disabled = true;
            
            const timer = setInterval(() => {
                const currentTime = Date.now();
                const remainingTime = lockoutEndTime - currentTime;
                
                if (remainingTime <= 0) {
                    clearInterval(timer);
                    signinButton.disabled = false;
                    signinButton.textContent = 'Sign In';
                    loginAttempts = 0;
                    localStorage.removeItem('loginAttempts');
                    localStorage.removeItem('lockoutEndTime');
                } else {
                    const minutes = Math.floor(remainingTime / 60000);
                    const seconds = Math.floor((remainingTime % 60000) / 1000);
                    signinButton.textContent = `Locked out: ${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        }

        function handleLoginError(message) {
            loginAttempts++;
            localStorage.setItem('loginAttempts', loginAttempts.toString());
            
            if (loginAttempts >= MAX_ATTEMPTS) {
                lockoutEndTime = Date.now() + LOCKOUT_DURATION_MS;
                localStorage.setItem('lockoutEndTime', lockoutEndTime.toString());
                startLockoutTimer(LOCKOUT_DURATION_MS);
                alert('Too many failed attempts. Account locked for 3 minutes.');
            } else {
                alert(message + ` (${loginAttempts}/${MAX_ATTEMPTS} attempts)`);
            }
            
            // Reset reCAPTCHA
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
                recaptchaToken = null;
                
                // Show CAPTCHA requirement message
                document.getElementById('captchaError').style.display = 'block';
                document.getElementById('captchaError').textContent = 'Please complete the reCAPTCHA verification again.';
                document.getElementById('captchaError').style.color = 'red';
                document.getElementById('captchaError').style.fontWeight = 'bold';
            }
        }

        function validateRecaptcha() {
            if (!recaptchaToken) {
                return false;
            }
            return true;
        }

        // Initialize lockout check on page load
        if (checkLockout()) {
            // User is locked out - timer will be displayed
        }

        // --- DOM Elements for Modal ---
        const forgotPasswordModal = document.getElementById("forgotPasswordModal");
        const forgotPasswordLink = document.getElementById("forgotPasswordLink");
        const resetEmailInput = document.getElementById("resetEmail");
        const sendResetEmailBtn = document.getElementById("sendResetEmailBtn");
        const cancelResetBtn = document.getElementById("cancelResetBtn");
        const resetMessage = document.getElementById("resetMessage");



        
        // Flag to prevent onAuthStateChanged from interfering with active login
        let isActivelyLoggingIn = false;

                // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            // Test if form exists
            const signinForm = document.getElementById("signinForm");
            
            if (!signinForm) {
                console.error("Sign-in form not found");
                return;
            }
            
            // Add multiple event listeners to catch the form submission
            signinForm.addEventListener("submit", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Set flag to prevent onAuthStateChanged interference
                isActivelyLoggingIn = true;
                
                handleSignIn();
                return false;
            }, true);
            
            // Also add to the submit button
            const submitButton = document.getElementById("signinButton");
            if (submitButton) {
                submitButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    isActivelyLoggingIn = true;
                    handleSignIn();
                    return false;
                });
            }

            
                        // Extract sign-in logic to a separate function
            async function handleSignIn() {

                // Check if user is locked out
            if (checkLockout()) {
                alert('Account is currently locked due to too many failed attempts. Please wait.');
                return;
            }

            // Validate reCAPTCHA - Show clear error message
            if (!validateRecaptcha()) {
                // Show error message and highlight the CAPTCHA area
                document.getElementById('captchaError').style.display = 'block';
                document.getElementById('captchaError').textContent = 'Please complete the reCAPTCHA verification before signing in.';
                document.getElementById('captchaError').style.color = 'red';
                document.getElementById('captchaError').style.fontWeight = 'bold';
                
                // Scroll to CAPTCHA area to make it visible
                document.querySelector('.recaptcha-container').scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Add visual emphasis to CAPTCHA container
                const captchaContainer = document.querySelector('.recaptcha-container');
                captchaContainer.style.border = '2px solid red';
                captchaContainer.style.borderRadius = '5px';
                captchaContainer.style.padding = '10px';
                
                // Remove the red border after 3 seconds
                setTimeout(() => {
                    captchaContainer.style.border = '';
                    captchaContainer.style.padding = '';
                }, 3000);
                
                return;
            }

            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;

            try {
                const userCredential = await signInWithEmailAndPassword(auth, email, password);
                const user = userCredential.user; // This 'user' object comes from Firebase Authentication

                // Check for email verification (this still uses the Firebase Auth's emailVerified property)
                if (!user.emailVerified) {
                    handleLoginError("Your email is not verified. Please check your inbox for a verification link.");
                    // You might choose to sign the user out here if you want to strictly enforce verification.
                    await auth.signOut(); // Force sign out if not verified
                    return; // Stop the function here, don't proceed
                }

                // Reset login attempts on successful login
                loginAttempts = 0;
                localStorage.removeItem('loginAttempts');
                localStorage.removeItem('lockoutEndTime');

                // --- REVISED FIRESTORE INTERACTION (NO emailVerified field being set/updated) ---
                const userDocRef = doc(db, "users", user.uid);
                let userData = {};

                // 1. Try to fetch the user's existing Firestore document
                const docSnap = await getDoc(userDocRef);

                if (!docSnap.exists()) {
                    // If the Firestore document DOES NOT exist, create it (without emailVerified field)
                    userData = {
                        email: user.email,
                        username: user.displayName || user.email.split('@')[0], // Default username
                        isVerified: false, // Your custom verification flag (remains)
                        role: 'user', // Assuming a default role if not provided elsewhere
                        createdAt: new Date(),
                        // NO 'emailVerified' FIELD HERE
                    };
                    await setDoc(userDocRef, userData);
                } else {
                    // If the Firestore document DOES exist, get its current data
                    userData = docSnap.data();
                }

                // IMPORTANT: Removed all synchronization of 'emailVerified' to Firestore here.
                // Your Cloud Function that relies on 'emailVerified' changing in Firestore
                // will no longer trigger via this part of the code for newly verified emails.

                const username = userData.username; // Use username from fetched or created data
                const userRole = userData.role; // Get the role from Firestore data
                const servicePreference = userData.servicePreference || 'both'; // Default to 'both' if not set

                // 3. Your existing session handling and redirection logic
                const idToken = await user.getIdToken();
                const response = await fetch("simple_session.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ idToken: idToken, uid: user.uid, username: username, email: user.email, role: userRole, servicePreference: servicePreference }) // Pass the uid, email, role and service preference to session.php
                });

                const result = await response.json();
                
                if (result.success) {
                    // Redirect to Dashboard.php for both admin and user
                    window.location.href = "Dashboard.php";
                } else {
                    handleLoginError("Login succeeded, but session creation failed: " + (result.message || 'Unknown error'));
                }

            } catch (error) {
                handleLoginError("Login failed: " + error.message);
            } finally {
                // Clear the flag regardless of success or failure
                isActivelyLoggingIn = false;
            }
            } // End handleSignIn function
        }); // End DOMContentLoaded
        
        // Backup event listener in case DOMContentLoaded doesn't work
        window.addEventListener('load', function() {
            const form = document.getElementById("signinForm");
            if (form && !form.hasAttribute('data-listener-attached')) {
                form.setAttribute('data-listener-attached', 'true');
                
                form.onsubmit = function(e) {
                    e.preventDefault();
                    isActivelyLoggingIn = true;
                    handleSignIn();
                    return false;
                };
            }
        });

        // --- Simple Authentication State Change Listener ---
        onAuthStateChanged(auth, async (user) => {
            // Don't interfere if user is actively logging in
            if (isActivelyLoggingIn) {
                return;
            }
            
            // If user is already authenticated and on signin page, create PHP session then redirect
            if (user && user.emailVerified && window.location.pathname.toLowerCase().includes('signin')) {
                const username = user.displayName || user.email.split('@')[0];
                
                // Get fresh ID token for session
                const idToken = await user.getIdToken(true); // Force refresh the token
                
                // Get user data from Firestore to include service preference
                const userDocRef = doc(db, "users", user.uid);
                const docSnap = await getDoc(userDocRef);
                const userData = docSnap.exists() ? docSnap.data() : {};
                const servicePreference = userData.servicePreference || 'both';
                
                const payload = { 
                    uid: user.uid, 
                    username: username, 
                    email: user.email, 
                    role: userData.role || 'user',
                    servicePreference: servicePreference,
                    idToken: idToken // Include the fresh ID token
                };
                
                try {
                    const res = await fetch('simple_session.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });
                    const js = await res.json();
                    if (js.success) {
                        window.location.replace('Dashboard.php');
                    } else {
                        await auth.signOut();
                    }
                } catch (err) {
                    await auth.signOut();
                }
            }
        });

        // --- Forgot Password Modal Functionality ---

        forgotPasswordLink.addEventListener("click", (e) => {
            e.preventDefault(); // Prevent default link behavior
            forgotPasswordModal.classList.add("active"); // Show the modal
            resetEmailInput.value = ''; // Clear previous email
            resetMessage.textContent = ''; // Clear previous messages
        });

        cancelResetBtn.addEventListener("click", () => {
            forgotPasswordModal.classList.remove("active"); // Hide the modal
        });

        // Optional: Close modal if clicking outside the content
        forgotPasswordModal.addEventListener("click", (e) => {
            if (e.target === forgotPasswordModal) {
                forgotPasswordModal.classList.remove("active");
            }
        });

        sendResetEmailBtn.addEventListener("click", async () => {
            const email = resetEmailInput.value.trim();
            if (!email) {
                resetMessage.textContent = "Please enter your email address.";
                resetMessage.style.color = "red";
                return;
            }

            resetMessage.textContent = "Sending...";
            resetMessage.style.color = "#555";

            try {
                await sendPasswordResetEmail(auth, email);
                resetMessage.textContent = "If an account with that email exists, a password reset link has been sent to your inbox. Please check your email.";
                resetMessage.style.color = "green";
                // Optionally close modal after a short delay
                setTimeout(() => {
                    forgotPasswordModal.classList.remove("active");
                }, 3000);
            } catch (error) {
                let errorMessage = "Failed to send password reset email. Please try again.";
                if (error.code) {
                    switch (error.code) {
                        case 'auth/invalid-email':
                            errorMessage = 'The email address is invalid.';
                            break;
                        case 'auth/user-not-found':
                            // For security, Firebase recommends giving a generic message for user-not-found
                            // to avoid revealing if an email exists in your system.
                            errorMessage = "If an account with that email exists, a password reset link has been sent to your inbox. Please check your email.";
                            break;
                        default:
                            errorMessage = error.message;
                    }
                }
                resetMessage.textContent = "Error: " + errorMessage;
                resetMessage.style.color = "red";
                console.error("Password Reset Error:", error);
            }
        });

    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Create an Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet"/>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: url('https://www.meritxellchildrensfoundation.org/images/1.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px 30px;
            width: 90%;
            max-width: 700px;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
            position: relative;
        }
        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #000;
        }
        form {
            display: flex;
            flex-wrap: wrap;
            gap: 16px 30px;
        }
        .form-group {
            flex: 1 1 45%;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .form-group.full-width {
            flex: 1 1 100%;
        }
        label {
            margin-bottom: 5px;
            font-size: 14px;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            width: 100%;
        }
        .form-footer {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 10px;
        }
        .btn-signup, .btn-back {
            border: none;
            padding: 8px 20px;
            color: #fff;
            font-size: 14px;
            border-radius: 44px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-signup {
            background: #7CB9E8;
        }
        .btn-back {
            background: #65676D;
        }
        .btn-signup:hover {
            background: #5a9bf5;
        }
        .btn-back:hover {
            background: #4f5055;
        }
        .logo {
            position: absolute;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            height: 60px;
        }

        #password-box {
            display: none;
            position: absolute;
            top: 40px;
            left: calc(100% + 10px);
            width: 250px;
            background: #fff;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            font-size: 13px;
            z-index: 10;
        }

        #password-box ul {
            list-style-type: none;
            padding-left: 10px;
            margin: 5px 0 0 0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 25px 20px;
            }

            h2 {
                font-size: 20px;
            }

            .form-group {
                flex: 1 1 100%;
            }

            .form-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-signup, .btn-back {
                width: 100%;
                margin-top: 10px;
            }

            #password-box {
                position: static;
                width: 100%;
                margin-top: 10px;
            }
        }

        .preference-options {
            display: flex;
            flex-direction: row;
            gap: 10px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .preference-item {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            padding: 8px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            transition: border-color 0.3s ease, background-color 0.3s ease;
            cursor: pointer;
            flex: 1;
            min-width: 180px;
        }

        .preference-item:hover {
            border-color: #7CB9E8;
            background-color: #f8fcff;
        }

        .preference-item input[type="radio"] {
            margin: 0;
            width: auto;
            margin-top: 2px;
        }

        .preference-item label {
            font-size: 14px;
            font-weight: 500;
            color: #333;
            cursor: pointer;
            margin: 0;
            flex: 1;
            line-height: 1.3;
        }

        .preference-description {
            font-size: 11px;
            color: #666;
            margin: 2px 0 0 0;
            line-height: 1.2;
        }

        .preference-item input[type="radio"]:checked + label {
            color: #7CB9E8;
        }

        .preference-item:has(input[type="radio"]:checked) {
            border-color: #7CB9E8;
            background-color: #f8fcff;
        }

        @media (max-width: 600px) {
            .preference-options {
                flex-direction: column;
                gap: 8px;
            }
            
            .preference-item {
                min-width: auto;
            }
        }
    </style>
</head>
<body>

<img src="https://www.meritxellchildrensfoundation.org/images/logo-with-words-3.png" alt="Logo" class="logo" />

<div class="container">
    <h2>Create an Account</h2>
    <form id="signup-form">
        <div class="form-group">
            <label>First Name:</label>
            <input type="text" name="firstname" required />
        </div>
        <div class="form-group">
            <label>Middle Name:</label>
            <input type="text" name="middlename" />
        </div>
        <div class="form-group">
            <label>Last Name:</label>
            <input type="text" name="lastname" required />
        </div>
        <div class="form-group">
            <label>Email Address:</label>
            <input
                type="email"
                name="email"
                required
                pattern="[a-z0-9._%+-]+@(gmail\.com|yahoo\.com)"
                title="Email must be a valid gmail.com or yahoo.com address."
            />
        </div>
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" required />
        </div>

        <div class="form-group full-width">
            <label>Password:</label>
            <input type="password" name="password" id="password" required
                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                title="Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a number, and a special character." />
            <div id="password-box">
                <strong>Password must contain:</strong>
                <ul>
                    <li id="length" style="color: red;">• At least 8 characters</li>
                    <li id="uppercase" style="color: red;">• An uppercase letter</li>
                    <li id="lowercase" style="color: red;">• A lowercase letter</li>
                    <li id="number" style="color: red;">• A number</li>
                    <li id="special" style="color: red;">• A special character</li>
                </ul>
            </div>
        </div>

        <div class="form-group full-width">
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required />
        </div>

        <div class="form-group full-width">
            <label>Birthdate:</label>
            <input type="date" name="birthdate" required />
        </div>

        <div class="form-group full-width">
            <label>Service Preference:</label>
            <div class="preference-options">
                <div class="preference-item">
                    <input type="radio" name="service_preference" value="adopt_only" id="adopt_only" required />
                    <label for="adopt_only">
                        🏡 Adopt Only
                        <div class="preference-description">Access adoption-related modules, AI bot, and inbox chat</div>
                    </label>
                </div>
                <div class="preference-item">
                    <input type="radio" name="service_preference" value="donate_only" id="donate_only" required />
                    <label for="donate_only">
                        💖 Donate Only
                        <div class="preference-description">Access donation-related modules only</div>
                    </label>
                </div>
                <div class="preference-item">
                    <input type="radio" name="service_preference" value="both" id="both" required />
                    <label for="both">
                        🌟 Both
                        <div class="preference-description">Access both adoption and donation modules</div>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="button" class="btn-back" onclick="history.back()">Back</button>
            <button type="submit" class="btn-signup">Sign Up</button>
        </div>
    </form>
</div>

<script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>

<script>
    const firebaseConfig = {
        apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
        authDomain: "ally-user.firebaseapp.com",
        projectId: "ally-user",
        storageBucket: "ally-user.appspot.com",
        messagingSenderId: "567088674192",
        appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
    };

    firebase.initializeApp(firebaseConfig);
    const auth = firebase.auth();
    const db = firebase.firestore();

    const form = document.getElementById("signup-form");
    const passwordInput = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const passwordBox = document.getElementById("password-box");

    const reqs = {
        length: document.getElementById("length"),
        uppercase: document.getElementById("uppercase"),
        lowercase: document.getElementById("lowercase"),
        number: document.getElementById("number"),
        special: document.getElementById("special")
    };

    passwordInput.addEventListener("focus", () => {
        passwordBox.style.display = "block";
    });

    passwordInput.addEventListener("blur", () => {
        // A slight delay to allow clicking on the password box before it disappears
        setTimeout(() => {
            passwordBox.style.display = "none";
        }, 150);
    });

    passwordInput.addEventListener("input", () => {
        const val = passwordInput.value;
        reqs.length.style.color = val.length >= 8 ? "green" : "red";
        reqs.uppercase.style.color = /[A-Z]/.test(val) ? "green" : "red";
        reqs.lowercase.style.color = /[a-z]/.test(val) ? "green" : "red";
        reqs.number.style.color = /\d/.test(val) ? "green" : "red";
        reqs.special.style.color = /[\W_]/.test(val) ? "green" : "red";
    });

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Signup form submitted."); // DEBUG

        // Validate email domain explicitly in JS as well
        const email = form.email.value.trim().toLowerCase();
        const allowedDomains = ["gmail.com", "yahoo.com"];
        const emailDomain = email.substring(email.lastIndexOf("@") + 1);

        if (!allowedDomains.includes(emailDomain)) {
            alert("Please enter a valid email address from gmail.com or yahoo.com only.");
            form.email.focus();
            return;
        }

        if (passwordInput.value !== confirmPassword.value) {
            alert("Passwords do not match!");
            confirmPassword.focus();
            return;
        }

        const firstname = form.firstname.value;
        const middlename = form.middlename.value;
        const lastname = form.lastname.value;
        const username = form.username.value;
        const birthdate = form.birthdate.value; // YYYY-MM-DD format
        const servicePreference = form.service_preference.value; // Get selected preference

        auth.createUserWithEmailAndPassword(email, passwordInput.value)
            .then((userCredential) => {
                console.log('User created successfully:', userCredential.user.uid);
                
                // Store user data in Firestore
                return db.collection('users').doc(userCredential.user.uid).set({
                    firstName: firstname,
                    middleName: middlename,
                    lastName: lastname,
                    username: username,
                    email: email,
                    birthdate: birthdate,
                    servicePreference: servicePreference, // Add service preference
                    emailVerified: false, // This will be updated when email is verified
                    isVerified: false, // This will be set to true when email is verified via cloud function
                    role: 'user',
                    createdAt: firebase.firestore.FieldValue.serverTimestamp()
                });
            })
            .then(() => {
                // Send email verification using Firebase's default system
                return auth.currentUser.sendEmailVerification();
            })
            .then(() => {
                console.log('Email verification sent successfully');
                alert('Account created successfully! Please check your email and click the verification link to activate your account.');
                
                // Clear the form and redirect to sign in
                form.reset();
                // Optional: redirect to sign in page after a delay
                setTimeout(() => {
                    window.location.href = "Signin.php";
                }, 2000);
            })
            .catch(error => {
                console.error("Error during signup process:", error.message); // DEBUG
                let errorMessage = "An unknown error occurred. Please try again.";
                if (error.code) {
                    switch (error.code) {
                        case 'auth/email-already-in-use':
                            errorMessage = 'The email address is already in use by another account.';
                            break;
                        case 'auth/invalid-email':
                            errorMessage = 'The email address is not valid.';
                            break;
                        case 'auth/operation-not-allowed':
                            errorMessage = 'Email/password accounts are not enabled. Please enable this in your Firebase project settings.';
                            break;
                        case 'auth/weak-password':
                            errorMessage = 'The password is too weak. Please use a stronger password.';
                            break;
                        default:
                            errorMessage = error.message; // Fallback to Firebase's message
                    }
                }
                alert("Error: " + errorMessage);
            });
    });
</script>

</body>
</html>
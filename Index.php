<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['uid']);

// If user is logged in, redirect to Dashboard
if ($isLoggedIn) {
    header('Location: Dashboard.php');
    exit;
}

if (!isset($_SESSION['alert'])) {
    $_SESSION['alert'] = null;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meritxell Children's World Foundation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        #get-started{
            background-color: #7CB9E8;
            color: #FFFFFF;
            margin: auto;
            padding: 10px;
            width: 10%;
            border: none;
            border-radius: 8px;  
            transition: 0.2s;
        }

        #get-started:hover{
            background-color: #6ea4ce;
        }

        #btn-donate{
            background-color: #7CB9E8;
            color: #FFFFFF;
            margin-left: 50px;
            padding: 13px 15px;
            border: none;
            border-radius: 8px;  
            transition: 0.2s;
        }

        #btn-donate:hover{
            background-color: #6ea4ce;
        }

        #btn-submit{
            background-color: #7CB9E8;
            color: #FFFFFF;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;  
            transition: 0.2s;
        }

        #btn-submit:hover{
            background-color: #6ea4ce;
        }

        #about-us{
            text-align: justify;
            text-justify: inter-word;
        }

        /*Form*/
        form {
            max-width: 600px;
            margin: auto;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-row {
            display: flex;
            gap: 10px;
        }
        input,
        textarea {
            width: 90%;
            padding: 8px;
            border: 1px solid #000000;
            background-color: #fff7f7;
            box-sizing: border-box;
        }
        small {
            color: #666;
            margin-top: 3px;
        }
    </style>
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">
    
    <!-- Public Navbar -->
    <nav style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background-color: #7CB9E8; color: white; height: 80px;">
        <div style="display: flex; align-items: center;">
            <a href="Index.php"><img src="https://www.meritxellchildrensfoundation.org/images/logo-with-words-3.png" alt="Logo" style="height: 60px;"></a>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="Signin.php" style="color: white; text-decoration: none; padding: 8px 16px; background: rgba(255,255,255,0.2); border-radius: 4px; transition: 0.2s;">Sign In</a>
            <a href="Signup.php" style="color: white; text-decoration: none; padding: 8px 16px; background: rgba(255,255,255,0.2); border-radius: 4px; transition: 0.2s;">Sign Up</a>
        </div>
    </nav>

    <!-- Main content -->
    <div class="content-wrapper">
        <div class="content">
            <div class="container py-4">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-12 mb-3">
                        <img src="https://www.meritxellchildrensfoundation.org/images/3.jpg" class="img-fluid rounded shadow" alt="Picture 1">
                    </div>
                    <div class="col-md-6 col-12 mb-3">
                        <img src="https://www.meritxellchildrensfoundation.org/images/2.jpg" class="img-fluid rounded shadow" alt="Picture 2">
                    </div>
                    <div class="col-12 text-center mt-4 mb-5">
                        <p style="font-family: 'Source Serif Pro'; font-size: 22px; color: #3F4045;">A Loving and Nurturing home for Children</p>
                        <a href="Signin.php"><button id="get-started" class="btn btn-primary mt-2">Get Started</button></a>
                    </div>

                    <div class="col-md-6 col-12 mb-5">
                        <h1 id="about" style="font-family: 'Source Serif Pro';">ABOUT US</h1>
                        <hr style="border: 2px solid;">
                        <p id="about-us">Meritxell Children's World Foundation Inc. (MCWFI) has been caring for 
                            marginalized and abandoned children aged 0-15 since 2008. Meritxell began 
                            as a response to the difficulties of providing adequate care and attention to 
                            abandoned girls in the state run care system in the Philippines. In 2010 
                            Meritxell Children's World Foundation Inc expanded to begin a home for abandoned infants.
                            The Foundation was established to provide a loving environment for children who have had 
                            little opportunity for any kind of family support, specifically those who have been rescued, 
                            abandoned, neglected, orphaned, surrendered and found. It is more of a home than an institution.</p>
                    </div>
                    <div class="col-md-6 col-12 mb-5">
                        <img src="Meritxell.png" class="img-fluid rounded shadow" alt="Picture 2">
                    </div>

                    <div class="col-md-6 col-12 mb-3">
                        <img src="Meritxell.png" class="img-fluid rounded shadow" alt="Picture 2">
                    </div>
                    <div class="col-md-6 col-12 mb-3">
                        <h1 style="font-family: 'Source Serif Pro';">THE WORK THAT WE DO</h1>
                        <hr style="border: 2px solid;">
                        <p id="about-us">Meritxell Foundation trustees envision a permanent family placement for each child, 
                            if family reintegration is not possible. We explore all possible options for permanent adoption 
                            placements for the children in our care with loving families, locally or overseas. This includes 
                            a partnership with Inter-County-Adoption Board (ICAB) for the participation of Meritxell children 
                            in the summer and winter camp programs in the USA for older children, which provides wonderful life 
                            experiences they would not otherwise have and inter-country adoption.</p>
                    </div>

                    <div class="col-12 text-center mt-4 mb-5">
                        <p style="font-family: 'Source Serif Pro'; font-size: 22px; font-style: italic; color: #0F4562;">CONNECT WITH MERITXELL</p>
                    </div>

                    <div class="col-md-6 col-12 mb-3">
                        <h3 style="font-family: 'Source Serif Pro'; color: #0F4562;">EMAIL US</h3>
                        <hr style="border: 2px solid; max-width: 40%; margin-left:0;">
                        
                        <form>
                            <div class="form-group">
                                <label>Name</label>
                                <div class="form-row">
                                <div class="input-column">
                                    <input type="text" name="lastname">
                                    <small>Last name</small>
                                </div>
                                <div class="input-column">
                                    <input type="text" name="firstname">
                                    <small>First name</small>
                                </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject">
                                <small>Subject</small>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <div class="form-row">
                                <div class="input-column">
                                    <input type="email" name="email">
                                    <small>Email Address</small>
                                </div>
                                <div class="input-column">
                                    <input type="email" name="confirm_email">
                                    <small>Confirm Email</small>
                                </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Message</label>
                                <textarea name="message" rows="5"></textarea>
                            </div>

                            <button type="submit" id="btn-submit">Submit</button>

                        </form>
                        
                    </div>
                    <div class="col-md-6 col-12 mb-3">
                        <h3 id="contact" style="font-family: 'Source Serif Pro'; color: #0F4562;">CONTACT US</h3>
                        <hr style="border: 2px solid; max-width: 40%; margin-left:0;">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.3127278444663!2d121.1164694920467!3d14.638181127561591!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b9aebe20a0b7%3A0x29f928af7863ac25!2sMeritxell%20Children&#39;s%20Home!5e0!3m2!1sen!2sus!4v1745685015350!5m2!1sen!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>

                    <div class="col-md-6 col-12 mb-3">
                        <h5 style="font-family: 'Source Serif Pro'; color: #0F4562;">JOIN US IN BRINGING HOPE AND HEALING!</h5>
                        <hr style="border: 2px solid;">
                        <br>
                        <h5 style="color: #0F4562;">You're invited to partner with us!</h5>
                        <p id="about-us">
                            Thank you for being part of our journey to transform children's lives in meaningful ways! 
                            To make a one-time online donation, simply click the red "Donate Now!" button to get started.
                        </p>
                        <br>
                        <h5 style="color: #0F4562;">To Support Meritxell via Mail:</h5>
                        <p>Checks can be made out to: "Meritxell Children's World Foundation"</p>
                        <p>*An official receipt will be sent for tax purposes</p>
                        <p>Checks can be mailed to:</p>
                        <p style="text-indent: 24px; font-family: 'Source Serif Pro';">24 Peach St, Marikina, 1811 </p>
                        <p style="text-indent: 24px; font-family: 'Source Serif Pro'; margin-top: -10px;">Metro Manila</p>
                    </div>

                    <div class="col-md-6 col-12 mb-3">
                        <br><br><br><br>
                        <a href="Signin.php"><button id="btn-donate" class="btn btn-primary mt-2">DONATE NOW!</button></a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="main-footer text-center">
        <strong>&copy; 2024 Meritxell Children's World Foundation.</strong> All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
</body>
</html> 
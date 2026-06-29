<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ACSCI SSLG Portal</title>
 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #4a0000 0%, #800000 50%, #b30000 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .logo-section img {
            width: 100px;
            height: auto;
            margin-bottom: 15px;
        }

        .brand-titles h1 {
            font-size: 20px;
            color: #800000;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .brand-titles h2 {
            font-size: 13px;
            color: #555;
            font-weight: 400;
            margin-top: 4px;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            color: #333;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: #800000;
            box-shadow: 0 0 8px rgba(128, 0, 0, 0.15);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: #800000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: #600000;
        }

        .login-btn:active {
            transform: scale(0.98);
        }

        .footer-text {
            margin-top: 25px;
            font-size: 12px;
            color: #777;
        }

        .footer-text a {
            color: #800000;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Logo Section -->
        <div class="logo-section">
            <!-- Ensure you place your ACSci logo in the path below, or fallback to an image -->
            <img src="uploads/logo.png" alt="Angeles City Science High School Logo" onerror="this.src='https://placehold.co/100x100?text=ACSci+Logo'">
        </div>

        <!-- School Name Custom Headers -->
        <div class="brand-titles">
            <h1>ACSci SSLG</h1>
            <h2>Angeles City Science High School Portal</h2>
        </div>

        <!-- Form Section -->
        <form action="login_process.php" method="POST">
            <div class="input-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            </div>

            <button type="submit" class="login-btn">Log In</button>
        </form>

        <div class="footer-text">
            <p>Supreme Student Learner Government © 2026</p>
            <p><a href="index.php">← Back to Main Website</a></p>
        </div>
    </div>

</body>
</html>

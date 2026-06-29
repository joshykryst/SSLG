<?php
session_start();
require 'Config.php';

$isLoggedIn = false;
$userData = null;

if(!empty($_SESSION["User_ID"])){
    $User_ID = $_SESSION["User_ID"];
    $result = mysqli_query($conn, "SELECT * FROM table_user 
        WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $userData = mysqli_fetch_assoc($result);
        $isLoggedIn = true;
    }
}

$message = null;
$inquiry = null;

// Check if tracking ID and email are provided
if (isset($_GET['tracking']) && isset($_GET['email'])) {
    $tracking_id = (int)$_GET['tracking'];
    $email = mysqli_real_escape_string($conn, $_GET['email']);
    
    // Query to get the message
    $query = "SELECT * FROM contact_feedback WHERE id = ? AND email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $tracking_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $inquiry = $result->fetch_assoc();
    } else {
        $message = "No message found with the provided tracking ID and email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Status - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .inquiry-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .status-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-new {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-read {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-responded {
            background-color: #d4edda;
            color: #155724;
        }
        
        .inquiry-subject {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .inquiry-meta {
            color: #666;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }
        
        .inquiry-meta span {
            margin-right: 15px;
        }
        
        .inquiry-content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            white-space: pre-line;
        }
        
        .response-container {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #ddd;
        }
        
        .response-header {
            color: #155724;
            margin-bottom: 15px;
        }
        
        .response-content {
            background-color: #f0f7f2;
            padding: 20px;
            border-left: 4px solid #28a745;
            border-radius: 4px;
            white-space: pre-line;
        }
        
        .response-date {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 10px;
            text-align: right;
        }
        
        .status-form {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .status-form h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        
        .check-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 500;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .check-btn:hover {
            background-color: #2980b9;
        }
        
        .no-inquiry {
            text-align: center;
            padding: 40px 0;
        }
        
        .no-inquiry h3 {
            color: #555;
            margin-bottom: 10px;
        }
        
        .no-inquiry p {
            color: #777;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        /* Basic navbar styles */
        .main-nav {
            background-color: #1a2a3a;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .nav-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
        }
        
        .nav-logo {
            height: 50px;
            margin-right: 15px;
        }
        
        .brand-text h1 {
            margin: 0;
            font-size: 1.5rem;
            color: white;
        }
        
        .brand-text p {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.8;
            color: white;
        }
        
        /* Footer styles */
        .footer {
            background-color: #1a2a3a;
            color: white;
            padding-top: 50px;
            margin-top: 50px;
        }
        
        .footer-top {
            background-color: #1a2a3a;
            padding-bottom: 30px;
        }
        
        .footer-bottom {
            background-color: #0f1a25;
            padding: 15px 0;
            text-align: center;
        }
        
        .footer-bottom p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <!-- Simple navbar -->
    <nav class="main-nav">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
                <div class="brand-text">
                    <h1>CMRICTHS</h1>
                    <p>Information and Communication Technology High School</p>
                </div>
            </a>
        </div>
    </nav>

    <main class="inquiry-container">
        <?php if (isset($inquiry)): ?>
            <div class="status-card">
                <div class="status-header">
                    <h1 class="inquiry-subject"><?php echo htmlspecialchars($inquiry['subject']); ?></h1>
                    <span class="status-badge status-<?php echo $inquiry['status']; ?>">
                        <?php 
                        switch($inquiry['status']) {
                            case 'new':
                                echo 'New';
                                break;
                            case 'read':
                                echo 'In Review';
                                break;
                            case 'responded':
                                echo 'Responded';
                                break;
                        }
                        ?>
                    </span>
                </div>
                
                <div class="inquiry-meta">
                    <span><strong>From:</strong> <?php echo htmlspecialchars($inquiry['name']); ?></span>
                    <span><strong>Email:</strong> <?php echo htmlspecialchars($inquiry['email']); ?></span>
                    <span><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($inquiry['created_at'])); ?></span>
                    <span><strong>Tracking ID:</strong> #<?php echo $inquiry['id']; ?></span>
                </div>
                
                <h3>Your Message:</h3>
                <div class="inquiry-content">
                    <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                </div>
                
                <?php if($inquiry['status'] === 'responded' && !empty($inquiry['response'])): ?>
                    <div class="response-container">
                        <h3 class="response-header">Our Response:</h3>
                        <div class="response-content">
                            <?php echo nl2br(htmlspecialchars($inquiry['response'])); ?>
                        </div>
                        <div class="response-date">
                            Responded on: <?php echo date('F j, Y g:i A', strtotime($inquiry['response_date'])); ?>
                        </div>
                    </div>
                <?php elseif($inquiry['status'] === 'read'): ?>
                    <div class="response-container">
                        <h3>Status Update:</h3>
                        <p>Your message is currently under review. We'll respond as soon as possible.</p>
                    </div>
                <?php else: ?>
                    <div class="response-container">
                        <h3>Status Update:</h3>
                        <p>Your message has been received. Our team will review it soon.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif (isset($_GET['tracking']) || isset($_GET['email'])): ?>
            <div class="status-card no-inquiry">
                <h3>Message Not Found</h3>
                <p>We couldn't find a message with the provided tracking ID and email. Please check your details and try again.</p>
            </div>
        <?php else: ?>
            <div class="status-form">
                <h2>Check Your Message Status</h2>
                
                <form action="contacts_status.php" method="GET">
                    <div class="form-group">
                        <label for="tracking">Tracking ID:</label>
                        <input type="text" id="tracking" name="tracking" required placeholder="Enter your tracking ID number">
                    </div>
                    <div class="form-group">
                        <label for="email">Your Email:</label>
                        <input type="email" id="email" name="email" required placeholder="Enter the email you used"
                               value="<?php echo $isLoggedIn ? htmlspecialchars($userData['Email']) : ''; ?>">
                    </div>
                    <button type="submit" class="check-btn">Check Status</button>
                </form>
            </div>
        <?php endif; ?>
        
        <a href="contacts.php" class="back-link">Back to Contact Page</a>
    </main>

    <!-- Simple footer -->
    <footer class="footer">
        <div class="footer-top">
            <div class="container">
                <!-- Footer content would go here -->
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> CMRICTHS. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Any JavaScript can go here
    </script>
</body>
</html>
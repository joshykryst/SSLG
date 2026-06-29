<?php

session_start();
require 'Config.php';


if(empty($_SESSION["User_ID"]) || $_SESSION["user_type"] !== "admin") {
    header("Location: Login.php");
    exit();
}


function getContactSettings($conn) {
    $settings = [];
    $result = mysqli_query($conn, "SELECT * FROM contact_settings");
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}


$message = '';
$messageType = '';

if(isset($_POST['update_contacts'])) {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $hours = mysqli_real_escape_string($conn, $_POST['hours']);
    $google_map_embed = mysqli_real_escape_string($conn, $_POST['google_map_embed']);
    

    $queries = [
        "UPDATE contact_settings SET setting_value = '$address' WHERE setting_key = 'address'",
        "UPDATE contact_settings SET setting_value = '$phone' WHERE setting_key = 'phone'",
        "UPDATE contact_settings SET setting_value = '$email' WHERE setting_key = 'email'",
        "UPDATE contact_settings SET setting_value = '$hours' WHERE setting_key = 'hours'",
        "UPDATE contact_settings SET setting_value = '$google_map_embed' WHERE setting_key = 'google_map_embed'"
    ];
    
    $success = true;
    foreach($queries as $query) {
        if(!mysqli_query($conn, $query)) {
            $success = false;
            break;
        }
    }
    
    if($success) {
        $message = "Contact information has been updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating contact information: " . mysqli_error($conn);
        $messageType = "error";
    }
}


$settings = getContactSettings($conn);


$User_ID = $_SESSION["User_ID"];
$result = mysqli_query($conn, "SELECT * FROM table_user WHERE User_ID = '$User_ID'");
$userData = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .admin-title {
            color: #1a237e;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        .form-control:focus {
            border-color: #3949ab;
            outline: none;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .admin-btn {
            background: linear-gradient(45deg, #3949ab, #1a237e);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .admin-btn:hover {
            box-shadow: 0 5px 15px rgba(26, 35, 126, 0.3);
            transform: translateY(-2px);
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .map-preview {
            margin-top: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9f9f9;
        }
        
        .map-preview iframe {
            width: 100%;
            height: 250px;
            border: 0;
        }
    </style>
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="admin_dashboard.php" class="nav-brand">
                    <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
                    <div class="brand-text">
                        <h1>Admin Dashboard</h1>
                    </div>
                </a>
                <div class="nav-links">
                    <a href="admin_dashboard.php">Dashboard</a>
                    <a href="admin_users.php">Users</a>
                    <a href="admin_news.php">News</a>
                    <a href="admin_contacts.php" class="active">Contacts</a>
                    <a href="admin_messages.php">Messages</a>
                </div>
            </div>
            <div class="nav-right">
                <div class="profile-trigger" onclick="toggleMenu()">
                    <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" alt="Profile" class="profile-avatar">
                    <span class="profile-name"><?php echo htmlspecialchars($userData['Username']); ?></span>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" alt="User Profile" class="sidebar-logo">
            <h3>Welcome, <?php echo htmlspecialchars($userData['Username']); ?></h3>
            <button class="close-btn" onclick="toggleMenu()">
                <span class="icon icon-close"></span>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">
                <h4>Admin Menu</h4>
                <a href="admin_dashboard.php"><span class="icon icon-dashboard"></span>Dashboard</a>
                <a href="admin_users.php"><span class="icon icon-users"></span>Manage Users</a>
                <a href="admin_news.php"><span class="icon icon-news"></span>Manage News</a>
                <a href="admin_contacts.php" class="active"><span class="icon icon-contact"></span>Contact Settings</a>
                <a href="admin_messages.php"><span class="icon icon-messages"></span>View Messages</a>
            </div>
            
            <div class="nav-section">
                <h4>Account</h4>
                <a href="Indexs.php"><span class="icon icon-home"></span>Visit Website</a>
                <a href="Logout.php" class="logout-btn">
                    <span class="icon icon-logout"></span>Logout
                </a>
            </div>
        </nav>
    </div>

    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">Contact Information Settings</h1>
            <a href="admin_dashboard.php" class="admin-btn">Back to Dashboard</a>
        </div>
        
        <?php if(!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-section">
                <h2>Contact Details</h2>
                <div class="form-group">
                    <label for="address">School Address</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($settings['address'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="hours">Office Hours</label>
                    <input type="text" id="hours" name="hours" class="form-control" value="<?php echo htmlspecialchars($settings['hours'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Google Maps Embed</h2>
                <div class="form-group">
                    <label for="google_map_embed">Google Map Embed URL</label>
                    <textarea id="google_map_embed" name="google_map_embed" class="form-control" required><?php echo htmlspecialchars($settings['google_map_embed'] ?? ''); ?></textarea>
                    <small>Go to Google Maps, search for your location, click "Share", select "Embed a map", copy the iframe src URL and paste it here.</small>
                </div>
                
                <div class="map-preview">
                    <h3>Map Preview</h3>
                    <iframe 
                        id="map_preview"
                        src="<?php echo htmlspecialchars($settings['google_map_embed'] ?? ''); ?>" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
            
            <button type="submit" name="update_contacts" class="admin-btn">Save Changes</button>
        </form>
    </div>
    
    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
        

        document.getElementById('google_map_embed').addEventListener('input', function() {
            document.getElementById('map_preview').src = this.value;
        });
    </script>
</body>
</html>
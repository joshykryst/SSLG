<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

// Add this code after your database connection and before displaying the contact info

// Function to get contact settings
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

// Get contact settings
$contactSettings = getContactSettings($conn);

// Set default values if settings are not found
$address = $contactSettings['address'] ?? 'Dona Aurora St, Claro M. Recto, Angeles City, Pampanga';
$phone = $contactSettings['phone'] ?? '(045) 887 5502';
$email = $contactSettings['email'] ?? 'cmricthsangelescity@yahoo.com';
$hours = $contactSettings['hours'] ?? 'Monday - Friday: 6:00 AM - 6:00 PM';
$mapEmbedUrl = $contactSettings['google_map_embed'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d516.5982697312345!2d120.59260559999996!3d15.1450612!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3396f241c2dfd6db%3A0xc17b92187d52d12c!2sClaro%20M.%20Recto%20Information%20and%20Communication%20Technology%20High%20School!5e0!3m2!1sen!2sph!4v1740848353961!5m2!1sen!2sph';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                        url('mathh.jpg') no-repeat center center/cover;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            margin-bottom: 4rem;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .info-card, .form-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .info-items {
            margin-top: 2rem;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .info-item .icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            background: #f0f4f8;
            padding: 1rem;
            border-radius: 50%;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a5568;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .submit-btn {
            background: #4299e1;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: inline-block;
            width: auto;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: #3182ce;
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(66, 153, 225, 0.3);
        }

        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
        }

        .contact-container {
            display: flex;
            max-width: 1200px;
            margin: 60px auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
        }

        .info-section {
            background: linear-gradient(135deg, #3b5998 0%, #192f60 100%);
            color: white;
            padding: 40px;
            flex: 1;
        }

        .form-section {
            flex: 2;
            padding: 40px;
            background: #f9f9f9;
        }

        .info-box {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }

        .info-box:hover {
            transform: translateX(10px);
        }

        .info-icon {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }

        .input-group input:focus,
        .input-group textarea:focus {
            border-color: #3b5998;
            outline: none;
        }

        .send-btn {
            background: linear-gradient(135deg, #3b5998 0%, #192f60 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 89, 152, 0.3);
        }

        .social-icons {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }

        .social-icons a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .social-icons a:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 0.2);
        }

        .social-icons img {
            width: 20px;
            height: 20px;
            filter: brightness(0) invert(1);
        }

        

.map-container {
    width: 100%;
    max-width: 1200px;
    margin: 40px auto;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
}

.footer {
    background: var(--primary);
    color: white;
}

.footer-top {
    background: var(--primary);
    padding: 4rem 0;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.footer-logo {
    width: 100px;
    margin-bottom: 1rem;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 0.8rem;
}

.footer-section a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-section a:hover {
    color: white;
}

.footer-social {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.footer-social a {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.footer-social a:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-3px);
}

.social-icon {
    width: 20px;
    height: 20px;
    filter: brightness(0) invert(1); 
}

.footer-social {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.footer-social a {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.footer-social a:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-3px);
}

.social-icon {
    width: 20px;
    height: 20px;
    filter: brightness(0) invert(1); 

.footer-bottom {
    background: var(--primary-dark);
    padding: 1.5rem 0;
}

.footer-bottom .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-links {
    display: flex;
    gap: 2rem;
}

@media (max-width: 768px) {
    .footer-bottom .container {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .footer-links {
        justify-content: center;
    }
}
    </style>
</head>
<body>
  
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="nav-brand">
                    <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
                    <div class="brand-text">
                        <h1>CMRICTHS</h1>
                        <p>Information and Communication Technology High School</p>
                    </div>
                </a>
                <div class="nav-links">
                    <a href="Indexs.php">Home</a>
                    <a href="about.php">About</a>
                    <a href="Gallery.php">Gallery</a>
                    <a href="News.php">News & Events</a>
                    <a href="contacts.php" class="active">Contacts</a>
                </div>
            </div>
            <div class="nav-right">
                <?php if($isLoggedIn): ?>
                    <div class="nav-profile">
                        <div class="profile-dropdown">
                            <div class="profile-trigger" onclick="toggleMenu()">
                                <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" 
                                     alt="Profile" 
                                     class="profile-avatar">
                                <span class="profile-name"><?php echo htmlspecialchars($userData['Username']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="Login.php" class="portal-btn">
                        <span class="icon icon-login"></span>
                        Student Portal
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <?php if($isLoggedIn): ?>
                <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" alt="User Profile" class="sidebar-logo">
                <h3>Welcome, <?php echo htmlspecialchars($userData['Username']); ?></h3>
            <?php else: ?>
                <img src="profile-default.jpg" alt="Guest Profile" class="sidebar-logo">
                <h3>Welcome, Guest</h3>
            <?php endif; ?>
            <button class="close-btn" onclick="toggleMenu()">
                <span class="icon icon-close"></span>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <?php if($isLoggedIn): ?>
                
                <div class="nav-section">
                    <h4>Quick Links</h4>
                    <a href="dashboard.php"><span class="icon icon-dashboard"></span>Dashboard</a>
            
                    <a href="grades.php"><span class="icon icon-grades"></span>Grades</a>
                    <a href="schedule.php"><span class="icon icon-calendar"></span>Schedule</a>
                </div>
                
                <div class="nav-section">
                    <h4>Settings</h4>
                    
                    <a href="Logout.php" class="logout-btn">
                        <span class="icon icon-logout"></span>Logout
                    </a>
                </div>
            <?php else: ?>
               
                <div class="nav-section">
                    <h4>Menu</h4>
                    <a href="Indexs.php"><span class="icon icon-home"></span>Home</a>
                    <a href="about.php"><span class="icon icon-info"></span>About</a>
                    <a href="Gallery.php"><span class="icon icon-gallery"></span>Gallery</a>
                    <a href="Login.php"><span class="icon icon-login"></span>Login</a>
                    <a href="Register.php"><span class="icon icon-register"></span>Register</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>

    
    <div class="hero">
        <h1>Get in Touch</h1>
        <p>Have questions? We'd love to hear from you.</p>
    </div>

    <div class="container">
        <div class="contact-grid">
           
            <div class="contact-info">
                <div class="info-card">
                    <h2>Contact Information</h2>
                    <div class="info-items">
                        <div class="info-item">
                            <span class="icon">📍</span>
                            <div>
                                <h3>Location</h3>
                                <p><?php echo htmlspecialchars($address); ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="icon">📞</span>
                            <div>
                                <h3>Phone</h3>
                                <p><?php echo htmlspecialchars($phone); ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="icon">✉️</span>
                            <div>
                                <h3>Email</h3>
                                <p><?php echo htmlspecialchars($email); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           
            <div class="contact-form-container">
                <div class="form-card">
                    <h2>Send Message</h2>
                    <form action="process_contact.php" method="POST" class="form-grid">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Your Name</label>
                                <input type="text" id="name" name="name" required 
                                       value="<?php echo $isLoggedIn ? htmlspecialchars($userData['Username']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required
                                       value="<?php echo $isLoggedIn ? htmlspecialchars($userData['Email']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        <div class="form-group" style="grid-column: 1 / span 2;">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="8" required style="min-height: 200px; resize: vertical;"></textarea>
                        </div>
                        <div style="text-align: right; grid-column: 1 / span 2;">
                            <button type="submit" class="submit-btn" style="padding: 8px 20px; font-size: 0.9rem;">Send Message</button>
                        </div>
                    </form>
                    <?php if(isset($_GET['status'])): ?>
    <div class="alert <?php echo $_GET['status'] === 'success' ? 'alert-success' : 'alert-error'; ?>" 
         style="margin-top: 20px; padding: 15px; border-radius: 5px; 
         <?php echo $_GET['status'] === 'success' ? 
               'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 
               'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
        <?php echo htmlspecialchars($_GET['message']); ?>
        
        <?php if($_GET['status'] === 'success' && isset($_GET['tracking'])): ?>
            <div style="margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; border: 2px dashed #28a745;">
                <p style="margin: 0; font-size: 1.1em; text-align: center; color: #155724;">
                    Please save your tracking information
                </p>
                <div style="text-align: center; margin: 15px 0; padding: 10px; background-color: #e8f5e9; border-radius: 4px;">
                    <span style="font-size: 1.8em; font-weight: 700; color: #155724; letter-spacing: 1px;">
                        #<?php echo htmlspecialchars($_GET['tracking']); ?>
                    </span>
                </div>
                <p style="margin: 5px 0 15px 0; font-size: 0.9em; text-align: center;">
                    Keep this ID to check the status of your message later
                </p>
                <div style="text-align: center;">
                    <a href="contacts_status.php?tracking=<?php echo htmlspecialchars($_GET['tracking']); ?>&email=<?php echo urlencode($_GET['email']); ?>" 
                       style="display: inline-block; margin-top: 5px; color: white; text-decoration: none; font-weight: 500; background-color: #28a745; padding: 8px 20px; border-radius: 4px;">
                       <i class="fas fa-search"></i> Track Your Message
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        setTimeout(function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }
        }, <?php echo ($_GET['status'] === 'success' && isset($_GET['tracking'])) ? '15000' : '5000'; ?>);
    </script>
<?php endif; ?>
                </div>
                <div class="status-check-box" style="background-color: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 20px; border-left: 4px solid #17a2b8;">
                    <h3 style="margin-top: 0; color: #17a2b8; font-size: 1.1rem;">Already sent a message?</h3>
                    <p style="margin-bottom: 10px;">You can check the status of your previous inquiries using your tracking ID.</p>
                    <a href="contacts_status.php" style="color: #17a2b8; text-decoration: none; font-weight: 500;">
                        <i class="fas fa-search"></i> Check Message Status
                    </a>
                </div>
            </div>
        </div>
    </div>

   
    <div class="map-container">
        <iframe 
            src="<?php echo htmlspecialchars($mapEmbedUrl); ?>" 
            width="100%" 
            height="450" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>

   
    <footer class="footer">
        <div class="footer-top">
            <div class="container footer-grid">
                <div class="footer-section">
                    <img src="logo.png" alt="CMRICTHS Logo" class="footer-logo">
                    <h3>CMRICTHS</h3>
                    <p>Empowering students through quality ICT education.</p>
                    <div class="footer-social">
                        <a href="https://www.facebook.com/cmricthsunleashed"><img src="fb.png" alt="Facebook" class="social-icon"></a>
                        <a href="https://www.instagram.com/cmricths.sslg/"><img src="ig.png" alt="Instagram" class="social-icon"></a>
                        <a href="https://www.tiktok.com/@cmricths.sslg"><img src="tt.png" alt="TikTok" class="social-icon"></a>
                        <a href="https://www.facebook.com/CMRICTHSSSLG"><img src="fb.png" alt="Facebook Group" class="social-icon"></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="indexs.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="News.php">News & Events</a></li>
                        <li><a href="contacts.php">Contacts</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><?php echo htmlspecialchars($address); ?></li>
                        <li>Phone: <?php echo htmlspecialchars($phone); ?></li>
                        <li>Email: <?php echo htmlspecialchars($email); ?></li>
                        <li>Hours: <?php echo htmlspecialchars($hours); ?></li>
                    </ul>
                </div>
                
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> CMRICTHS. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#privacy">Privacy Policy</a>
                    <a href="#terms">Terms of Use</a>
                    <a href="#cookies">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    
    <script src="script.js"></script>
    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>
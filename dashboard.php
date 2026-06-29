<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'Config.php';


$isLoggedIn = false;
$userData = null;

if(!empty($_SESSION["User_ID"])){
    $User_ID = $_SESSION["User_ID"];
    $result = mysqli_query($conn, "SELECT * FROM table_user WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $userData = mysqli_fetch_assoc($result);
        $isLoggedIn = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script type="text/javascript">
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            const mainContent = document.querySelector('.main-content');
            
            if (sidebar) {
               
                sidebar.classList.toggle('active');
                
                
                body.classList.toggle('sidebar-open');
                
                
                if (window.innerWidth < 992) {
                    let overlay = document.querySelector('.sidebar-overlay');
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.className = 'sidebar-overlay';
                        overlay.addEventListener('click', toggleMenu);
                        document.body.appendChild(overlay);
                    }
                    
                   
                    if (sidebar.classList.contains('active')) {
                        overlay.classList.add('active');
                    } else {
                        overlay.classList.remove('active');
                    }
                }
            }
        }
        
        
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.classList.contains('active')) {
                document.body.classList.add('sidebar-open');
            }
        });
        
     
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    toggleMenu();
                }
            }
        });
    </script>
    
    <script src="dashboard.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
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
                    <a href="Indexs.php" class="active">Home</a>
                    <a href="about.php">About</a>
                    <a href="Gallery.php">Gallery</a>
                    <a href="#admissions">Admissions</a>
                    <a href="News.php">News & Events</a>
                    <a href="contacts.php">Contact</a>
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
                <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" 
                     alt="User Profile" class="sidebar-logo">
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
                    <a href="dashboard.php" class="active"><span class="icon icon-dashboard"></span>Dashboard</a>
                    
                    <a href="grades.php"><span class="icon icon-grades"></span>Grades</a>
                    <a href="schedule.php"><span class="icon icon-calendar"></span>Schedule</a>
                </div>
                <div class="nav-section">
                    <h4>Communications</h4>
                    <a href="messages.php"><span class="icon icon-messages"></span>Messages</a>
                    <a href="notifications.php"><span class="icon icon-notifications"></span>Notifications</a>
                </div>
                <div class="nav-section">
                    <h4>Settings</h4>
                    <a href="settings.php"><span class="icon icon-settings"></span>Settings</a>
                    <a href="Logout.php" class="logout-btn">
                        <span class="icon icon-logout"></span>Logout
                    </a>
                </div>
            <?php else: ?>
                
                <div class="nav-section">
                    <h4>Menu</h4>
                    <a href="Login.php"><span class="icon icon-login"></span>Login</a>
                    <a href="Register.php"><span class="icon icon-register"></span>Register</a>
                    <a href="#about"><span class="icon icon-info"></span>About Us</a>
                    <a href="#contact"><span class="icon icon-contact"></span>Contact</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>

    
    <main class="main-content">
        <div class="dashboard-container">
          
            <div class="welcome-section">
                <h1>Welcome, <?php echo htmlspecialchars($userData['Username']); ?>!</h1>
                <p>Your Student Dashboard</p>
            </div>

        
            <div class="dashboard-card profile-info-section">
                <div class="profile-header">
                    <div class="profile-image">
                        <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" 
                             alt="Profile Picture" id="previewImage">
                        <div class="image-upload">
                            <label for="profilePicture" class="upload-btn">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="profilePicture" name="profile_picture" accept="image/*" hidden>
                        </div>
                    </div>
                    <div class="profile-basic">
                        <h3><?php echo htmlspecialchars($userData['Username']); ?></h3>
                        <p class="student-id">Student ID: <?php echo htmlspecialchars($userData['User_ID']); ?></p>
                        <button type="button" class="edit-profile-btn" id="editProfileBtn">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                    </div>
                </div>
                
                <form id="profileUpdateForm" class="profile-form" method="POST" action="update_profile.php">
                    <div class="form-grid">
                        
                        <div class="form-section">
                            <h4>Personal Information</h4>
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="FirstName" 
                                       value="<?php echo htmlspecialchars($userData['FirstName'] ?? ''); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="LastName" 
                                       value="<?php echo htmlspecialchars($userData['LastName'] ?? ''); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="Email" 
                                       value="<?php echo htmlspecialchars($userData['Email']); ?>" readonly>
                            </div>
                        </div>

                       
                        <div class="form-section">
                            <h4>Academic Information</h4>
                            <div class="form-group">
                                <label for="gradeLevel">Grade Level</label>
                                <input type="text" id="gradeLevelDisplay" 
                                       value="<?php echo htmlspecialchars($userData['grade_level'] ?? 'Not Set'); ?>" readonly>
                                <select id="gradeLevel" name="grade_level" style="display: none;">
                                    <option value="">Select Grade Level</option>
                                    <option value="Grade 7">Grade 7</option>
                                    <option value="Grade 8">Grade 8</option>
                                    <option value="Grade 9">Grade 9</option>
                                    <option value="Grade 10">Grade 10</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="section">Section</label>
                                <input type="text" id="sectionDisplay" 
                                       value="<?php echo htmlspecialchars($userData['section'] ?? 'Not Set'); ?>" readonly>
                                <select id="section" name="section" style="display: none;">
                                    <option value="">Select Section</option>
                                    <?php
                                    $gradeSections = [
                                        'Grade 7' => ['Babbage', 'Byron', 'Cooper', 'Eckert', 'Kilby', 'Leibniz', 'Liscov', 'Osborne', 'Pascal', 'Rossum', 'Stallman', 'Thompson', 'Wilkes'],
                                        'Grade 8' => ['Andreessen', 'Berners', 'Brin', 'Engelbart', 'Gray', 'Hamilton', 'Mauchly', 'Turing', 'Wilson'],
                                        'Grade 9' => ['Atanasoff', 'Hollerith', 'Hopper', 'Hull', 'Iverson', 'Johansen', 'Johnson', 'Neumann', 'Page', 'Perlis'],
                                        'Grade 10' => ['Allen', 'Banatao', 'Bryce', 'Cray', 'Minsky', 'Shannon', 'Stibitz', 'Torvalds', 'Wozniak', 'Zuse']
                                    ];
                                    
                                   
                                    $currentGradeLevel = $userData['grade_level'] ?? '';
                                    
                                   
                                    if (!empty($currentGradeLevel) && isset($gradeSections[$currentGradeLevel])) {
                                        foreach ($gradeSections[$currentGradeLevel] as $sectionName) {
                                            $selected = ($userData['section'] == $sectionName) ? 'selected' : '';
                                            echo "<option value=\"$sectionName\" $selected>$sectionName</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="schoolYear">School Year</label>
                                <input type="text" id="schoolYearDisplay" 
                                       value="<?php echo htmlspecialchars($userData['school_year'] ?? 'Not Set'); ?>" readonly>
                                <select id="schoolYear" name="school_year" style="display: none;">
                                    <option value="">Select School Year</option>
                                    <option value="2024-2025">2024-2025</option>
                                    <option value="2025-2026">2025-2026</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="display: none;">
                        <button type="button" class="cancel-btn" onclick="toggleEditMode()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="save-btn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

         
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="icon icon-grade"></span>
                    <div class="stat-info">
                        <h3>Current Grade</h3>
                        <p><?php echo htmlspecialchars($userData['grade_level'] ?? 'Not Set'); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="icon icon-section"></span>
                    <div class="stat-info">
                        <h3>Section</h3>
                        <p><?php echo htmlspecialchars($userData['section'] ?? 'Not Set'); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="icon icon-calendar"></span>
                    <div class="stat-info">
                        <h3>School Year</h3>
                        <p><?php echo htmlspecialchars($userData['school_year'] ?? 'Not Set'); ?></p>
                    </div>
                </div>
            </div>

           
            <div class="dashboard-card">
                <h2>Quick Links</h2>
                <div class="quick-links">
                    <a href="profile.php" class="quick-link">
                        <span class="icon icon-user"></span>
                        <span>My Profile</span>
                    </a>
                    <a href="grades.php" class="quick-link">
                        <span class="icon icon-grades"></span>
                        <span>Grades</span>
                    </a>
                    <a href="schedule.php" class="quick-link">
                        <span class="icon icon-calendar"></span>
                        <span>Class Schedule</span>
                    </a>
                    <a href="messages.php" class="quick-link">
                        <span class="icon icon-messages"></span>
                        <span>Messages</span>
                    </a>
                </div>
            </div>

            <div class="dashboard-card activity-section">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <span class="icon icon-login"></span>
                        <div class="activity-details">
                            <p>Last login: Today at 8:30 AM</p>
                            <small>System</small>
                        </div>
                    </div>
                    <div class="activity-item">
                        <span class="icon icon-grade"></span>
                        <div class="activity-details">
                            <p>New grades posted for Mathematics</p>
                            <small>2 days ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
document.addEventListener('DOMContentLoaded', function() {
   
    const uploadBtn = document.querySelector('.upload-btn');
    if (uploadBtn) {
        uploadBtn.style.display = 'flex';
    }

    const profilePicture = document.getElementById('profilePicture');
    const previewImage = document.getElementById('previewImage');
    
    if (profilePicture) {
        profilePicture.addEventListener('change', function() {
          
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                };
                
                reader.readAsDataURL(this.files[0]);
                
             
                uploadProfilePicture(this.files[0]);
            }
        });
    }
    
    function uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append('profile_picture', file);
        
      
        const statusDiv = document.createElement('div');
        statusDiv.className = 'status-message';
        statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        document.querySelector('.profile-header').appendChild(statusDiv);
        
        fetch('update_profile_picture.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
          
            statusDiv.remove();
            
       
            const resultDiv = document.createElement('div');
            
            if (data.success) {
                resultDiv.className = 'status-message status-success';
                resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + (data.message || 'Profile picture updated successfully!');
            } else {
                resultDiv.className = 'status-message status-error';
                resultDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Failed to update profile picture.');
               
                previewImage.src = "<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>";
            }
            
            document.querySelector('.profile-header').appendChild(resultDiv);
            
         
            setTimeout(() => {
                resultDiv.remove();
            }, 3000);
        })
        .catch(error => {
          
            statusDiv.remove();
            
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'status-message status-error';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> An error occurred while uploading.';
            document.querySelector('.profile-header').appendChild(errorDiv);
            
       
            previewImage.src = "<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>";
            
           
            setTimeout(() => {
                errorDiv.remove();
            }, 3000);
            
            console.error('Error:', error);
        });
    }
});
</script>
</body>
</html>
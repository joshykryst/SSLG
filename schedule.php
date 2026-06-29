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


$timeSlots = [
    "7:30 AM - 8:30 AM",
    "8:30 AM - 9:30 AM",
    "9:30 AM - 10:30 AM",
    "10:30 AM - 11:30 AM",
    "11:30 AM - 12:30 PM",
    "12:30 PM - 1:30 PM",
    "1:30 PM - 2:30 PM",
    "2:30 PM - 3:30 PM"
];


$days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];


$gradeSections = [
    'Grade 7' => ['Babbage', 'Byron', 'Cooper', 'Eckert', 'Kilby', 'Leibniz', 'Liscov', 'Osborne', 'Pascal', 'Rossum', 'Stallman', 'Thompson', 'Wilkes'],
    'Grade 8' => ['Andreessen', 'Berners', 'Brin', 'Engelbart', 'Gray', 'Hamilton', 'Mauchly', 'Turing', 'Wilson'],
    'Grade 9' => ['Atanasoff', 'Hollerith', 'Hopper', 'Hull', 'Iverson', 'Johansen', 'Johnson', 'Neumann', 'Page', 'Perlis'],
    'Grade 10' => ['Allen', 'Banatao', 'Bryce', 'Cray', 'Minsky', 'Shannon', 'Stibitz', 'Torvalds', 'Wozniak', 'Zuse']
];


$selectedGrade = isset($_GET['grade']) ? $_GET['grade'] : ($userData ? $userData['grade_level'] : 'Grade 7');
$selectedSection = isset($_GET['section']) ? $_GET['section'] : ($userData ? $userData['section'] : $gradeSections[$selectedGrade][0]);


$schedulesQuery = "SELECT * FROM class_schedules WHERE grade_level='$selectedGrade' AND section_name='$selectedSection' ORDER BY time_slot, day_of_week";
$schedulesResult = mysqli_query($conn, $schedulesQuery);


$scheduleGrid = [];
foreach ($timeSlots as $timeSlot) {
    $scheduleGrid[$timeSlot] = [];
    foreach ($days as $day) {
        $scheduleGrid[$timeSlot][$day] = [
            'id' => '',
            'subject' => '',
            'teacher' => '',
            'room' => '',
            'class_type' => 'regular'
        ];
    }
}

if ($schedulesResult) {
    while ($row = mysqli_fetch_assoc($schedulesResult)) {
        $scheduleGrid[$row['time_slot']][$row['day_of_week']] = [
            'id' => $row['id'],
            'subject' => $row['subject'],
            'teacher' => $row['teacher'],
            'room' => $row['room'],
            'class_type' => $row['class_type']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>">
    <style>
        .schedule-container {
            margin-top: 20px;
            overflow-x: auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .filter-form select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
        }

        .filter-form button {
            padding: 8px 15px;
            background-color: #1a237e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table th {
            background-color: #1a237e;
            color: white;
            padding: 12px;
            text-align: center;
            font-weight: 500;
            position: sticky;
            top: 0;
        }

        .schedule-table td {
            border: 1px solid #e0e0e0;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
            height: 80px;
        }

        .schedule-table .time-column {
            background-color: #f5f5f5;
            font-weight: 500;
            width: 140px;
        }

        .schedule-cell {
            position: relative;
            height: 100%;
            min-height: 80px;
        }

        .schedule-content {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 8px;
        }

        .subject-name {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .teacher-name {
            font-size: 0.85rem;
            color: #555;
        }

        .room-number {
            font-size: 0.8rem;
            color: #777;
            margin-top: auto;
        }

        .class-regular {
            background-color: #e3f2fd;
        }

        .class-laboratory {
            background-color: #e8f5e9;
        }

        .class-workshop {
            background-color: #fff3e0;
        }

        .class-elective {
            background-color: #f3e5f5;
        }
        
        .print-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .print-btn, .download-btn {
            background-color: #1a237e;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .print-btn:hover, .download-btn:hover {
            background-color: #303f9f;
        }
        
        .schedule-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .schedule-header h2 {
            margin-bottom: 5px;
        }
        
        .schedule-header p {
            color: #555;
        }
        
        @media print {
            .main-nav, .sidebar, .filter-form, .print-buttons, footer {
                display: none !important;
            }
            
            .main-content {
                margin: 0;
                padding: 0;
            }
            
            .schedule-container {
                box-shadow: none;
            }
            
            body {
                padding-top: 0;
            }
        }
    </style>
</head>
<body>
   
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="indexs.php" class="nav-brand">
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
                    <a href="contacts.php">Contact</a>
                    <?php if($isLoggedIn): ?>
                    
                        
                    <?php endif; ?>
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
                    <a href="profile.php"><span class="icon icon-user"></span>Profile</a>
                    <a href="grades.php"><span class="icon icon-grades"></span>Grades</a>
                    <a href="schedule.php" class="active"><span class="icon icon-calendar"></span>Schedule</a>
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
                <h1>Class Schedule</h1>
                <p>View your weekly class schedule</p>
            </div>
            
            
            <form action="schedule.php" method="GET" class="filter-form">
                <div>
                    <label for="grade">Grade Level:</label>
                    <select name="grade" id="grade" onchange="updateSections(this.value)">
                        <?php foreach (array_keys($gradeSections) as $grade): ?>
                            <option value="<?php echo $grade; ?>" <?php echo ($grade === $selectedGrade) ? 'selected' : ''; ?>>
                                <?php echo $grade; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="section">Section:</label>
                    <select name="section" id="section">
                        <?php foreach ($gradeSections[$selectedGrade] as $section): ?>
                            <option value="<?php echo $section; ?>" <?php echo ($section === $selectedSection) ? 'selected' : ''; ?>>
                                <?php echo $section; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">View Schedule</button>
            </form>
            
           
            <div class="print-buttons">
                <button onclick="printSchedule()" class="print-btn">
                    <i class="fas fa-print"></i> Print Schedule
                </button>
                <button onclick="downloadSchedule()" class="download-btn">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
            
           
            <div class="schedule-container">
                <div class="schedule-header">
                    <h2><?php echo $selectedGrade; ?> - Section <?php echo $selectedSection; ?></h2>
                    <p>Weekly Class Schedule</p>
                </div>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Time Slot</th>
                            <?php foreach ($days as $day): ?>
                                <th><?php echo $day; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scheduleGrid as $timeSlot => $daySlots): ?>
                            <tr>
                                <td class="time-column"><?php echo $timeSlot; ?></td>
                                <?php foreach ($days as $day): ?>
                                    <?php 
                                    $cellData = $daySlots[$day];
                                    $cellClass = 'class-' . $cellData['class_type'];
                                    ?>
                                    <td>
                                        <div class="schedule-cell">
                                            <?php if (!empty($cellData['subject'])): ?>
                                                <div class="schedule-content <?php echo $cellClass; ?>">
                                                    <div class="subject-name"><?php echo htmlspecialchars($cellData['subject']); ?></div>
                                                    <div class="teacher-name"><?php echo htmlspecialchars($cellData['teacher']); ?></div>
                                                    <div class="room-number">Room: <?php echo htmlspecialchars($cellData['room']); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="dashboard.js"></script>
    <script>
        
        const gradeSections = <?php echo json_encode($gradeSections); ?>;
        
    
        function updateSections(grade) {
            const sectionDropdown = document.getElementById('section');
            sectionDropdown.innerHTML = '';
            
            gradeSections[grade].forEach(section => {
                const option = document.createElement('option');
                option.value = section;
                option.textContent = section;
                sectionDropdown.appendChild(option);
            });
        }
        
        
        function printSchedule() {
            window.print();
        }
        
    
        function downloadSchedule() {
            alert('PDF download functionality would be implemented here.');
          
        }
        
     
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            
            
            sidebar.classList.toggle('active');
            
            
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
                    body.classList.add('sidebar-open');
                } else {
                    overlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
            }
        }
        
        
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    toggleMenu();
                }
            }
        });
    </script>
</body>
</html>
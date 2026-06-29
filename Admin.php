<?php
session_start();
require 'Config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$isTeacher = false;
$teacherSubject = '';

if(!empty($_SESSION["Admin_ID"])) {
    $Admin_ID = $_SESSION["Admin_ID"];
    

    $teacherQuery = "SELECT * FROM table_admin WHERE Admin_ID = ? AND role = 'teacher'";
    $stmt = $conn->prepare($teacherQuery);
    $stmt->bind_param("i", $Admin_ID);
    $stmt->execute();
    $teacherResult = $stmt->get_result();
    
    if($teacherResult && $teacherResult->num_rows > 0) {
        $teacherData = $teacherResult->fetch_assoc();
        $isTeacher = true;
        $teacherSubject = $teacherData['subject']; 
        
    
        $adminData = array(
            'Username' => $teacherData['Username'],
            'profile_picture' => $teacherData['profile_picture'] ?? 'admin-default.jpg'
        );
    } else {
       
        $isTeacher = false;
        $teacherSubject = '';
        
      
        $adminQuery = "SELECT * FROM table_admin WHERE Admin_ID = ?";
        $stmt = $conn->prepare($adminQuery);
        $stmt->bind_param("i", $Admin_ID);
        $stmt->execute();
        $adminResult = $stmt->get_result();
        
        if($adminResult && $adminResult->num_rows > 0) {
            $adminData = $adminResult->fetch_assoc();
            
            if(!isset($adminData['profile_picture']) || empty($adminData['profile_picture'])) {
                $adminData['profile_picture'] = 'admin-default.jpg';
            }
        } else {
            
            $adminData = array(
                'Username' => 'Administrator',
                'profile_picture' => 'admin-default.jpg'
            );
        }
    }
} else {
    
    $isTeacher = false;
    $teacherSubject = '';
    $adminData = array(
        'Username' => 'Guest',
        'profile_picture' => 'admin-default.jpg'
    );
}


if (isset($_GET['delete'])) {
    // Log for debugging
    error_log("Delete request received for user ID: " . $_GET['delete']);
    
    if (!$isTeacher) {
        $id = (int) $_GET['delete'];
        
        // Start by disabling foreign key checks to force deletion
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Check if the user exists first
            $check_user = $conn->prepare("SELECT User_ID FROM table_user WHERE User_ID = ?");
            $check_user->bind_param("i", $id);
            $check_user->execute();
            $user_result = $check_user->get_result();
            
            if ($user_result->num_rows == 0) {
                throw new Exception("User does not exist");
            }
            
            // Delete all related records using direct queries instead of prepared statements
            // to minimize potential syntax issues
            
            // Explicitly delete from all possible related tables
            $conn->query("DELETE FROM grades WHERE User_ID = $id");
            error_log("Grades deleted for user ID: " . $id);
            
            // Try to delete from other potential related tables
            $tables_to_check = ['attendance', 'user_activities', 'submissions', 'assignments', 'student_logs'];
            
            foreach ($tables_to_check as $table) {
                // Check if table exists first to avoid errors
                $table_check = $conn->query("SHOW TABLES LIKE '$table'");
                if ($table_check && $table_check->num_rows > 0) {
                    $conn->query("DELETE FROM $table WHERE User_ID = $id");
                    error_log("Records deleted from $table for user ID: " . $id);
                }
            }
            
            // Now delete the user record itself
            $delete_result = $conn->query("DELETE FROM table_user WHERE User_ID = $id");
            
            if (!$delete_result) {
                throw new Exception("Failed to delete user record: " . $conn->error);
            }
            
            error_log("User deleted with ID: " . $id);
            
            // Commit transaction
            $conn->commit();
            
            // Restore foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
            
            // Redirect with success message
            header("Location: admin.php?success=delete");
            exit();
            
        } catch (Exception $e) {
            // Log detailed error
            error_log("Error deleting user: " . $e->getMessage());
            
            // Rollback transaction
            $conn->rollback();
            
            // Restore foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
            
            // Redirect with error message
            header("Location: admin.php?error=delete&message=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        // Teachers don't have delete permission
        header("Location: admin.php?error=unauthorized");
        exit();
    }
}


if (isset($_GET['delete_grade'])) {
    $user_id = (int)$_GET['user_id'];
    $subject = mysqli_real_escape_string($conn, $_GET['subject']);
    $quarter = (int)$_GET['quarter'];
    
   
    if ($isTeacher && $subject !== $teacherSubject) {
     
        header("Location: admin.php?error=unauthorized_subject");
        exit();
    }
    
    $delete_query = "DELETE FROM grades WHERE User_ID = ? AND subject_name = ? AND quarter = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("isi", $user_id, $subject, $quarter);
    
    if ($delete_stmt->execute()) {
        header("Location: admin.php?success=grade_delete");
    } else {
        header("Location: admin.php?error=grade_delete");
    }
    exit();
}

if (isset($_POST['update'])) {
    $id = (int) $_POST['User_ID'];
    $username = mysqli_real_escape_string($conn, $_POST['Username']);
    $firstname = mysqli_real_escape_string($conn, $_POST['FirstName']);
    $lastname = mysqli_real_escape_string($conn, $_POST['LastName']);
    $birthday = mysqli_real_escape_string($conn, $_POST['Birthday']);
    $gender = mysqli_real_escape_string($conn, $_POST['Gender']);
    $lrn = mysqli_real_escape_string($conn, $_POST['LRN']);
    $email = mysqli_real_escape_string($conn, $_POST['Email']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $grade_level = mysqli_real_escape_string($conn, $_POST['grade_level']);
    $school_year = mysqli_real_escape_string($conn, $_POST['school_year']);
    
   
    $check_lrn = $conn->prepare("SELECT User_ID FROM table_user WHERE LRN = ? AND User_ID != ?");
    $check_lrn->bind_param("si", $lrn, $id);
    $check_lrn->execute();
    $lrn_result = $check_lrn->get_result();
    
    if ($lrn_result->num_rows > 0) {
      
        header("Location: admin.php?error=duplicate_lrn");
        exit();
    }
    
    $sql = "UPDATE table_user SET 
            Username=?, FirstName=?, LastName=?, Birthday=?, 
            Gender=?, LRN=?, Email=?, section=?, 
            grade_level=?, school_year=?
            WHERE User_ID=?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssi", 
        $username, $firstname, $lastname, $birthday,
        $gender, $lrn, $email, $section,
        $grade_level, $school_year, $id
    );
    
    if ($stmt->execute()) {
        header("Location: admin.php?success=1");
    } else {
        header("Location: admin.php?error=1");
    }
    exit();
}


$current_quarter = isset($_GET['quarter']) ? (int)$_GET['quarter'] : 1;


if (isset($_POST['update_grade'])) {
    $user_id = (int) $_POST['User_ID'];
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $grade = (float) $_POST['grade'];
    $quarter = (int) $_POST['quarter'];
    
   
    if ($isTeacher && $subject_name !== $teacherSubject) {
        
        header("Location: admin.php?error=unauthorized_subject");
        exit();
    }
    

    $check_query = "SELECT * FROM grades WHERE User_ID = ? AND subject_name = ? AND quarter = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("isi", $user_id, $subject_name, $quarter);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
       
        $update_query = "UPDATE grades SET grade = ? WHERE User_ID = ? AND subject_name = ? AND quarter = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("disi", $grade, $user_id, $subject_name, $quarter);
        
        if (!$update_stmt->execute()) {
            echo "Error updating grade: " . $update_stmt->error;
        } else {
            header("Location: admin.php?success=grade_update");
            exit();
        }
    } else {
       
        $insert_query = "INSERT INTO grades (User_ID, subject_name, grade, quarter) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("isdi", $user_id, $subject_name, $grade, $quarter);
        
        if (!$insert_stmt->execute()) {
            echo "Error inserting grade: " . $insert_stmt->error;
        } else {
            header("Location: admin.php?success=grade_insert");
            exit();
        }
    }
}


$quarters = array(1, 2, 3, 4);
$subjects = array(
    'Math',
    'Science',
    'English',
    'Filipino',
    'MAPEH',
    'TLE',
    'AP',
    'ESP'
);


$gradesQuery = "SELECT g.*, u.FirstName, u.LastName,
               CASE 
                  WHEN g.grade >= 75 THEN 'Passed'
                  ELSE 'Failed'
               END as status
               FROM grades g 
               JOIN table_user u ON g.User_ID = u.User_ID";


if ($isTeacher) {
    $gradesQuery .= " WHERE g.subject_name = '" . mysqli_real_escape_string($conn, $teacherSubject) . "'";
}

$gradesQuery .= " ORDER BY u.LastName, u.FirstName, g.quarter";

$grades = $conn->query($gradesQuery);

$result = $conn->query("SELECT * FROM table_user");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CMRICTHS</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="admin.js" defer></script>
</head>
<body>
 
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="nav-brand">
                    <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
                    <div class="brand-text">
                        <h1>CMRICTHS</h1>
                        <p>Admin Dashboard</p>
                    </div>
                </a>
            </div>
            <div class="nav-right">
                
                <a href="./adminschedules.php" class="admin-nav-btn">
                    <i class="fas fa-calendar-alt"></i> Manage Schedules
                </a>
                <a href="NewsAdmin.php" class="admin-nav-btn">
                    <i class="fas fa-newspaper"></i> Manage News
                </a>
                
                <a href="admingallery.php" class="admin-nav-btn">
                    <i class="fas fa-images"></i> Manage Gallery
                </a>
                
                <a href="admincontacts.php" class="admin-nav-btn">
                    <i class="fas fa-envelope"></i> Contact Feedback
                    <?php
                    
                    $unread_query = "SELECT COUNT(*) as unread FROM contact_feedback WHERE status = 'new'";
                    $unread_result = $conn->query($unread_query);
                    if ($unread_result && $unread_row = $unread_result->fetch_assoc()) {
                        $unread_count = $unread_row['unread'];
                        if ($unread_count > 0) {
                            echo '<span class="notification-badge">' . $unread_count . '</span>';
                        }
                    }
                    ?>
                </a>

                <?php if ($isTeacher): ?>
                <span class="teacher-subject-badge">
                    <i class="fas fa-book"></i> <?php echo htmlspecialchars($teacherSubject); ?> Teacher
                </span>
                <?php endif; ?>
              
                <div class="nav-profile">
                    <div class="profile-dropdown">
                        <div class="profile-trigger" onclick="toggleAdminMenu()">
                            <img src="profile-admin.jpg" alt="Admin" class="profile-avatar">
                            <span class="profile-name">Administrator</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="profile-menu" id="adminProfileMenu">
                            <div class="profile-header">
                                <img src="<?php echo $adminData['profile_picture']; ?>" alt="Admin" class="profile-picture">
                                <div class="profile-info">
                                    <h4><?php echo htmlspecialchars($adminData['Username']); ?></h4>
                                    <p><?php echo $isTeacher ? htmlspecialchars($teacherSubject) . ' Teacher' : 'System Administrator'; ?></p>
                                </div>
                            </div>
                            <div class="profile-links">
                                <a href="admin_settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <a href="Logout.php" class="logout-link">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="dashboard-container">
            
            <div id="alertContainer">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                            if ($_GET['success'] === 'delete') {
                                echo "User deleted successfully.";
                            } elseif ($_GET['success'] === 'grade_delete') {
                                echo "Grade record deleted successfully.";
                            } elseif ($_GET['success'] === 'grade_update' || $_GET['success'] === 'grade_insert') {
                                echo "Grade saved successfully.";
                            } else {
                                echo "Changes saved successfully.";
                            }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?php
        if ($_GET['error'] === 'duplicate_lrn') {
            echo "This LRN already belongs to another student. Please use a unique LRN.";
        } elseif ($_GET['error'] === 'unauthorized_subject') {
            echo "You are not authorized to modify grades for this subject.";
        } elseif ($_GET['error'] === 'delete') {
            echo "Failed to delete the student record. This might be because of database constraints or missing permissions.";
        } elseif ($_GET['error'] === 'grade_delete') {
            echo "Failed to delete the grade record. Please try again.";
        } else {
            echo "An error occurred. Please try again.";
        }
        ?>
    </div>
<?php endif; ?>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-users"></i> Student Records</h2>
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search students...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>LRN</th>
                                <th>Section</th>
                                <th>Grade Level</th>
                                <th>School Year</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= htmlspecialchars($row['User_ID']) ?></td>
                                <td><?= htmlspecialchars($row['Username']) ?></td>
                                <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                                <td><?= htmlspecialchars($row['Email']) ?></td>
                                <td><?= htmlspecialchars($row['LRN']) ?></td>
                                <td><?= htmlspecialchars($row['section'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['grade_level'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['school_year'] ?? '') ?></td>
                                <td class="action-buttons">
                                    <button type="button" class="edit-btn" onclick="openEditModal(
                                        <?= (int)$row['User_ID'] ?>, 
                                        '<?= addslashes($row['Username'] ?? '') ?>', 
                                        '<?= addslashes($row['FirstName'] ?? '') ?>', 
                                        '<?= addslashes($row['LastName'] ?? '') ?>', 
                                        '<?= addslashes($row['Email'] ?? '') ?>', 
                                        '<?= addslashes($row['LRN'] ?? '') ?>', 
                                        '<?= addslashes($row['section'] ?? '') ?>', 
                                        '<?= addslashes($row['grade_level'] ?? '') ?>', 
                                        '<?= addslashes($row['school_year'] ?? '') ?>',
                                        '<?= addslashes($row['Birthday'] ?? '') ?>',
                                        '<?= addslashes($row['Gender'] ?? 'Male') ?>'
                                    )">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="delete-btn" onclick="confirmDelete(<?= (int)$row['User_ID'] ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

          
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-graduation-cap"></i> Grade Management</h2>
                    <div class="filter-box">
                        <select id="gradeLevelFilter" class="filter-select-compact" onchange="updateSectionFilter()">
                            <option value="">All Grades</option>
                            <option value="Grade 7">Grade 7</option>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>
                        </select>
                        <select id="sectionFilter" class="filter-select-compact">
                            <option value="">All Sections</option>
                           
                        </select>
                        <select id="quarterFilter" class="filter-select-compact" onchange="filterByQuarter(this.value)">
                            <option value="">All Quarters</option>
                            <option value="1">Q1</option>
                            <option value="2">Q2</option>
                            <option value="3">Q3</option>
                            <option value="4">Q4</option>
                        </select>
                    </div>
                </div>
                
                <div class="grade-management">
                  
                    <div class="grade-averages-section">
                        <h3><i class="fas fa-chart-line"></i> Student Grade Averages</h3>
                        <div class="table-responsive">
                            <table class="admin-table averages-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Grade</th>
                                        <th>Section</th>
                                        <?php foreach($quarters as $quarter): ?>
                                            <th>Q<?= $quarter ?></th>
                                        <?php endforeach; ?>
                                        <th>Overall</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                 
                                    $students_query = "SELECT u.User_ID, u.FirstName, u.LastName, u.grade_level, u.section
                                                      FROM table_user u
                                                      ORDER BY u.grade_level, u.section, u.LastName, u.FirstName";
                                    $students_result = $conn->query($students_query);
                                    
                                    while($student = $students_result->fetch_assoc()):
                                    
                                        $quarterly_averages = [];
                                        $overall_grades = [];
                                        
                                        foreach($quarters as $quarter) {
                                            $avg_query = "SELECT AVG(grade) as average 
                                                         FROM grades 
                                                         WHERE User_ID = {$student['User_ID']} AND quarter = {$quarter}";
                                            $avg_result = $conn->query($avg_query)->fetch_assoc();
                                            $quarterly_averages[$quarter] = $avg_result['average'] ? number_format($avg_result['average'], 2) : '-';
                                            
                                            if($avg_result['average']) {
                                                $overall_grades[] = $avg_result['average'];
                                            }
                                        }
                                        
                                     
                                        $overall_average = count($overall_grades) > 0 ? number_format(array_sum($overall_grades) / count($overall_grades), 2) : '-';
                                        $status = $overall_average !== '-' ? ($overall_average >= 75 ? 'Passed' : 'Failed') : '-';
                                        
                                  
                                        $has_grades = false;
                                        foreach($quarterly_averages as $average) {
                                            if($average !== '-') {
                                                $has_grades = true;
                                                break;
                                            }
                                        }
                                    ?>
                                    <tr data-grade-level="<?= htmlspecialchars($student['grade_level'] ?? '') ?>" 
                                        data-section="<?= htmlspecialchars($student['section'] ?? '') ?>"
                                        data-has-grades="<?= $has_grades ? 'true' : 'false' ?>">
                                        <td><?= htmlspecialchars($student['LastName'] ?? '') . ', ' . htmlspecialchars($student['FirstName'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['grade_level'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['section'] ?? '') ?></td>
                                        <?php foreach($quarters as $quarter): ?>
                                            <td class="grade-cell"><?= $quarterly_averages[$quarter] ?></td>
                                        <?php endforeach; ?>
                                        <td class="overall-grade"><?= $overall_average ?></td>
                                        <td>
                                            <?php if($status !== '-'): ?>
                                                <span class="status-badge <?= $status === 'Passed' ? 'grade-passing' : 'grade-failing' ?>">
                                                    <?= $status ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
            
                    <div class="add-grade-form">
                        <h3>Add/Update Student Grade</h3>
                        <form method="POST" action="admin.php" class="grade-input-form">
                            <div class="form-row">
                                <div class="form-group" style="max-width: 120px;">
                                    <label for="grade_level_select">Grade:</label>
                                    <select id="grade_level_select" class="filter-select-compact" onchange="updateStudentOptions()">
                                        <option value="">All</option>
                                        <option value="Grade 7">Grade 7</option>
                                        <option value="Grade 8">Grade 8</option>
                                        <option value="Grade 9">Grade 9</option>
                                        <option value="Grade 10">Grade 10</option>
                                    </select>
                                </div>
                                <div class="form-group" style="max-width: 120px;">
                                    <label for="section_select">Section:</label>
                                    <select id="section_select" class="filter-select-compact" onchange="updateStudentOptions()">
                                        <option value="">All</option>
                                       
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="student_select">Student:</label>
                                    <select id="student_select" name="User_ID" required>
                                        <option value="">-- Select Student --</option>
                                        <?php
                                        $students = $conn->query("SELECT User_ID, FirstName, LastName, section, grade_level FROM table_user ORDER BY grade_level, LastName, FirstName");
                                        while($student = $students->fetch_assoc()):
                                        ?>
                                        <option value="<?= $student['User_ID'] ?>" 
                                               data-section="<?= htmlspecialchars($student['section'] ?? '') ?>"
                                               data-grade-level="<?= htmlspecialchars($student['grade_level'] ?? '') ?>">
                                            <?= htmlspecialchars($student['LastName'] ?? '') . ', ' . htmlspecialchars($student['FirstName'] ?? '') ?> 
                                            (<?= htmlspecialchars($student['grade_level'] ?? '') ?> - <?= htmlspecialchars($student['section'] ?? '') ?>)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="subject_input">Subject:</label>
                                    <select id="subject_input" name="subject_name" required <?php echo $isTeacher ? 'disabled' : ''; ?>>
                                        <?php 
                                        if ($isTeacher) {
                                            
                                            echo "<option value=\"" . htmlspecialchars($teacherSubject) . "\">" . htmlspecialchars($teacherSubject) . "</option>";
                                          
                                            echo "<input type=\"hidden\" name=\"subject_name\" value=\"" . htmlspecialchars($teacherSubject) . "\">";
                                        } else {
                                          
                                            foreach($subjects as $subject): 
                                                echo "<option value=\"" . htmlspecialchars($subject) . "\">" . htmlspecialchars($subject) . "</option>";
                                            endforeach;
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="grade_input">Grade:</label>
                                    <input type="number" id="grade_input" name="grade" min="0" max="100" step="0.01" required>
                                </div>
                                <div class="form-group" style="max-width: 120px;">
                                    <label for="quarter_input">Quarter:</label>
                                    <select id="quarter_input" name="quarter" required>
                                        <option value="1">Q1</option>
                                        <option value="2">Q2</option>
                                        <option value="3">Q3</option>
                                        <option value="4">Q4</option>
                                    </select>
                                </div>
                                <div class="form-group" style="min-width: auto;">
                                    <button type="submit" name="update_grade" class="save-btn">Save Grade</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                   
                    <div class="table-responsive">
                        <table class="admin-table grades-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Subject</th>
                                    <th>Grade</th>
                                    <th>Quarter</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($grades->num_rows > 0): ?>
                                    <?php while($grade = $grades->fetch_assoc()): ?>
                                        <?php 
                                        
                                        $studentInfo = $conn->query("SELECT grade_level, section FROM table_user WHERE User_ID = " . $grade['User_ID'])->fetch_assoc();
                                        ?>
                                        <tr data-quarter="<?= $grade['quarter'] ?>" 
                                            data-grade-level="<?= htmlspecialchars($studentInfo['grade_level'] ?? '') ?>" 
                                            data-section="<?= htmlspecialchars($studentInfo['section'] ?? '') ?>">
                                            <td>
                                                <div class="student-name">
                                                    <?= htmlspecialchars($grade['LastName'] ?? '') . ', ' . htmlspecialchars($grade['FirstName'] ?? '') ?>
                                                    <small><?= htmlspecialchars($studentInfo['grade_level'] ?? '') ?> - <?= htmlspecialchars($studentInfo['section'] ?? '') ?></small>
                                                </div>
                                            </td>
                                            <td><span class="subject-badge"><?= htmlspecialchars($grade['subject_name'] ?? '') ?></span></td>
                                            <td><strong><?= number_format($grade['grade'] ?? 0, 2) ?></strong></td>
                                            <td><span class="quarter-badge">Q<?= $grade['quarter'] ?></span></td>
                                            <td>
                                                <span class="status-badge <?= ($grade['grade'] ?? 0) >= 75 ? 'grade-passing' : 'grade-failing' ?>">
                                                    <?= $grade['status'] ?? 'N/A' ?>
                                                </span>
                                            </td>
                                            <td class="action-buttons">
                                                <button class="edit-btn" onclick="openGradeEditForm(
                                                    <?= (int)$grade['User_ID'] ?>, 
                                                    '<?= addslashes($grade['subject_name'] ?? '') ?>', 
                                                    <?= (float)($grade['grade'] ?? 0) ?>, 
                                                    <?= (int)$grade['quarter'] ?>
                                                )">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="delete-btn" onclick="confirmGradeDelete(
                                                    <?= (int)$grade['User_ID'] ?>, 
                                                    '<?= addslashes($grade['subject_name'] ?? '') ?>', 
                                                    <?= (int)$grade['quarter'] ?>,
                                                    '<?= addslashes(($grade['FirstName'] ?? '') . ' ' . ($grade['LastName'] ?? '')) ?>'
                                                )">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-table-message">
                                            <div>
                                                <i class="fas fa-graduation-cap"></i>
                                                <p>No grades have been entered yet</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="action-button-container" style="margin: 20px 0;">
        <a href="admincontacts.php" class="action-button">
            <i class="fas fa-envelope"></i> View Contact Feedback
            <?php
            
            $unread_query = "SELECT COUNT(*) as unread FROM contact_feedback WHERE status = 'new'";
            $unread_result = $conn->query($unread_query);
            if ($unread_result && $unread_row = $unread_result->fetch_assoc()) {
                $unread_count = $unread_row['unread'];
                if ($unread_count > 0) {
                    echo '<span class="notification-badge">' . $unread_count . '</span>';
                }
            }
            ?>
        </a>
    </div>

    <div id="editForm" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Student Record</h2>
                <span class="close" onclick="closeEditForm()">&times;</span>
            </div>
            <form method="POST" action="admin.php" class="edit-form">
                <input type="hidden" id="User_ID" name="User_ID">
                <div class="form-grid">
                    <div class="form-section">
                        <h3>Personal Information</h3>
                        <div class="form-group">
                            <label for="Username">Username</label>
                            <input type="text" id="Username" name="Username" required>
                        </div>
                        <div class="form-group">
                            <label for="FirstName">First Name</label>
                            <input type="text" id="FirstName" name="FirstName" required>
                        </div>
                        <div class="form-group">
                            <label for="LastName">Last Name</label>
                            <input type="text" id="LastName" name="LastName" required>
                        </div>
                        <div class="form-group">
                            <label for="Email">Email</label>
                            <input type="email" id="Email" name="Email" required>
                        </div>
                        <div class="form-group">
                            <label for="Birthday">Birthday</label>
                            <input type="date" id="Birthday" name="Birthday">
                        </div>
                        <div class="form-group">
                            <label for="Gender">Gender</label>
                            <select id="Gender" name="Gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Academic Information</h3>
                        <div class="form-group">
                            <label for="LRN">LRN</label>
                            <input type="text" id="LRN" name="LRN" required>
                        </div>
                        <div class="form-group">
                            <label for="grade_level">Grade Level</label>
                            <select id="grade_level" name="grade_level" required onchange="updateSectionOptions()">
                                <option value="Grade 7">Grade 7</option>
                                <option value="Grade 8">Grade 8</option>
                                <option value="Grade 9">Grade 9</option>
                                <option value="Grade 10">Grade 10</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="section">Section</label>
                            <select id="section" name="section" required>
                               
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="school_year">School Year</label>
                            <select id="school_year" name="school_year" required>
                                <option value="2023-2024">2023-2024</option>
                                <option value="2024-2025">2024-2025</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" onclick="closeEditForm()" class="cancel-btn">Cancel</button>
                    <button type="submit" name="update" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>


    <div id="gradeForm" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Grade</h2>
                <span class="close-btn" onclick="closeGradeForm()">&times;</span>
            </div>
            <form method="POST" action="admin.php" class="edit-form">
                <input type="hidden" id="student_id" name="User_ID">
                <div class="form-grid">
                    <div class="form-section">
                        <div class="form-group">
                            <label>Subject</label>
                            <select id="subject_name" name="subject_name" required <?php echo $isTeacher ? 'disabled' : ''; ?>>
                                <?php 
                                if ($isTeacher) {
                                    
                                    echo "<option value=\"" . htmlspecialchars($teacherSubject) . "\">" . htmlspecialchars($teacherSubject) . "</option>";
                                    
                                    echo "<input type=\"hidden\" name=\"subject_name\" value=\"" . htmlspecialchars($teacherSubject) . "\">";
                                } else {
                                    
                                    echo "<option value=\"Math\">Math</option>";
                                    echo "<option value=\"Science\">Science</option>";
                                    echo "<option value=\"English\">English</option>";
                                    echo "<option value=\"Filipino\">Filipino</option>";
                                    echo "<option value=\"MAPEH\">MAPEH</option>";
                                    echo "<option value=\"TLE\">TLE</option>";
                                    echo "<option value=\"AP\">AP</option>";
                                    echo "<option value=\"ESP\">ESP</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Grade</label>
                            <input type="number" id="grade" name="grade" step="0.01" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="form-section">
                        <div class="form-group">
                            <label>Quarter</label>
                            <select id="quarter" name="quarter" required>
                                <option value="1">1st Quarter</option>
                                <option value="2">2nd Quarter</option>
                                <option value="3">3rd Quarter</option>
                                <option value="4">4th Quarter</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeGradeForm()">Cancel</button>
                    <button type="submit" name="update_grade" class="save-btn">Save Grade</button>
                </div>
            </form>
        </div>
    </div>

    <script src="admin.js"></script>
    <script>
function confirmDelete(userId) {
    if (confirm("Are you sure you want to delete this student? This will also delete all their grades and cannot be undone.")) {
        // Show loading indicator
        document.body.style.cursor = 'wait';
        
        // Disable the clicked button to prevent multiple submissions
        event.target.disabled = true;
        
        // Create an AJAX request to handle deletion
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'delete_student.php?id=' + userId, true);
        
        xhr.onload = function() {
            if (this.status >= 200 && this.status < 300) {
                try {
                    var response = JSON.parse(this.responseText);
                    if (response.success) {
                        // Success - reload the page to show updated student list
                        window.location.href = 'admin.php?success=delete';
                    } else {
                        // Error with message
                        alert("Error: " + response.message);
                        document.body.style.cursor = 'default';
                        event.target.disabled = false;
                    }
                } catch(e) {
                    // Reload anyway if response isn't valid JSON
                    window.location.href = 'admin.php?success=delete';
                }
            } else {
                // Server error
                alert("Server error occurred. Please try again.");
                document.body.style.cursor = 'default';
                event.target.disabled = false;
            }
        };
        
        xhr.onerror = function() {
            alert("Request failed. Please try again.");
            document.body.style.cursor = 'default';
            event.target.disabled = false;
        };
        
        xhr.send();
    }
}

function confirmGradeDelete(userId, subject, quarter, studentName) {
    if (confirm("Are you sure you want to delete the " + subject + " grade for " + studentName + ", Quarter " + quarter + "?")) {
        window.location.href = "Admin.php?delete_grade=1&user_id=" + userId + "&subject=" + subject + "&quarter=" + quarter;
    }
}
</script>
</body>
</html>

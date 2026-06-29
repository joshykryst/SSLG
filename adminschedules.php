<?php
session_start();
require 'Config.php';

if(empty($_SESSION["Admin_ID"])) {
    header("Location: Login.php");
    exit();
}

$Admin_ID = $_SESSION["Admin_ID"];
$teacherCheck = $conn->prepare("SELECT * FROM table_admin WHERE Admin_ID = ? AND role = 'teacher'");
$teacherCheck->bind_param("i", $Admin_ID);
$teacherCheck->execute();
$result = $teacherCheck->get_result();

if($result->num_rows > 0) {
    $teacherData = $result->fetch_assoc();
    $isTeacher = true;
    $teacherSubject = $teacherData['subject'];
} else {
    $isTeacher = false;
}

if($isTeacher) {
    $editableSubjects = array($teacherSubject);
} else {
    $editableSubjects = array('Math', 'Science', 'English', 'Filipino', 'MAPEH', 'TLE', 'AP', 'ESP');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$isLoggedIn = true; 
$adminData = array(
    'Username' => 'Admin',
    'profile_picture' => 'admin-default.jpg'
);

if(!empty($_SESSION["Admin_ID"])){
    $Admin_ID = $_SESSION["Admin_ID"];
    $result = mysqli_query($conn, "SELECT * FROM table_admin WHERE Admin_ID = '" . mysqli_real_escape_string($conn, $Admin_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $adminData = mysqli_fetch_assoc($result);
        $isLoggedIn = true;
    }
}

$message = '';
$messageType = '';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add' || $action === 'edit') {
        $grade = mysqli_real_escape_string($conn, $_POST['grade']);
        $section = mysqli_real_escape_string($conn, $_POST['section']);
        $timeSlot = mysqli_real_escape_string($conn, $_POST['time_slot']);
        $day = mysqli_real_escape_string($conn, $_POST['day']);
        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
        $teacher = mysqli_real_escape_string($conn, $_POST['teacher']);
        $room = mysqli_real_escape_string($conn, $_POST['room']);
        $classType = mysqli_real_escape_string($conn, $_POST['class_type']);
        
        if ($action === 'add') {
           
            $checkQuery = "SELECT * FROM class_schedules WHERE grade_level='$grade' AND section_name='$section' 
                          AND time_slot='$timeSlot' AND day_of_week='$day'";
            $checkResult = mysqli_query($conn, $checkQuery);
            
            if (mysqli_num_rows($checkResult) > 0) {
                
                $updateQuery = "UPDATE class_schedules SET subject='$subject', teacher='$teacher', 
                              room='$room', class_type='$classType' WHERE grade_level='$grade' 
                              AND section_name='$section' AND time_slot='$timeSlot' AND day_of_week='$day'";
                
                if (mysqli_query($conn, $updateQuery)) {
                    $message = "Schedule updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating schedule: " . mysqli_error($conn);
                    $messageType = "error";
                }
            } else {
                
                $insertQuery = "INSERT INTO class_schedules (grade_level, section_name, time_slot, day_of_week, 
                              subject, teacher, room, class_type) VALUES ('$grade', '$section', '$timeSlot', 
                              '$day', '$subject', '$teacher', '$room', '$classType')";
                
                if (mysqli_query($conn, $insertQuery)) {
                    $message = "Schedule added successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error adding schedule: " . mysqli_error($conn);
                    $messageType = "error";
                }
            }
        } elseif ($action === 'edit') {
            $id = mysqli_real_escape_string($conn, $_POST['id']);
            
            $updateQuery = "UPDATE class_schedules SET grade_level='$grade', section_name='$section', 
                          time_slot='$timeSlot', day_of_week='$day', subject='$subject', teacher='$teacher', 
                          room='$room', class_type='$classType' WHERE id=$id";
            
            if (mysqli_query($conn, $updateQuery)) {
                $message = "Schedule updated successfully!";
                $messageType = "success";
            } else {
                $message = "Error updating schedule: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    } elseif ($action === 'delete') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        $deleteQuery = "DELETE FROM class_schedules WHERE id=$id";
        
        if (mysqli_query($conn, $deleteQuery)) {
            $message = "Schedule deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Error deleting schedule: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
}

$defaultTimeSlots = [
    'standard' => [
        "7:30 AM - 8:30 AM",
        "8:30 AM - 9:30 AM",
        "9:30 AM - 10:30 AM",
        "10:30 AM - 11:30 AM",
        "11:30 AM - 12:30 PM",
        "12:30 PM - 1:30 PM",
        "1:30 PM - 2:30 PM",
        "2:30 PM - 3:30 PM"
    ],
    'grade7_regular' => [
        "6:30 AM - 7:00 AM", 
        "7:00 AM - 7:50 AM",
        "7:50 AM - 8:40 AM",
        "8:40 AM - 9:30 AM",
        "9:30 AM - 9:45 AM", 
        "9:45 AM - 10:35 AM",
        "10:35 AM - 11:25 AM",
        "11:25 AM - 12:15 PM",
        "12:15 PM - 1:00 PM", 
        "1:00 PM - 1:50 PM",
        "1:50 PM - 2:40 PM",
        "2:40 PM - 3:30 PM",
        "3:30 PM - 4:00 PM"
    ],
    'grade7_friday' => [
        "6:30 AM - 7:00 AM", 
        "7:00 AM - 7:45 AM",
        "7:45 AM - 8:30 AM",
        "8:30 AM - 9:15 AM",
        "9:15 AM - 9:30 AM", 
        "9:30 AM - 10:15 AM",
        "10:15 AM - 11:00 AM",
        "11:00 AM - 11:45 AM",
        "11:45 AM - 12:30 PM",
        "1:15 PM - 2:00 PM",
        "2:00 PM - 2:45 PM",
        "2:45 PM - 3:15 PM"
    ]
];

$timeSlotTemplates = $defaultTimeSlots;

$timeSlotTemplatesQuery = "SELECT * FROM schedule_timeslots ORDER BY template_name";
$timeSlotTemplatesResult = mysqli_query($conn, $timeSlotTemplatesQuery);

if ($timeSlotTemplatesResult && mysqli_num_rows($timeSlotTemplatesResult) > 0) {
    while ($row = mysqli_fetch_assoc($timeSlotTemplatesResult)) {
        $templateName = $row['template_name'];
        $timeSlots = explode(',', $row['time_slots']);
        $timeSlotTemplates[$templateName] = $timeSlots;
    }
}
 
$selectedTemplate = isset($_GET['template']) ? $_GET['template'] : 'standard';
if (!array_key_exists($selectedTemplate, $timeSlotTemplates)) {
    $selectedTemplate = 'standard';
}

$timeSlots = $timeSlotTemplates[$selectedTemplate];


if (!is_array($timeSlots) || empty($timeSlots)) {

    $timeSlots = $defaultTimeSlots['standard'];
    
    
    error_log("Warning: \$timeSlots was null or not an array. Using default time slots instead.");
}


if (isset($_POST['save_template'])) {
    $templateName = mysqli_real_escape_string($conn, $_POST['template_name']);
    
    
    error_log("Template name: " . $templateName);
    

    if (empty($templateName)) {
        $message = "Error: Template name cannot be empty.";
        $messageType = "error";
    } else if (!isset($_POST['new_time_slots']) || !is_array($_POST['new_time_slots'])) {
     
        error_log("No time slots provided or invalid format");
        $message = "Error: No time slots provided.";
        $messageType = "error";
    } else {
        $newTimeSlots = $_POST['new_time_slots'];
        error_log("Number of time slots: " . count($newTimeSlots));
       
        $validTimeSlots = [];
        foreach ($newTimeSlots as $slot) {
            $trimmedSlot = trim($slot);
            if (!empty($trimmedSlot)) {
                $validTimeSlots[] = $trimmedSlot;
            }
        }
        
        error_log("Number of valid time slots: " . count($validTimeSlots));
        
        if (count($validTimeSlots) > 0) {
            $timeSlotsString = mysqli_real_escape_string($conn, implode(',', $validTimeSlots));
            
            
            $checkQuery = "SELECT * FROM schedule_timeslots WHERE template_name = '$templateName'";
            $checkResult = mysqli_query($conn, $checkQuery);
            
            if (!$checkResult) {
                error_log("Database error: " . mysqli_error($conn));
                $message = "Database error: " . mysqli_error($conn);
                $messageType = "error";
            } else if (mysqli_num_rows($checkResult) > 0) {
               
                $updateQuery = "UPDATE schedule_timeslots SET time_slots = '$timeSlotsString' WHERE template_name = '$templateName'";
                if (mysqli_query($conn, $updateQuery)) {
                    $message = "Time slot template updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating template: " . mysqli_error($conn);
                    $messageType = "error";
                }
            } else {
               
                $insertQuery = "INSERT INTO schedule_timeslots (template_name, time_slots) VALUES ('$templateName', '$timeSlotsString')";
                if (mysqli_query($conn, $insertQuery)) {
                    $message = "New time slot template created successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error creating template: " . mysqli_error($conn);
                    $messageType = "error";
                }
            }
            
           
            $timeSlotTemplates[$templateName] = $validTimeSlots;
        } else {
            $message = "Error: Time slots cannot be empty.";
            $messageType = "error";
        }
    }
}


if (isset($_GET['delete_template'])) {
    $templateName = mysqli_real_escape_string($conn, $_GET['delete_template']);
   
    if ($templateName !== 'standard' && $templateName !== 'grade7_regular' && $templateName !== 'grade7_friday') {
        $deleteQuery = "DELETE FROM schedule_timeslots WHERE template_name = '$templateName'";
        if (mysqli_query($conn, $deleteQuery)) {
            $message = "Template deleted successfully!";
            $messageType = "success";
            
          
            unset($timeSlotTemplates[$templateName]);
        } else {
            $message = "Error deleting template: " . mysqli_error($conn);
            $messageType = "error";
        }
    } else {
        $message = "Cannot delete default templates.";
        $messageType = "error";
    }
}

$days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];


$gradeSections = [
    'Grade 7' => ['Babbage', 'Byron', 'Cooper', 'Eckert', 'Kilby', 'Leibniz', 'Liscov', 'Osborne', 'Pascal', 'Rossum', 'Stallman', 'Thompson', 'Wilkes'],
    'Grade 8' => ['Andreessen', 'Berners', 'Brin', 'Engelbart', 'Gray', 'Hamilton', 'Mauchly', 'Turing', 'Wilson'],
    'Grade 9' => ['Atanasoff', 'Hollerith', 'Hopper', 'Hull', 'Iverson', 'Johansen', 'Johnson', 'Neumann', 'Page', 'Perlis'],
    'Grade 10' => ['Allen', 'Banatao', 'Bryce', 'Cray', 'Minsky', 'Shannon', 'Stibitz', 'Torvalds', 'Wozniak', 'Zuse']
];


$selectedGrade = isset($_GET['grade']) && array_key_exists($_GET['grade'], $gradeSections) ? $_GET['grade'] : 'Grade 7';
$selectedSection = isset($_GET['section']) && in_array($_GET['section'], $gradeSections[$selectedGrade]) ? 
    $_GET['section'] : $gradeSections[$selectedGrade][0];


$schedulesQuery = "SELECT * FROM class_schedules WHERE grade_level='$selectedGrade' AND section_name='$selectedSection' ORDER BY time_slot, day_of_week";
$schedulesResult = mysqli_query($conn, $schedulesQuery);



if (!is_array($timeSlots) || empty($timeSlots)) {
  
    $timeSlots = $defaultTimeSlots['standard'];
    

    error_log("Warning: \$timeSlots was null or not an array. Using default time slots instead.");
}


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
       
        if (isset($scheduleGrid[$row['time_slot']]) && isset($scheduleGrid[$row['time_slot']][$row['day_of_week']])) {
            $scheduleGrid[$row['time_slot']][$row['day_of_week']] = [
                'id' => $row['id'],
                'subject' => $row['subject'],
                'teacher' => $row['teacher'],
                'room' => $row['room'],
                'class_type' => $row['class_type']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management - CMRICTHS Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin.css?v=<?php echo time(); ?>">
    <style>
        
        .actions-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
            justify-content: flex-end;
        }
        
        .action-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .add-btn {
            background-color: #4caf50;
            color: white;
        }
        
        .add-btn:hover {
            background-color: #388e3c;
        }
        
        .template-btn {
            background-color: #3498db;
            color: white;
        }
        
        .template-btn:hover {
            background-color: #2980b9;
        }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 25px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .filter-form div {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-form label {
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        
        .filter-form select {
            min-width: 180px;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-family: 'Poppins', sans-serif;
        }
        
        .filter-form button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }
        
        .filter-form button[type="submit"] {
            background-color: #1a237e;
            color: white;
        }
        
        .filter-form button[type="submit"]:hover {
            background-color: #0d1757;
        }
       
        .admin-main {
            margin-top: 90px;
            padding: 20px;
        }
        
        .schedule-container {
            margin-top: 20px;
            overflow-x: auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1px; 
        }
        
       
        .admin-nav-btn.active {
            background-color: #1a237e;
            color: white;
        }
        
       
        .page-header {
            margin-bottom: 25px;
        }
        
        .page-header h1 {
            color: #1a237e;
            margin-bottom: 8px;
            font-size: 28px;
        }
        
        .page-header p {
            color: #666;
            margin: 0;
        }
        
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 25px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .filter-form div {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-form label {
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        
        .filter-form select {
            min-width: 180px;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-family: 'Poppins', sans-serif;
        }
        
        .filter-form button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }
        
        .filter-form button[type="submit"] {
            background-color: #1a237e;
            color: white;
        }
        
        .filter-form button[type="submit"]:hover {
            background-color: #0d1757;
        }
        
        .add-btn {
            background-color: #4caf50;
            color: white;
        }
        
        .add-btn:hover {
            background-color: #388e3c;
        }
        
        
        .schedule-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            min-width: 900px;
        }
        
        .schedule-table th, .schedule-table td {
            border: 1px solid #e0e0e0;
            padding: 0;
            text-align: center;
            height: 120px;
        }
        
        .schedule-table th {
            background-color: #1a237e;
            color: white;
            padding: 12px 8px;
            font-weight: 500;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .schedule-table th:first-child {
            width: 140px;
        }
        
        .time-column {
            background-color: #f5f7fa;
            font-weight: 500;
            padding: 12px 8px !important;
            color: #333;
            font-size: 14px;
            width: 140px;
        }
        
        .schedule-cell {
            position: relative;
            height: 100%;
            min-height: 100px;
        }
        
        .schedule-content {
            padding: 12px 8px;
            height: 100%;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s;
            box-sizing: border-box;
            height: 120px;
            width: 100%;
        }
        
        .schedule-content:hover {
            background-color: #f5f7fa;
        }
        
        .subject-name {
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 5px;
            font-size: 15px;
        }
        
        .teacher-name {
            font-size: 13px;
            color: #555;
            margin-bottom: 3px;
        }
        
        .room-number {
            font-size: 12px;
            color: #777;
        }
        
      
        .class-regular {
            border-left: 4px solid #1a237e;
        }
        
        .class-laboratory {
            border-left: 4px solid #f57c00;
            background-color: #fff3e0;
        }
        
        .class-workshop {
            border-left: 4px solid #388e3c;
            background-color: #e8f5e9;
        }
        
        .class-elective {
            border-left: 4px solid #7b1fa2;
            background-color: #f3e5f5;
        }
        
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 50px auto;
            padding: 0;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .modal-header {
            background-color: #1a237e;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-title {
            margin: 0;
            font-size: 20px;
            font-weight: 500;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        
        .modal form {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-family: 'Poppins', sans-serif;
        }
        
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        
        @media (max-width: 992px) {
            .schedule-table th:first-child,
            .time-column {
                width: 120px;
            }
        }
        
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-form div {
                width: 100%;
            }
            
            .filter-form select {
                width: 100%;
                min-width: unset;
            }
        }
        
       
        
        .empty-cell {
            color: #9e9e9e;
            font-style: italic;
            font-size: 14px;
            font-weight: 500;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .schedule-content:hover .empty-cell {
            opacity: 1;
            color: #1a237e;
        }
        
        
        .schedule-table {
            min-width: 900px; 
        }
        
        
        .schedule-table th, 
        .schedule-table td {
            height: 120px;
        }
        
      
        .schedule-content {
            box-sizing: border-box;
            height: 120px;
            width: 100%;
        }
        
        
    
       
        .template-selection {
            margin-bottom: 25px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .template-selection h3 {
            color: #1a237e;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .template-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .template-btn {
            padding: 8px 16px;
            background-color: #f0f4ff;
            color: #1a237e;
            text-decoration: none;
            border: 1px solid #e0e6ff;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .template-btn:hover {
            background-color: #e3e9ff;
        }
        
        .template-btn.active {
            background-color: #1a237e;
            color: white;
            border-color: #1a237e;
        }
        
        .add-template-btn {
            background-color: #4caf50;
            color: white;
            border: none;
        }
        
        .add-template-btn:hover {
            background-color: #388e3c;
        }
        
        
        .time-slots-container {
            margin-bottom: 15px;
        }
        
        .time-slot-field {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }
        
        .time-slot-field input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .remove-time-slot {
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .template-actions {
            display: flex;
            justify-content: space-between;
        }
        
        .save-template-btn {
            background-color: #1a237e;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 500;
            font-size: 16px;
        }
        
        .save-template-btn:hover {
            background-color: #0d1757;
        }
        
        
        .template-btn-group {
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .template-actions {
            position: absolute;
            top: -8px;
            right: -8px;
            display: none;
        }
        
        .template-btn-group:hover .template-actions {
            display: flex;
            gap: 5px;
        }
        
        .template-action-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: none;
            background-color: #1a237e;
            color: white;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .template-action-btn.delete-btn {
            background-color: #f44336;
        }
        
        .template-action-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="Admin.php" class="nav-brand">
                    <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
                    <div class="brand-text">
                        <h1>CMRICTHS</h1>
                        <p>Admin Dashboard</p>
                    </div>
                </a>
            </div>
            <div class="nav-right">
               
                <a href="./adminschedules.php" class="admin-nav-btn active">
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
                            <img src="<?php echo $adminData['profile_picture'] ?? 'admin-default.jpg'; ?>" 
                                 alt="Admin" class="profile-avatar">
                            <span class="profile-name"><?php echo htmlspecialchars($adminData['Username'] ?? 'Administrator'); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="profile-menu" id="adminProfileMenu">
                            <div class="profile-header">
                                <img src="<?php echo $adminData['profile_picture'] ?? 'admin-default.jpg'; ?>" 
                                     alt="Admin" class="profile-picture">
                                <div class="profile-info">
                                    <h4><?php echo htmlspecialchars($adminData['Username'] ?? 'Admin User'); ?></h4>
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

 
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?php echo $adminData['profile_picture'] ?? 'admin-default.jpg'; ?>" alt="Admin Profile" class="sidebar-logo">
            <h3>Welcome, <?php echo htmlspecialchars($adminData['Username']); ?></h3>
            <button class="close-btn" onclick="toggleMenu()">
                <span class="icon icon-close"></span>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">
                <h4>Dashboard</h4>
                <a href="Admin.php"><span class="icon icon-dashboard"></span>Dashboard</a>
                <a href="admin/students.php"><span class="icon icon-users"></span>Students</a>
                <a href="admin/teachers.php"><span class="icon icon-teachers"></span>Teachers</a>
                <a href="adminschedules.php" class="active"><span class="icon icon-calendar"></span>Schedules</a>
                <a href="admin/grades.php"><span class="icon icon-grades"></span>Grades</a>
                <a href="admin/announcements.php"><span class="icon icon-announcement"></span>Announcements</a>
            </div>
            <div class="nav-section">
                <h4>Settings</h4>
                <a href="admin/profile.php"><span class="icon icon-user"></span>Profile</a>
                <a href="Logout.php" class="logout-btn"><span class="icon icon-logout"></span>Logout</a>
            </div>
        </nav>
    </div>
    
   
    <main class="admin-main">
        <div class="admin-container">
            <div class="page-header">
                <h1>Class Schedule Management</h1>
                <p>Manage class schedules for all grade levels and sections</p>
            </div>
            
            <?php if (!empty($message)): ?>
            <div class="message message-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Action buttons moved to the top -->
            <div class="actions-container">
                <button type="button" class="action-btn add-btn" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Schedule Item
                </button>
                <button type="button" class="action-btn template-btn" onclick="openTemplateModal()">
                    <i class="fas fa-clock"></i> Create Time Template
                </button>
            </div>
            
            
            <div class="template-selection">
                <h3>Time Slot Templates</h3>
                
                <div class="template-buttons">
                    <?php foreach ($timeSlotTemplates as $templateKey => $slots): ?>
                        <div class="template-btn-group">
                            <a href="adminschedules.php?grade=<?php echo $selectedGrade; ?>&section=<?php echo $selectedSection; ?>&template=<?php echo $templateKey; ?>" 
                               class="template-btn <?php echo ($templateKey === $selectedTemplate) ? 'active' : ''; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $templateKey)); ?>
                            </a>
                            <div class="template-actions">
                                <button type="button" class="template-action-btn" 
                                        onclick="loadTemplateForEdit('<?php echo $templateKey; ?>')" 
                                        title="Edit Template">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <?php if ($templateKey !== 'standard' && $templateKey !== 'grade7_regular' && $templateKey !== 'grade7_friday'): ?>
                                    <button type="button" class="template-action-btn delete-btn" 
                                            onclick="confirmDeleteTemplate('<?php echo $templateKey; ?>')" 
                                            title="Delete Template">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Filter form without the additional buttons -->
            <form action="adminschedules.php" method="GET" class="filter-form">
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
                <!-- Keep the current template when changing grade/section -->
                <input type="hidden" name="template" value="<?php echo $selectedTemplate; ?>">
                <button type="submit">View Schedule</button>
            </form>
            
           
            <div class="schedule-container">
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
                                    $cellClass = !empty($cellData['class_type']) ? 'class-' . $cellData['class_type'] : 'class-regular';
                                    $hasData = !empty($cellData['subject']);
                                    ?>
                                    <td>
                                        <?php if ($hasData): ?>
                                            <div class="schedule-content <?php echo $cellClass; ?>" onclick="openEditModal(
                                                '<?php echo $cellData['id']; ?>', 
                                                '<?php echo $selectedGrade; ?>', 
                                                '<?php echo $selectedSection; ?>', 
                                                '<?php echo $timeSlot; ?>', 
                                                '<?php echo $day; ?>', 
                                                '<?php echo addslashes(htmlspecialchars($cellData['subject'])); ?>', 
                                                '<?php echo addslashes(htmlspecialchars($cellData['teacher'])); ?>', 
                                                '<?php echo addslashes(htmlspecialchars($cellData['room'])); ?>', 
                                                '<?php echo $cellData['class_type']; ?>'
                                            )">
                                                <div class="subject-name"><?php echo htmlspecialchars($cellData['subject']); ?></div>
                                                <div class="teacher-name"><?php echo htmlspecialchars($cellData['teacher']); ?></div>
                                                <div class="room-number">Room: <?php echo htmlspecialchars($cellData['room']); ?></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="schedule-content" onclick="openAddModalWithData(
                                                '<?php echo $selectedGrade; ?>', 
                                                '<?php echo $selectedSection; ?>', 
                                                '<?php echo $timeSlot; ?>', 
                                                '<?php echo $day; ?>'
                                            )">
                                                <div class="empty-cell">+ Add Class</div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Schedule Item</h3>
                <button class="close-modal" onclick="closeAddModal()">&times;</button>
            </div>
            <form action="adminschedules.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="add-grade">Grade Level:</label>
                    <select class="form-control" id="add-grade" name="grade" required onchange="updateModalSections(this.value, 'add-section')">
                        <?php foreach (array_keys($gradeSections) as $grade): ?>
                            <option value="<?php echo $grade; ?>"><?php echo $grade; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-section">Section:</label>
                    <select class="form-control" id="add-section" name="section" required>
                        <?php foreach ($gradeSections['Grade 7'] as $section): ?>
                            <option value="<?php echo $section; ?>"><?php echo $section; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-time-slot">Time Slot:</label>
                    <select class="form-control" id="add-time-slot" name="time_slot" required>
                        <?php foreach ($timeSlots as $timeSlot): ?>
                            <option value="<?php echo $timeSlot; ?>"><?php echo $timeSlot; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-day">Day:</label>
                    <select class="form-control" id="add-day" name="day" required>
                        <?php foreach ($days as $day): ?>
                            <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-subject">Subject:</label>
                    <input type="text" class="form-control" id="add-subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="add-teacher">Teacher:</label>
                    <input type="text" class="form-control" id="add-teacher" name="teacher" required>
                </div>
                <div class="form-group">
                    <label for="add-room">Room:</label>
                    <input type="text" class="form-control" id="add-room" name="room" required>
                </div>
                <div class="form-group">
                    <label for="add-class-type">Class Type:</label>
                    <select class="form-control" id="add-class-type" name="class_type">
                        <option value="regular">Regular</option>
                        <option value="laboratory">Laboratory</option>
                        <option value="workshop">Workshop</option>
                        <option value="elective">Elective</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="add-btn">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
    

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Schedule Item</h3>
                <button class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form action="adminschedules.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit-id" name="id">
                <div class="form-group">
                    <label for="edit-grade">Grade Level:</label>
                    <select class="form-control" id="edit-grade" name="grade" required onchange="updateModalSections(this.value, 'edit-section')">
                        <?php foreach (array_keys($gradeSections) as $grade): ?>
                            <option value="<?php echo $grade; ?>"><?php echo $grade; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-section">Section:</label>
                    <select class="form-control" id="edit-section" name="section" required>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-time-slot">Time Slot:</label>
                    <select class="form-control" id="edit-time-slot" name="time_slot" required>
                        <?php foreach ($timeSlots as $timeSlot): ?>
                            <option value="<?php echo $timeSlot; ?>"><?php echo $timeSlot; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-day">Day:</label>
                    <select class="form-control" id="edit-day" name="day" required>
                        <?php foreach ($days as $day): ?>
                            <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-subject">Subject:</label>
                    <input type="text" class="form-control" id="edit-subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="edit-teacher">Teacher:</label>
                    <input type="text" class="form-control" id="edit-teacher" name="teacher" required>
                </div>
                <div class="form-group">
                    <label for="edit-room">Room:</label>
                    <input type="text" class="form-control" id="edit-room" name="room" required>
                </div>
                <div class="form-group">
                    <label for="edit-class-type">Class Type:</label>
                    <select class="form-control" id="edit-class-type" name="class_type">
                        <option value="regular">Regular</option>
                        <option value="laboratory">Laboratory</option>
                        <option value="workshop">Workshop</option>
                        <option value="elective">Elective</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="add-btn">Update Schedule</button>
                </div>
            </form>
            <form action="adminschedules.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this schedule item?');" style="margin-top:15px;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete-id" name="id">
                <button type="submit" class="btn btn-danger" style="background-color: #f44336; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%;">Delete Schedule Item</button>
            </form>
        </div>
    </div>

    
    <div id="templateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create/Edit Time Slot Template</h3>
                <button class="close-modal" onclick="closeTemplateModal()">&times;</button>
            </div>
            <form action="adminschedules.php?grade=<?php echo $selectedGrade; ?>&section=<?php echo $selectedSection; ?>" method="POST">
                <div class="form-group">
                    <label for="template_name">Template Name:</label>
                    <input type="text" class="form-control" id="template_name" name="template_name" required>
                    <small>Use descriptive names like "Grade 7 Regular" or "High School"</small>
                </div>
                
                <div class="form-group">
                    <label>Time Slots:</label>
                    <div class="time-slots-container" id="timeSlotsContainer">
                        
                    </div>
                    <button type="button" class="add-btn" onclick="addTimeSlotField()">
                        <i class="fas fa-plus"></i> Add Time Slot
                    </button>
                </div>
                
                <div class="form-group template-actions">
                    <button type="submit" name="save_template" class="save-template-btn">
                        <i class="fas fa-save"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>

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
    
    
    function updateModalSections(grade, targetId) {
        const sectionDropdown = document.getElementById(targetId);
        if (!sectionDropdown) return;
        
        sectionDropdown.innerHTML = '';
        
        if (gradeSections[grade]) {
            gradeSections[grade].forEach(section => {
                const option = document.createElement('option');
                option.value = section;
                option.textContent = section;
                sectionDropdown.appendChild(option);
            });
        }
    }
    
   
    function toggleAdminMenu() {
        const profileMenu = document.getElementById('adminProfileMenu');
        profileMenu.classList.toggle('active');
        
       
        document.addEventListener('click', function(event) {
            const isClickInside = event.target.closest('.profile-dropdown');
            if (!isClickInside && profileMenu.classList.contains('active')) {
                profileMenu.classList.remove('active');
            }
        }, { once: true });
    }
    
   
    function openAddModal() {
        const modal = document.getElementById('addModal');
        if (!modal) return;
        
        modal.style.display = 'block';
        document.getElementById('add-grade').value = '<?php echo $selectedGrade; ?>';
        updateModalSections('<?php echo $selectedGrade; ?>', 'add-section');
        
        const sectionSelect = document.getElementById('add-section');
        if (sectionSelect) {
            sectionSelect.value = '<?php echo $selectedSection; ?>';
        }
        
       
        window.addEventListener('click', function closeOnClickOutside(event) {
            if (event.target === modal) {
                closeAddModal();
                window.removeEventListener('click', closeOnClickOutside);
            }
        });
    }
    
    
    function openAddModalWithData(grade, section, timeSlot, day) {
        const modal = document.getElementById('addModal');
        if (!modal) return;
        
        modal.style.display = 'block';
        
        const gradeSelect = document.getElementById('add-grade');
        if (gradeSelect) gradeSelect.value = grade;
        
        updateModalSections(grade, 'add-section');
        
        const sectionSelect = document.getElementById('add-section');
        if (sectionSelect) sectionSelect.value = section;
        
        const timeSlotSelect = document.getElementById('add-time-slot');
        if (timeSlotSelect) timeSlotSelect.value = timeSlot;
        
        const daySelect = document.getElementById('add-day');
        if (daySelect) daySelect.value = day;
        
        
        window.addEventListener('click', function closeOnClickOutside(event) {
            if (event.target === modal) {
                closeAddModal();
                window.removeEventListener('click', closeOnClickOutside);
            }
        });
    }
    
   
    function closeAddModal() {
        const modal = document.getElementById('addModal');
        if (modal) modal.style.display = 'none';
    }
    
    function openEditModal(id, grade, section, timeSlot, day, subject, teacher, room, classType) {
        const modal = document.getElementById('editModal');
        if (!modal) return;
        
        modal.style.display = 'block';
        
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-grade').value = grade;
        updateModalSections(grade, 'edit-section');
        document.getElementById('edit-section').value = section;
        document.getElementById('edit-time-slot').value = timeSlot;
        document.getElementById('edit-day').value = day;
        document.getElementById('edit-subject').value = subject;
        document.getElementById('edit-teacher').value = teacher;
        document.getElementById('edit-room').value = room;
        document.getElementById('edit-class-type').value = classType;
        document.getElementById('delete-id').value = id;
        
        
        window.addEventListener('click', function closeOnClickOutside(event) {
            if (event.target === modal) {
                closeEditModal();
                window.removeEventListener('click', closeOnClickOutside);
            }
        });
    }
    
    
    function closeEditModal() {
        const modal = document.getElementById('editModal');
        if (modal) modal.style.display = 'none';
    }
    
   
    function openTemplateModal(templateName = '', timeSlots = []) {
        const modal = document.getElementById('templateModal');
        if (!modal) return;
        
        modal.style.display = 'block';
        
       
        document.getElementById('template_name').value = templateName;
        document.getElementById('timeSlotsContainer').innerHTML = '';
        
        
        if (timeSlots.length > 0) {
            timeSlots.forEach(slot => {
                addTimeSlotField(slot);
            });
        } else {
            
            addTimeSlotField();
            addTimeSlotField();
        }
        
       
        window.addEventListener('click', function closeOnClickOutside(event) {
            if (event.target === modal) {
                closeTemplateModal();
                window.removeEventListener('click', closeOnClickOutside);
            }
        });
    }
    
    
    function closeTemplateModal() {
        const modal = document.getElementById('templateModal');
        if (modal) modal.style.display = 'none';
    }
    
  
    function addTimeSlotField(value = '') {
        const container = document.getElementById('timeSlotsContainer');
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'time-slot-field';
        
        fieldDiv.innerHTML = `
            <input type="text" name="new_time_slots[]" class="form-control" 
                   placeholder="e.g. 7:30 AM - 8:30 AM" value="${value}">
            <button type="button" class="remove-time-slot" onclick="removeTimeSlotField(this)">
                &times;
            </button>
        `;
        
        container.appendChild(fieldDiv);
    }
    
   
    function removeTimeSlotField(button) {
        const container = document.getElementById('timeSlotsContainer');
        const fieldDiv = button.parentNode;
        container.removeChild(fieldDiv);
        
        
        if (container.children.length === 0) {
            addTimeSlotField();
        }
    }
    
    
    function editTemplate(templateName, timeSlots) {
        openTemplateModal(templateName, timeSlots);
    }
    
    
    function confirmDeleteTemplate(templateName) {
        if (templateName === 'standard' || templateName === 'grade7_regular' || templateName === 'grade7_friday') {
            alert('Cannot delete default templates.');
            return;
        }
        
        if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
            window.location.href = `adminschedules.php?grade=<?php echo $selectedGrade; ?>&section=<?php echo $selectedSection; ?>&delete_template=${templateName}`;
        }
    }
    
   
    function loadTemplateForEdit(templateName) {
        const templates = <?php echo json_encode($timeSlotTemplates); ?>;
        if (templates[templateName]) {
            openTemplateModal(templateName, templates[templateName]);
        }
    }
    
    
    function toggleMenu() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) sidebar.classList.toggle('active');
    }
    
    
    document.addEventListener('DOMContentLoaded', function() {
        
        updateModalSections('<?php echo $selectedGrade; ?>', 'edit-section');
    });
</script>
</body>
</html>
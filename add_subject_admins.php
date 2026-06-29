<?php

session_start();
require 'Config.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);


$subjectAdmins = [
    [
        'username' => 'scienceadmin',
        'email' => 'science@school.edu',
        'password' => 'science123',
        'role' => 'teacher',
        'subject' => 'Science'
    ],
    [
        'username' => 'mathadmin',
        'email' => 'math@school.edu',
        'password' => 'math123',
        'role' => 'teacher',
        'subject' => 'Math'
    ],
    [
        'username' => 'englishadmin',
        'email' => 'english@school.edu',
        'password' => 'english123',
        'role' => 'teacher',
        'subject' => 'English'
    ],
    [
        'username' => 'filipinoadmin',
        'email' => 'filipino@school.edu',
        'password' => 'filipino123',
        'role' => 'teacher',
        'subject' => 'Filipino'
    ],
    [
        'username' => 'mapehadmin',
        'email' => 'mapeh@school.edu',
        'password' => 'mapeh123',
        'role' => 'teacher',
        'subject' => 'MAPEH'
    ],
    [
        'username' => 'tleadmin',
        'email' => 'tle@school.edu',
        'password' => 'tle123',
        'role' => 'teacher',
        'subject' => 'TLE'
    ],
    [
        'username' => 'apadmin',
        'email' => 'ap@school.edu',
        'password' => 'ap123',
        'role' => 'teacher',
        'subject' => 'AP'
    ],
    [
        'username' => 'espadmin',
        'email' => 'esp@school.edu',
        'password' => 'esp123',
        'role' => 'teacher',
        'subject' => 'ESP'
    ]
];

echo "<h1>Creating Subject Admin Accounts</h1>";

$checkSubjectColumn = $conn->query("SHOW COLUMNS FROM `table_admin` LIKE 'subject'");
if($checkSubjectColumn->num_rows == 0) {

    $conn->query("ALTER TABLE `table_admin` ADD COLUMN `subject` VARCHAR(50) DEFAULT NULL");
    echo "<p>Added 'subject' column to table_admin table.</p>";
}


$checkRoleColumn = $conn->query("SHOW COLUMNS FROM `table_admin` LIKE 'role'");
if($checkRoleColumn->num_rows == 0) {

    $conn->query("ALTER TABLE `table_admin` ADD COLUMN `role` VARCHAR(20) DEFAULT 'admin'");
    echo "<p>Added 'role' column to table_admin table.</p>";
}


foreach ($subjectAdmins as $admin) {

    $checkUsername = $conn->prepare("SELECT Admin_ID FROM table_admin WHERE Username = ?");
    $checkUsername->bind_param("s", $admin['username']);
    $checkUsername->execute();
    $usernameResult = $checkUsername->get_result();
    
    if ($usernameResult->num_rows > 0) {

        $row = $usernameResult->fetch_assoc();
        $admin_id = $row['Admin_ID'];
        

        $hashed_password = password_hash($admin['password'], PASSWORD_BCRYPT);
        
  
        $updateAdmin = $conn->prepare("UPDATE table_admin SET 
            Email = ?,
            Password = ?,
            role = ?,
            subject = ?
            WHERE Admin_ID = ?");
        
        $updateAdmin->bind_param("ssssi", 
            $admin['email'],
            $hashed_password,
            $admin['role'],
            $admin['subject'],
            $admin_id
        );
        
        if ($updateAdmin->execute()) {
            echo "<p>Updated {$admin['username']} with subject {$admin['subject']}</p>";
        } else {
            echo "<p>Error updating {$admin['username']}: " . $conn->error . "</p>";
        }
    } else {
       
       
        $hashed_password = password_hash($admin['password'], PASSWORD_BCRYPT);
        
       
        $insertAdmin = $conn->prepare("INSERT INTO table_admin 
            (Username, Email, Password, role, subject) 
            VALUES (?, ?, ?, ?, ?)");
        
        $insertAdmin->bind_param("sssss", 
            $admin['username'],
            $admin['email'],
            $hashed_password,
            $admin['role'],
            $admin['subject']
        );
        
        if ($insertAdmin->execute()) {
            echo "<p>Created new admin {$admin['username']} with subject {$admin['subject']}</p>";
        } else {
            echo "<p>Error creating {$admin['username']}: " . $conn->error . "</p>";
        }
    }
}

echo "<p>All admin accounts processed.</p>";
echo "<p><a href='Login.php'>Go to Login Page</a></p>";
?>
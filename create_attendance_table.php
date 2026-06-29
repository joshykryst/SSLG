<?php
// Connect to the database
require 'Config.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create the attendance table
$sql = "CREATE TABLE IF NOT EXISTS `attendance` (
  `attendance_id` INT(11) NOT NULL AUTO_INCREMENT,
  `User_ID` INT(11) NOT NULL,
  `date` DATE NOT NULL,
  `status` ENUM('present', 'absent', 'late', 'excused') NOT NULL DEFAULT 'present',
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendance_id`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `table_user` (`User_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Attendance table created successfully";
} else {
    echo "Error creating attendance table: " . $conn->error;
}

// Close connection
$conn->close();
?>
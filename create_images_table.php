<?php
require 'Config.php';

try {
   
    $sql1 = "CREATE TABLE IF NOT EXISTS `images` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `filename` varchar(255) NOT NULL,
        `description` text,
        `user_id` int(11) NOT NULL,
        `upload_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

   
    $sql2 = "ALTER TABLE `images`
        ADD CONSTRAINT `fk_user_images` 
        FOREIGN KEY (`user_id`) REFERENCES `table_user` (`User_ID`) 
        ON DELETE CASCADE;";

    if ($conn->query($sql1) === TRUE) {
        echo "Images table created successfully<br>";
        if ($conn->query($sql2) === TRUE) {
            echo "Foreign key constraint added successfully";
        } else {
            echo "Error adding foreign key: " . $conn->error;
        }
    } else {
        echo "Error creating table: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
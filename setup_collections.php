<?php
require 'Config.php';


$sql1 = "CREATE TABLE IF NOT EXISTS collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    cover_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";


$sql2 = "CREATE TABLE IF NOT EXISTS collection_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_id INT NOT NULL,
    image_id INT NOT NULL,
    sort_order INT DEFAULT 0,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";


if ($conn->query($sql1) === TRUE) {
    echo "Collections table created successfully<br>";
} else {
    echo "Error creating collections table: " . $conn->error . "<br>";
}

if ($conn->query($sql2) === TRUE) {
    echo "Collection images table created successfully<br>";
} else {
    echo "Error creating collection images table: " . $conn->error . "<br>";
}


$sample = "INSERT INTO collections (name, description, date, cover_image) VALUES 
    ('Graduation Day', 'Photos from our graduation ceremony', '2024-06-15', 'placeholder_graduation.jpg'),
    ('School Field Trip', 'Our trip to the science museum', '2024-04-20', 'placeholder_trip.jpg'),
    ('Annual Sports Day', 'Highlights from our annual sports competition', '2024-01-25', 'placeholder_sports.jpg'),
    ('Science Fair', 'Student science projects showcase', '2024-03-12', 'placeholder_science.jpg')";

if ($conn->query($sample) === TRUE) {
    echo "Sample collections added successfully<br>";
} else {
    echo "Error adding sample collections: " . $conn->error . "<br>";
}


$conn->close();

echo "<p>Setup complete. <a href='admingallery.php'>Go to Admin Gallery</a></p>";
?>
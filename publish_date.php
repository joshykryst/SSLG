<?php
$create_table_query = "CREATE TABLE IF NOT EXISTS news_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    author VARCHAR(100) NOT NULL,
    publish_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    featured TINYINT(1) DEFAULT 0
)";
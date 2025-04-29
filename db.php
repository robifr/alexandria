<?php
ini_set('memory_limit', '512M'); // RAM for large BLOBs.
ini_set('max_execution_time', 300); // Up to 5 minutes per request.

$host = "localhost";
$user = "root";
$pass = "";

// Connect without specifying a database.
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
  die("DB connection failed: " . $conn->connect_error);
}

// Check if the database exists.
$db_check = $conn->query("SHOW DATABASES LIKE 'alexandria'");
if ($db_check->num_rows == 0) {
  $conn->query("CREATE DATABASE IF NOT EXISTS alexandria");
}

// Now, select the 'alexandria' database.
$conn->select_db('alexandria');

// Check if 'users' table exists.
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check->num_rows == 0) {
  $conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");
}

// Check if 'books' table exists.
$book_check = $conn->query("SHOW TABLES LIKE 'books'");
if ($book_check->num_rows == 0) {
  $conn->query("CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    writer VARCHAR(255) NOT NULL,

    -- Contents.
    file_mime_type VARCHAR(100) NOT NULL, -- `application/epub+zip` for .epub file.
    file_path VARCHAR(500) NOT NULL, -- Path in file system, currently located in `assets/epub/`.

    -- Thumbnail.
    cover_file_name VARCHAR(255) NOT NULL,
    cover_mime_type VARCHAR(100) NOT NULL, -- `image/jpg` for .jpg file.
    cover LONGBLOB NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");
}
?>

<?php
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
?>

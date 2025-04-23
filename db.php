<?php
$host = "localhost";
$user = "root";
$pass = "";

// First, connect without specifying a database.
$conn = new mysqli($host, $user, $pass);

// Check if the connection is successful.
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Check if the database exists.
$db_check = $conn->query("SHOW DATABASES LIKE 'alexandria'");

if ($db_check->num_rows == 0) {
    // Database doesn't exist, create it.
    $conn->query("CREATE DATABASE IF NOT EXISTS alexandria");
}

// Now, select the 'alexandria' database.
$conn->select_db('alexandria');

// Ensure the 'users' table exists, or create it.
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check->num_rows == 0) {
    // Table doesn't exist, so create it.
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}
?>

<?php
ini_set('memory_limit', '512M'); // RAM for large BLOBs.
ini_set('max_execution_time', 300); // Up to 5 minutes per request.

$config = require __DIR__ . '/config.php';
$host = $config['DB_HOST'];
$user = $config['DB_USER'];
$pass = $config['DB_PASS'];

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

$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check->num_rows == 0) {
  $conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");
}

$book_check = $conn->query("SHOW TABLES LIKE 'books'");
if ($book_check->num_rows == 0) {
  $conn->query("CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    writer VARCHAR(255) NOT NULL,

    -- Contents.
    file_mime_type VARCHAR(100) NOT NULL, -- `application/epub+zip` for .epub file.
    file_path VARCHAR(500) NOT NULL, -- Path in file system, e.g. `./assets/epub/file_name.epub`.

    -- Thumbnail.
    cover_file_name VARCHAR(255) NOT NULL,
    cover_mime_type VARCHAR(100) NOT NULL, -- `image/jpg` for .jpg file.
    cover LONGBLOB NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");
}

$rp_check = $conn->query("SHOW TABLES LIKE 'reading_progress'");
if ($rp_check->num_rows == 0) {
  $conn->query("CREATE TABLE IF NOT EXISTS reading_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    last_cfi VARCHAR(255) NULL, -- EPUB CFI string.
    location_current INT NOT NULL, -- Last read location in EPUB.
    location_total INT NOT NULL, -- Total location in EPUB.
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY(user_id, book_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
  )");
}

$bookmark_check = $conn->query("SHOW TABLES LIKE 'bookmark'");
if ($bookmark_check->num_rows == 0) {
  $conn->query("CREATE TABLE IF NOT EXISTS bookmark (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY(user_id, book_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
  )");
}

$category_check = $conn->query("SHOW TABLES LIKE 'category'");
if ($category_check->num_rows == 0) {
  $conn->query("CREATE TABLE IF NOT EXISTS category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
  )");
}

$book_categories_check = $conn->query("SHOW TABLES LIKE 'book_category'");
if ($book_categories_check->num_rows == 0) {
  $conn->query("CREATE TABLE IF NOT EXISTS book_category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    category_id INT NOT NULL,
    UNIQUE KEY(book_id, category_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE CASCADE
  )");
}
?>

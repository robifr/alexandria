<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Not authenticated']);
  exit;
}
$user_id = (int)$_SESSION['user_id'];
$username = $_SESSION['username'];

include '../db.php';

// Handle POST (add, delete and logout).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Handle form-data for book uploads
  if (isset($_POST['title']) && isset($_POST['writer']) && isset($_POST['categories'])) {
    $title = trim($conn->real_escape_string($_POST['title']));
    $writer = trim($conn->real_escape_string($_POST['writer']));
    $categories = json_decode($_POST['categories'], true);

    if ($title === '' || $writer === '') {
      http_response_code(400);
      echo json_encode(['error' => 'Missing required fields']);
      exit;
    }

    // Handle book file
    if (!isset($_FILES['book_file']) || $_FILES['book_file']['error'] !== UPLOAD_ERR_OK) {
      http_response_code(400);
      echo json_encode(['error' => 'Missing book file']);
      exit;
    }
    $bookFile = $_FILES['book_file'];
    $bookMime = mime_content_type($bookFile['tmp_name']);
    $bookFileName = basename($bookFile['name']);
    $relativePath = 'assets/epub/' . time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $bookFileName);
    $bookPath = '../' . $relativePath; // Path for uploading.
    if (!move_uploaded_file($bookFile['tmp_name'], $bookPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save book file']);
        exit;
    }

    // Handle cover file
    if (!isset($_FILES['cover_file']) || $_FILES['cover_file']['error'] !== UPLOAD_ERR_OK) {
      http_response_code(400);
      echo json_encode(['error' => 'Missing cover file']);
      exit;
    }
    $coverFile = $_FILES['cover_file'];
    $coverMime = mime_content_type($coverFile['tmp_name']);
    $coverFileName = basename($coverFile['name']);
    $coverBlob = file_get_contents($coverFile['tmp_name']);

    // Insert book without relative path.
    $stmt = $conn->prepare("INSERT INTO books (title, writer, file_mime_type, file_path, cover_file_name, cover_mime_type, cover) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $null = NULL;
    $stmt->bind_param("ssssssb", $title, $writer, $bookMime, $relativePath, $coverFileName, $coverMime, $null);
    $stmt->send_long_data(6, $coverBlob);
    
    if ($stmt->execute()) {
      $bookId = $stmt->insert_id;
      
      // Link categories
      if (!empty($categories)) {
        foreach ($categories as $catId) {
          $catId = (int)$catId;
          if ($catId > 0) {
            $conn->query("INSERT INTO book_category (book_id, category_id) VALUES ($bookId, $catId)");
          }
        }
      }
      
      echo json_encode(['success' => true, 'action' => 'added']);
    } else {
      http_response_code(500);
      echo json_encode(['error' => 'Failed to insert book']);
    }
    $stmt->close();
    exit;
  }

  // Handle JSON requests for other operations
  $data = json_decode(file_get_contents('php://input'), true);

  // Logout
  if (!empty($data['logout'])) {
    session_destroy();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
  }

  // Delete book
  if (!empty($data['id'])) {
    $id = (int)$data['id'];
    $res = $conn->query("SELECT file_path FROM books WHERE id = $id");
    if ($res && $res->num_rows > 0) {
      $row = $res->fetch_assoc();
      if (file_exists($row['file_path'])) unlink($row['file_path']);
    }
    $conn->query("DELETE FROM books WHERE id = $id");
    echo json_encode(['success' => true, 'action' => 'deleted']);
    exit;
  }
}

// Serve user session
if (isset($_GET['user'])) {
  header('Content-Type: application/json');
  echo json_encode([
    'id' => $user_id,
    'username' => $username
  ]);
  exit;
}

// Serve categories and books
header('Content-Type: application/json');

// Categories
$categories = [];
$result = $conn->query("SELECT id, category_name FROM category ORDER BY category_name ASC");
while ($row = $result->fetch_assoc()) {
  $categories[] = $row;
}

// Books
$search = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';
$query = "
  SELECT b.id, b.title, b.writer, b.cover_file_name, b.cover_mime_type, b.cover, b.created_at,
         GROUP_CONCAT(c.category_name ORDER BY c.category_name SEPARATOR ', ') AS categories
  FROM books b
  LEFT JOIN book_category bc ON b.id = bc.book_id
  LEFT JOIN category c ON bc.category_id = c.id
";
if ($search !== '') {
  $query .= " WHERE b.title LIKE '%$search%' OR b.writer LIKE '%$search%' ";
}
$query .= " GROUP BY b.id ORDER BY b.created_at DESC";

$result = $conn->query($query);
$books = [];
while ($row = $result->fetch_assoc()) {
  $coverBase64 = 'data:' . $row['cover_mime_type'] . ';base64,' . base64_encode($row['cover']);
  $books[] = [
    'id' => $row['id'],
    'title' => $row['title'],
    'writer' => $row['writer'],
    'categories' => $row['categories'] ? explode(', ', $row['categories']) : [],
    'created' => $row['created_at'],
    'cover' => $coverBase64,
  ];
}

echo json_encode([
  'categories' => $categories,
  'books' => $books
], JSON_UNESCAPED_UNICODE);
?>
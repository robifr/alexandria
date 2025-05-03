<?php
include '../db.php';
header('Content-Type: application/json');

// Fetch metadata and EPUB URL.
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $conn->prepare("SELECT title, writer, file_path FROM books WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows === 0) {
    http_response_code(404);
    exit('Book not found');
  }

  $stmt->bind_result($title, $writer, $file_path);
  $stmt->fetch();
  // Return the book details as JSON.
  echo json_encode([
    'id' => $id,
    'title' => $title,
    'writer' => $writer,
    'file_path' => $file_path
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// Stream the EPUB file.
if (isset($_GET['epub'])) {
  $id = (int)$_GET['epub'];
  $stmt = $conn->prepare("SELECT file_path, file_mime_type FROM books WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows === 0) {
    http_response_code(404);
    exit('EPUB not found');
  }

  $stmt->bind_result($path, $mime);
  $stmt->fetch();
  if (!file_exists($path)) {
    http_response_code(404);
    die("File not found at: " . $path);
  }

  // Set proper headers
  header("Content-Type: $mime");
  header("Content-Length: " . filesize($path));
  readfile($path);
  exit;
}

// It’s a bad request if being hit here.
http_response_code(400);
echo json_encode(['error' => 'Invalid reader request']);

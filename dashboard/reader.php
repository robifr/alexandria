<?php
session_start();
if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error'=>'Not authenticated']);
  exit;
}
$user_id = (int)$_SESSION['user_id'];

include '../db.php';
header('Content-Type: application/json');

if (isset($_GET['update_progress'])) {
  $data = json_decode(file_get_contents('php://input'), true);
  $book_id = (int)$data['id'];
  $last_cfi = $data['last_cfi'];
  $current_loc = (int)$data['location_current'];
  $total_loc = (int)$data['location_total'];

  $stmt = $conn->prepare("
    INSERT INTO reading_progress (user_id, book_id, last_cfi, location_current, location_total)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      last_cfi = VALUES(last_cfi),
      location_current = VALUES(location_current),
      location_total = VALUES(location_total),
      updated_at = NOW()
  ");
  $stmt->bind_param('iissi', $user_id, $book_id, $last_cfi, $current_loc, $total_loc);
  $stmt->execute();
  http_response_code(204);
  exit;
}

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
  $stmt->close();

  // load last-read
  $progress = $conn->prepare("
    SELECT last_cfi, location_current, location_total FROM reading_progress
    WHERE user_id = ? AND book_id = ?
  ");
  $progress->bind_param('ii', $user_id, $id);
  $progress->execute();
  $progress->store_result();
  $last_cfi = null;
  $current_loc = 0;
  $total_loc = 0;
  if ($progress->num_rows) {
    $progress->bind_result($last_cfi, $current_loc, $total_loc);
    $progress->fetch();
  }
  $progress->close();

  echo json_encode([
    'id' => $id,
    'title' => $title,
    'writer' => $writer,
    'file_path' => $file_path,
    'last_cfi' => $last_cfi,
    'location_current' => $current_loc,
    'location_total' => $total_loc
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

<?php
session_start();
if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Not authenticated']);
  exit;
}
$user_id = (int)$_SESSION['user_id'];

include '../db.php';

// Serve cover images.
if (isset($_GET['cover'])) {
  $id = (int)$_GET['cover'];
  $stmt = $conn->prepare("SELECT cover_mime_type, cover FROM books WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows === 0) {
    http_response_code(404);
    exit('Cover not found');
  }

  $stmt->bind_result($mime_type, $cover);
  $stmt->fetch();
  header('Content-Type: ' . $mime_type);
  echo $cover;
  exit;
}

// Otherwise, return JSON list with per-user last location.
header('Content-Type: application/json');
$stmt = $conn->prepare("
  SELECT 
    books.id,
    books.title,
    books.writer,
    COALESCE(reading_progress.location_current,0) AS location_current,
    COALESCE(reading_progress.location_total,0) AS location_total
  FROM books
  LEFT JOIN reading_progress
    ON reading_progress.book_id = books.id AND reading_progress.user_id = ?
  ORDER BY books.created_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();

$books = [];
while ($row = $res->fetch_assoc()) {
  $row['cover_url'] = "books.php?cover=" . $row['id'];
  $row['epub_url'] = "reader.php?epub=" . $row['id'];
  $books[] = $row;
}
echo json_encode($books, JSON_UNESCAPED_UNICODE);

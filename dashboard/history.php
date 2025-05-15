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

// Serve cover images.
if (isset($_GET['cover'])) {
  $cover_id = (int)$_GET['cover'];
  $stmt = $conn->prepare("SELECT cover_mime_type, cover FROM books WHERE id = ?");
  $stmt->bind_param('i', $cover_id);
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

// Remove single history entry.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['remove'])) {
  $data = json_decode(file_get_contents('php://input'), true);
  if (isset($data['book_id'])) {
    $book_id = intval($data['book_id']);
    // Remove reading progress row for the user.
    $stmt = $conn->prepare("DELETE FROM reading_progress WHERE user_id=? AND book_id=?");
    $stmt->bind_param('ii', $user_id, $book_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
  }
  http_response_code(400);
  echo json_encode(['error' => 'Missing book_id']);
  exit;
}

// Clear all history.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['clear'])) {
  $stmt = $conn->prepare("DELETE FROM reading_progress WHERE user_id=?");
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  echo json_encode(['success' => true]);
  exit;
}

// Fetch history grouped by date.
$stmt = $conn->prepare("
  SELECT 
    DATE(updated_at) as read_date,
    books.id,
    books.title,
    books.writer,
    books.cover_file_name
  FROM reading_progress
  JOIN books ON reading_progress.book_id = books.id
  WHERE reading_progress.user_id = ?
  ORDER BY read_date DESC, reading_progress.updated_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
  $date = $row['read_date'];
  $cover_url = "history.php?cover=" . $row['id'];
  $history[$date][] = [
    'id' => (int)$row['id'],
    'title' => $row['title'],
    'writer' => $row['writer'],
    'cover_url' => $cover_url
  ];
}
echo json_encode($history);

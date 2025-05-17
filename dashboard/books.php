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

if (isset($_GET['categories'])) {
  $result = $conn->query("SELECT id, category_name FROM category ORDER BY category_name ASC");
  $categories = [];
  while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
  }
  echo json_encode($categories, JSON_UNESCAPED_UNICODE);
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
    COALESCE(reading_progress.location_total,0) AS location_total,
    IF(bookmark.user_id IS NULL, FALSE, TRUE) AS is_bookmarked
  FROM books
  LEFT JOIN reading_progress
    ON reading_progress.book_id = books.id AND reading_progress.user_id = ?
  LEFT JOIN bookmark
    ON bookmark.book_id = books.id AND bookmark.user_id = ?
  ORDER BY books.created_at DESC
");
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

$books = [];
$book_ids = [];
while ($row = $res->fetch_assoc()) {
  $row['cover_url'] = "books.php?cover=" . $row['id'];
  $row['epub_url'] = "reader.php?epub=" . $row['id'];
  $books[] = $row;
  $book_ids[] = (int)$row['id'];
}

// Get all categories for all books.
$cat_map = [];
if (!empty($book_ids)) {
  $placeholders = implode(',', array_fill(0, count($book_ids), '?'));
  $types = str_repeat('i', count($book_ids));
  $cat_stmt = $conn->prepare("
    SELECT book_category.book_id, category.category_name
    FROM book_category
    JOIN category ON category.id = book_category.category_id
    WHERE book_category.book_id IN ($placeholders)
  ");
  $cat_stmt->bind_param($types, ...$book_ids);
  $cat_stmt->execute();
  $cat_result = $cat_stmt->get_result();
  while ($row = $cat_result->fetch_assoc()) {
    $cat_map[$row['book_id']][] = $row['category_name'];
  }
}

// Assign categories to each book.
foreach ($books as &$book) {
  $book['categories'] = $cat_map[$book['id']] ?? [];
}

echo json_encode($books, JSON_UNESCAPED_UNICODE);
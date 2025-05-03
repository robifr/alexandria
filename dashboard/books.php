<?php
include '../db.php';

// If "cover" param is set, serve the image.
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

// Otherwise, return list of books.
header('Content-Type: application/json');
$result = $conn->query("SELECT id, title, writer FROM books ORDER BY created_at DESC");
$books = [];
while ($row = $result->fetch_assoc()) {
  $row['cover_url'] = "books.php?cover=" . $row['id'];
  $books[] = $row;
}
echo json_encode($books, JSON_UNESCAPED_UNICODE);
?>

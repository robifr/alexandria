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

// If "id" param is set, return book details.
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

  // Return the book details as JSON
  echo json_encode([
    'id' => $id,
    'title' => $title,
    'writer' => $writer,
    'file_path' => $file_path
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

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

// Otherwise, return list of books.
header('Content-Type: application/json');
$result = $conn->query("SELECT id, title, writer FROM books ORDER BY created_at DESC");
$books = [];
while ($row = $result->fetch_assoc()) {
  $row['cover_url'] = "books.php?cover={$row['id']}";
  $row['epub_url'] = "books.php?epub={$row['id']}";
  $books[] = $row;
}
echo json_encode($books, JSON_UNESCAPED_UNICODE);
?>

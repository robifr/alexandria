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

// Handle POST (add, update, delete and logout).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  // Logout.
  if (!empty($data['logout'])) {
    session_destroy();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
  }

  // Update category.
  if (!empty($data['action']) && $data['action'] === 'update' && !empty($data['id']) && !empty($data['name'])) {
    $id = (int)$data['id'];
    $name = trim($conn->real_escape_string($data['name']));
    if ($name !== '') {
      $conn->query("UPDATE category SET category_name = '$name' WHERE id = $id");
      echo json_encode(['success' => true, 'action' => 'updated']);
    } else {
      http_response_code(400);
      echo json_encode(['error' => 'Category name cannot be empty']);
    }
    exit;
  }

  // Add category.
  if (!empty($data['name']) && empty($data['id'])) {
    $name = trim($conn->real_escape_string($data['name']));
    if ($name !== '') {
      $conn->query("INSERT INTO category (category_name) VALUES ('$name')");
      echo json_encode(['success' => true, 'action' => 'added']);
    } else {
      http_response_code(400);
      echo json_encode(['error' => 'Category name cannot be empty']);
    }
    exit;
  }

  // Delete category.
  if (!empty($data['id']) && empty($data['name'])) {
    $id = (int)$data['id'];
    $conn->query("DELETE FROM category WHERE id = $id");
    echo json_encode(['success' => true, 'action' => 'deleted']);
    exit;
  }
}

// Serve user session.
if (isset($_GET['user'])) {
  header('Content-Type: application/json');
  echo json_encode([
    'id' => $user_id,
    'username' => $username
  ]);
  exit;
}

// Otherwise, serve list of categories.
header('Content-Type: application/json');
$result = $conn->query("SELECT id, category_name FROM category ORDER BY category_name ASC");
$categories = [];
while ($row = $result->fetch_assoc()) {
  $categories[] = $row;
}
echo json_encode($categories, JSON_UNESCAPED_UNICODE);
?>
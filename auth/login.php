<?php
session_start();   
include '../db.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Look up user.
$stmt = $conn->prepare("SELECT id, password FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  $stmt->bind_result($user_id, $hashed);
  $stmt->fetch();

  if (password_verify($password, $hashed)) {
    // Store user ID as current session.
    $_SESSION['user_id'] = $user_id;
    echo "<script>window.location.href='../dashboard/books.html';</script>";
  } else {
    // Wrong password.
    echo "<script>window.location.href='auth.html?error=login';</script>";
  }
} else {
  // User not found.
  echo "<script>window.location.href='auth.html?error=login';</script>";
}

$stmt->close();
$conn->close();

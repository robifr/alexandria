<?php
include '../db.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Check if the username already exists.
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  // Username already taken.
  echo "<script>window.location.href='auth.html?error=signup';</script>";
  exit();
}

// Hash the password before saving it.
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed);

if ($stmt->execute()) {
  // Successful signup.
  echo "<script>window.location.href='auth.html?success=signup';</script>";
} else {
  // Error creating account.
  echo "<script>window.location.href='auth.html?error=signup';</script>";
}

$stmt->close();
$conn->close();
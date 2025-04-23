<?php
include '../db.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Look up user.
$stmt = $conn->prepare("SELECT password FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  $stmt->bind_result($hashed);
  $stmt->fetch();

  if (password_verify($password, $hashed)) {
    echo "<script>window.location.href='../dashboard/books.html';</script>";
  } else {
    echo "<script>alert('Wrong password'); window.location.href='login.html';</script>";
  }
} else {
  echo "<script>alert('Username not found'); window.location.href='login.html';</script>";
}
$stmt->close();
$conn->close();
?>

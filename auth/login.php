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

  // If success, go to dashboard.
  if (password_verify($password, $hashed)) {
    echo "<script>window.location.href='../dashboard/books.html';</script>";
  } else {
    echo "<script>window.location.href='login.html?error=1';</script>";
  }
} else {
  echo "<script>window.location.href='login.html?error=1';</script>";
}

$stmt->close();
$conn->close();
?>

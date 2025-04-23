<?php
include '../db.php';

// Get the username and password from the POST request.
$username = $_POST['username'];
$password = $_POST['password'];

// Check if the username already exists.
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Username already exists, show an error message.
    echo "<script>alert('Username already taken! Please choose a different one.'); window.location.href='signup.html';</script>";
    exit();
}

// Hash the password before saving it.
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert the new user into the database.
$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed);

if ($stmt->execute()) {
    // Successfully created the account.
    echo "<script>alert('Account created!'); window.location.href='login.html';</script>";
} else {
    // Something went wrong (shouldn't happen if the database is set up correctly)
    echo "<script>alert('Something went wrong. Please try again.'); window.location.href='signup.html';</script>";
}

$stmt->close();
$conn->close();
?>

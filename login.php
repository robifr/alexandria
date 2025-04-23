<?php 
session_start();
include "conn.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">

</head>
<body>

<?php
 if(isset($_POST['username'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $query = mysqli_query($conn, "SELECT*FROM user
     where username= '$username' and password='$password' ");

    if(mysqli_num_rows($query) > 0){
        $data = mysqli_fetch_array($query);
        $_SESSION['user'] = $data;
        echo '<script>
        location.href="index.php";</script>';
    }else{
        echo '<script>alert("Usename or Password incorect.");</script>';
    }
 }
?>

    <div class="login_form">
        <h2>Log in</h2>
        <form method="post">
            <input type="text" name="username" placeholder="username" required>
            <input type="password" name="password" placeholder="password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account?<a href="signup.php">Register</a></p>
    </div>

</body>
</html>
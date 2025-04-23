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
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>

    <?php
    if(isset($_POST['username'])){
        $name = $_POST['name'];
        $username =$_POST['username'];
        $password = md5($_POST['password']);

        $check = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
    if(mysqli_num_rows($check) > 0){
        echo '<script>alert("Username already taken");</script>';
    } else{
        $query = mysqli_query($conn, "INSERT INTO user(name,username,password) value('$name','$username','$password')");
        if($query){
            echo '<script>alert("congrats account created.");
            location.href="login.php"</script>';
        }else{
            echo '<script>alert("username was used")</script>';
            }
        }
    }
    
    ?>

    <div class="signup_form">
        <h2>Sign up</h2>
        <form method="post">
            <input type="text" name="name" placeholder="Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <p>have an account?<a href="login.php">Login</a></p>
    </div>
</body>
</html>
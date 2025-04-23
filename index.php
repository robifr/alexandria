<?php
// Include the db.php file, which will handle database creation and table setup.
include 'db.php';

// Redirect to the login page after database setup is complete.
header("Location: auth/login.html");
exit;
?>

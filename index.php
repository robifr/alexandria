<?php
// Include the db.php file, which will handle database creation and table setup.
include 'db.php';

// Redirect to the authentication page after database setup is complete.
header("Location: auth/auth.html");
exit;
?>

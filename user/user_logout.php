<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

<<<<<<< HEAD
// Redirect to home page of the project
header("Location: http://localhost/ecocollect/");
=======
// Redirect to login page
header("Location: user_login.php");
>>>>>>> 9cf3b64f7d69f7b3281d8dc73055b26a706c1b65
exit();
?>

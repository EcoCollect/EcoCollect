<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to home page of the project
header("Location: http://localhost/ecocollect/");
exit();
?>

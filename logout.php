<?php
session_start();

// Destroy all session variables
$_SESSION = array();

// Completely end the session
session_destroy();

// Redirect back to login page
header("Location: faculty_login.html?msg=" . urlencode("You have been logged out successfully."));
exit;
?>

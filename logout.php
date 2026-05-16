<?php
// Start the session
session_start();

// Destroy the session to log the user out
session_unset();  // Remove all session variables
session_destroy(); // Destroy the session

// Redirect the user to the login page (or another page)
header("Location: ./");
exit;
?>

<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['first_name'] = 'Admin';
$_SERVER['HTTP_HOST'] = 'localhost';

ob_start();
include 'adminPanel/Admissions/index.php';
$output = ob_get_clean();
echo "LENGTH OF OUTPUT: " . strlen($output) . "\n";
echo "LAST 500 CHARS:\n";
echo substr($output, -500) . "\n";
?>

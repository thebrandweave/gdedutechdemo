<?php 
$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'gdedutech.com') !== false) {
    $conn = new mysqli("localhost", "u232955123_gdedutech", "Brandweave@24", "u232955123_gdedutech");

} else {
    $conn = new mysqli("localhost", "root", "", "gd_edu_tech");
}

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// define('UPLOADS_DIR', __DIR__ . '/uploads/');
$adminMail="gdedutech24@gmail.com";
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

?>
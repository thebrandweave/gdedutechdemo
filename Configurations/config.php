<?php 
$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'gdedutech.com') !== false) {
    $conn = new mysqli("localhost", "u232955123_gdedutech", "Brandweave@24", "u232955123_gdedutech");
} else {
    $conn = new mysqli("localhost", "root", "", "gdedutech");
}

// Re-enable this so failures are visible instead of a blank page
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$adminMail = "gdedutech24@gmail.com";

// Google OAuth Client ID Configuration
// Replace with your Client ID from Google Cloud Console (https://console.cloud.google.com/apis/credentials)
define('GOOGLE_CLIENT_ID', '267890495884-euub370ds33tgn98rponciu9n8e59snl.apps.googleusercontent.com');
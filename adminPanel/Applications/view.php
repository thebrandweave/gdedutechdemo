<?php
session_start();

// Admin protection
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

// Validate file
if (!isset($_GET['file'])) {
    die("No file specified");
}

$file = basename($_GET['file']); // prevent directory traversal
$filePath = "uploads/" . $file;

// Check file exists
if (!file_exists($filePath)) {
    die("File not found");
}

// Get file type
$mime = mime_content_type($filePath);

// Set headers
header("Content-Type: $mime");
header("Content-Disposition: inline; filename=\"$file\"");

// Output file
readfile($filePath);
exit;
?>
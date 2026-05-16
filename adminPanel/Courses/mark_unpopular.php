<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Check if the ID is provided
if (isset($_GET['id'])) {
    $course_id = intval($_GET['id']);

    // Update the isPopular status in the database
    $stmt = $conn->prepare("UPDATE Courses SET isPopular = 0 WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);

    if ($stmt->execute()) {
        header("Location: index.php?message=Course marked as unpopular successfully.");
        exit();
    } else {
        header("Location: index.php?error=Error marking course as unpopular.");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>

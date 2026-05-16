<?php
session_start();
require_once '../../Configurations/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && strtolower($_SESSION['role']) === 'staff') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
   
    if (empty($title) || empty($content)) {
        $_SESSION['error'] = "Both title and content are required.";
    } else {
        // Add debugging
        error_log("Attempting to insert message: Title: $title, User ID: $user_id");
        
        $query = "INSERT INTO Messages (title, content, created_by) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt === false) {
            error_log("MySQL Error: " . mysqli_error($conn));
            $_SESSION['error'] = "Database error occurred.";
        } else {
            mysqli_stmt_bind_param($stmt, 'ssi', $title, $content, $user_id);
           
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Message posted successfully.";
            } else {
                error_log("Execute Error: " . mysqli_stmt_error($stmt));
                $_SESSION['error'] = "Failed to post message.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
} else {
    $_SESSION['error'] = "Permission denied or invalid request method.";
}

header('Location: index.php');
exit();
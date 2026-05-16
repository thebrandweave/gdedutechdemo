<?php
session_start();
require_once '../../Configurations/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    
    if (empty($title) || empty($content)) {
        $_SESSION['error'] = "Both title and content are required.";
    } else {
        $query = "INSERT INTO StudentQuestions (title, content, user_id) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $title, $content, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Your question has been submitted successfully.";
        } else {
            $_SESSION['error'] = "Error submitting question. Please try again.";
        }
    }
}

header('Location: index.php');
exit();

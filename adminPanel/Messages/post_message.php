<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (!empty($title) && !empty($content)) {
        $insert_query = "INSERT INTO Messages (title, content, created_by) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Location: ./');
            exit();
        }
    }
}

header('Location: ./');
exit(); 
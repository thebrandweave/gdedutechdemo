<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    $message_id = $_GET['id'];
    
    $delete_query = "DELETE FROM Messages WHERE message_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $message_id);
    
    mysqli_stmt_execute($stmt);
}

header('Location: ./');
exit();
?> 
<?php
session_start();
require_once '../../Configurations/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}


if (isset($_GET['id']) && isset($_GET['status'])) {
    $meeting_id = intval($_GET['id']);
    $status = $_GET['status'];
    
    if (in_array($status, ['approved', 'rejected', 'completed'])) {
        $query = "UPDATE meeting_schedules SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $meeting_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Meeting status updated successfully.";
        } else {
            $_SESSION['error'] = "Error updating meeting status.";
        }
    }
}

header('Location: index.php');
exit();
?> 
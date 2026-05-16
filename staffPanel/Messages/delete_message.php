<?php
session_start();
require_once '../../Configurations/config.php';

if (isset($_GET['id']) && isset($_SESSION['user_id']) && $_SESSION['role'] === 'Staff') {
    $message_id = intval($_GET['id']);
    
    // First check if the user has permission to delete this message
    $check_query = "SELECT created_by FROM Messages WHERE message_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'i', $message_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $message = mysqli_fetch_assoc($result);
    
    // Only proceed if the message exists and user is either the creator or an admin
    if ($message && ($_SESSION['user_id'] == $message['created_by'] || $_SESSION['role'] === 'Admin')) {
        $query = "DELETE FROM Messages WHERE message_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $message_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Message deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting message.";
        }
    } else {
        $_SESSION['error'] = "You don't have permission to delete this message.";
    }
} else {
    $_SESSION['error'] = "Invalid request or insufficient permissions.";
}

header('Location: index.php');
exit();
?> 
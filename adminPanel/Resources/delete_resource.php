<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // First, delete all access requests for this paper
        $delete_requests_query = "DELETE FROM access_requests WHERE paper_id = ?";
        $stmt = mysqli_prepare($conn, $delete_requests_query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        // Then, delete the paper itself
        $delete_paper_query = "DELETE FROM question_papers WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_paper_query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        // If both operations are successful, commit the transaction
        mysqli_commit($conn);

        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        // If an error occurs, rollback the changes
        mysqli_rollback($conn);
        die("Error deleting resource: " . $e->getMessage());
    }
}

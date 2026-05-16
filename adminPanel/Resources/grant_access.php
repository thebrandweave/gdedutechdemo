<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Handle granting access
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    // Fetch the paper ID from the access request
    $request_query = "SELECT paper_id FROM access_requests WHERE id = $request_id";
    $request_result = mysqli_query($conn, $request_query);
    $request_data = mysqli_fetch_assoc($request_result);
    $paper_id = $request_data['paper_id'];

    // Update the question paper status to 'open'
    $update_query = "UPDATE question_papers SET status = 'open' WHERE id = $paper_id";
    mysqli_query($conn, $update_query);

    // Update the access request status to 'granted'
    $update_request_query = "UPDATE access_requests SET status = 'granted' WHERE id = $request_id";
    mysqli_query($conn, $update_request_query);

    header('Location: index.php');
    exit();
}

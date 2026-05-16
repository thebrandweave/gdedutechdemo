<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle access request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paper_id'])) {
    $paper_id = $_POST['paper_id'];
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

    // Insert access request into the database
    $insert_query = "INSERT INTO access_requests (paper_id, user_id) VALUES ('$paper_id', '$user_id')";
    mysqli_query($conn, $insert_query);
}

<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Not logged in');
}

// Handle access request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paper_id'])) {
    $paper_id = $_POST['paper_id'];
    $user_id = $_SESSION['user_id'];

    // Check if request already exists
    $check_query = "SELECT * FROM access_requests 
                    WHERE paper_id = ? AND user_id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $paper_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        // Insert new access request
        $insert_query = "INSERT INTO access_requests (paper_id, user_id) 
                        VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ii", $paper_id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            http_response_code(200);
            echo "Request sent successfully";
        } else {
            http_response_code(500);
            echo "Error sending request: " . mysqli_error($conn);
        }
    } else {
        http_response_code(400);
        echo "Request already pending";
    }
} else {
    http_response_code(400);
    echo "Invalid request";
}

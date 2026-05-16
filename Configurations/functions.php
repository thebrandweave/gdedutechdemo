<?php
// Function to log activities
function logActivity($conn, $user_id, $activity_type, $description, $related_id = null) {
    $query = "INSERT INTO ActivityLog (user_id, activity_type, activity_description, related_id) 
              VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'issi', $user_id, $activity_type, $description, $related_id);
    mysqli_stmt_execute($stmt);
}

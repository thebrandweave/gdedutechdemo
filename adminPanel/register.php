<?php

// Add after successful user registration
if ($stmt->execute()) {
    $user_id = mysqli_insert_id($conn);
    logActivity($conn, $user_id, 'user_registered', "New user registration", $user_id);
    // ... rest of your registration success handling
}

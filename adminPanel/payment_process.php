<?php
// Add after successful payment
if ($payment_success) {
    logActivity($conn, $_SESSION['user_id'], 'payment_made', "Made payment of ₹$amount for course: $course_title", $payment_id);
    // ... rest of your payment success handling
}

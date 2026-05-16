<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .confirmation-card {
            max-width: 600px;
            margin: 100px auto;
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success-icon {
            font-size: 4rem;
            color: #198754;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="card confirmation-card">
            <div class="card-body text-center p-5">
                <i class="bi bi-check-circle-fill success-icon"></i>
                <h2 class="mb-4">Payment Submitted Successfully!</h2>
                <p class="text-muted mb-4">
                    Your payment proof has been submitted and is pending approval. 
                    Please wait while we verify your payment. This usually takes 1-24 hours.
                </p>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    You will receive a notification once your payment is approved.
                </div>
                <a href="../" class="btn btn-primary btn-lg">
                    <i class="bi bi-house me-2"></i>Go to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Database connection
require_once '../../Configurations/config.php';

// Get job ID and fetch details
if (isset($_GET['id'])) {
    $job_id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT * FROM Careers WHERE job_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $career = mysqli_fetch_assoc($result);

    if (!$career) {
        echo json_encode(['error' => 'Career not found']);
        exit();
    }

    // Format the date
    $deadline = new DateTime($career['application_deadline']);
    $formatted_deadline = $deadline->format('M d, Y');
    $created_at = new DateTime($career['created_at']);
    $formatted_created = $created_at->format('M d, Y');

    // Prepare the HTML response
    $response = '
    <div class="modal-header">
        <h5 class="modal-title">Career Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 24px;">
                        ' . strtoupper(substr($career['job_title'], 0, 1)) . '
                    </div>
                    <div>
                        <h4 class="mb-1">' . htmlspecialchars($career['job_title']) . '</h4>
                        <p class="text-muted mb-0">' . htmlspecialchars($career['company_name']) . '</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Location:</strong><br>' . htmlspecialchars($career['location']) . '</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Job Type:</strong><br><span class="badge bg-info">' . htmlspecialchars($career['job_type']) . '</span></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Application Deadline:</strong><br>' . $formatted_deadline . '</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong><br>
                            <span class="badge bg-' . ($career['status'] === 'Active' ? 'success' : ($career['status'] === 'Closed' ? 'danger' : 'warning')) . '">
                                ' . htmlspecialchars($career['status']) . '
                            </span>
                        </p>
                    </div>
                </div>

                <div class="mb-3">
                    <h5>Job Description</h5>
                    <p>' . nl2br(htmlspecialchars($career['job_description'])) . '</p>
                </div>

                <div class="mb-3">
                    <h5>Requirements</h5>
                    <p>' . nl2br(htmlspecialchars($career['requirements'])) . '</p>
                </div>

                <div class="mb-3">
                    <h5>Benefits</h5>
                    <p>' . nl2br(htmlspecialchars($career['benefits'])) . '</p>
                </div>

                <div class="text-muted">
                    <small>Posted on: ' . $formatted_created . '</small>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="edit_career.php?id=' . $career['job_id'] . '" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>';

    echo $response;
    exit();
}

// If no ID provided, redirect to index
header('Location: index.php');
exit();
<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Database connection
require_once '../../Configurations/config.php';

// Get admin details from session
$admin_name = $_SESSION['first_name'] ?? 'Admin';

// Fetch all careers
$query = "SELECT * FROM Careers ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Management - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;"><img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
<li class="w-100">
                            <a href="../Admissions/" class="nav-link">
                                <i class="bi bi-person-plus me-2"></i> Student Admission
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Applications/" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i> Scholarship Applications
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Events/" class="nav-link">
                                <i class="bi bi-calendar2-event me-2"></i> Events
                            </a>
                        </li>
                        <li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle active" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-lightbulb me-2"></i> Quick Links
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                                <li><a class="dropdown-item" href="../index.php">Career portal</a></li>
                                <li><a class="dropdown-item" href="./Shop/shop.php">Shop</a></li>
                            </ul>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule/" class="nav-link">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../FAQ/" class="nav-link">
                                <i class="bi bi-question-circle me-2"></i> FAQ
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Users/" class="nav-link">
                                <i class="bi bi-people me-2"></i> Users
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../manage_qr.php" class="nav-link">
                                <i class="bi bi-qr-code me-2"></i> Payment QR
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../pending_payments.php" class="nav-link">
                                <i class="bi bi-credit-card me-2"></i> Pending Payments
                            </a>
                        </li>
                        <li class="w-100 mt-auto">
                            <a href="../logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

           

            <!-- Main Content -->
            <div class="col py-3">
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Career Portal Management</h2>
                            <p class="text-muted">Manage career opportunities and job listings</p>
                        </div>
                        <div class="col-auto">
                            <a href="view_candidates.php" class="btn btn-info me-2">
                                <i class="bi bi-people me-2"></i>View All Candidates
                            </a>
                            <a href="Add_career.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Add New Career
                            </a>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                            <?php 
                            echo htmlspecialchars($_SESSION['message']);
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Careers Table -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Current Job Listings</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="py-3 px-4">Job Title</th>
                                            <th class="py-3 px-4">Company</th>
                                            <th class="py-3 px-4">Location</th>
                                            <th class="py-3 px-4">Job Type</th>
                                            <th class="py-3 px-4">Deadline</th>
                                            <th class="py-3 px-4">Status</th>
                                            <th class="py-3 px-4 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($career = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td class="py-3 px-4">
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <?php echo strtoupper(substr($career['job_title'], 0, 1)); ?>
                                                            </div>
                                                            <?php echo htmlspecialchars($career['job_title']); ?>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 px-4"><?php echo htmlspecialchars($career['company_name']); ?></td>
                                                    <td class="py-3 px-4"><?php echo htmlspecialchars($career['location']); ?></td>
                                                    <td class="py-3 px-4">
                                                        <span class="badge bg-info">
                                                            <?php echo htmlspecialchars($career['job_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-4">
                                                        <?php 
                                                        if (!empty($career['application_deadline'])) {
                                                            $deadline = new DateTime($career['application_deadline']);
                                                            echo $deadline->format('M d, Y');
                                                        } else {
                                                            echo 'No deadline set';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="py-3 px-4">
                                                        <span class="badge bg-<?php echo $career['status'] === 'Active' ? 'success' : ($career['status'] === 'Closed' ? 'danger' : 'warning'); ?>">
                                                            <?php echo htmlspecialchars($career['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-4 text-center">
                                                        <div class="btn-group" role="group">
                                                            <a href="view_career.php?id=<?php echo $career['job_id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary me-1" 
                                                               title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="edit_career.php?id=<?php echo $career['job_id']; ?>" 
                                                               class="btn btn-sm btn-outline-warning me-1" 
                                                               title="Edit Job">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="delete_career.php?id=<?php echo $career['job_id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this job posting?');" 
                                                               title="Delete Job">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">No job listings found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Add this modal container -->
    <div class="modal fade" id="careerModal" tabindex="-1" aria-labelledby="careerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Add this script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle view button clicks
        document.querySelectorAll('a[href^="view_career.php"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalContent').innerHTML = html;
                        const modal = new bootstrap.Modal(document.getElementById('careerModal'));
                        modal.show();
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    });
    </script>
</body>
</html>
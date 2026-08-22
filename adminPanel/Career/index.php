<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Database connection
require_once '../../Configurations/config.php';

// Get admin details from session
$admin_name = $_SESSION['username'] ?? 'Admin';

// Fetch all careers
$query = "SELECT * FROM Careers ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Opportunities Management - GD Edu Tech Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">
            
            <!-- Executive Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar d-flex flex-column">
                <div class="p-3 border-bottom border-white border-opacity-10 d-flex align-items-center gap-2">
                    <img height="36" src="../../Images/Logos/GD_Only_logo.png" alt="GD Logo">
                    <div>
                        <div class="fw-bold text-white fs-6">GD Edu Tech</div>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100" id="menu">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="../Admissions/" class="nav-link"><i class="bi bi-person-plus me-2"></i> Student Admission</a></li>
                    <li class="w-100"><a href="../Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="../Applications/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="../Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-briefcase me-2"></i> Careers</a></li>
                    <li class="w-100"><a href="../social_links.php" class="nav-link"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100"><a href="../Schedule/index.php" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="../feedback/feedback.php" class="nav-link"><i class="bi bi-chat-square-heart me-2"></i> Feedback</a></li>
                    <li class="w-100"><a href="../Messages/index.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="../FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="../Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="../manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="../pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col min-vh-100 d-flex flex-column">
                
                <!-- Top Header -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Career Portal Management</h4>
                        <span class="text-muted small">Manage job opportunities, create career listings, and review candidates</span>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="view_candidates.php" class="btn btn-outline-primary rounded-pill">
                            <i class="bi bi-people-fill me-1.5"></i> View Candidates
                        </a>
                        <a href="Add_career.php" class="btn btn-primary rounded-pill">
                            <i class="bi bi-plus-circle-fill me-1.5"></i> Add New Job Opening
                        </a>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <!-- Alert Messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['message']); ?></span>
                            </div>
                            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Career Table Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-briefcase-fill text-primary me-2"></i>Active Job Openings Catalog</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-1.5 rounded-pill fw-semibold">
                                Total: <?php echo mysqli_num_rows($result); ?> Listings
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                        <th>Job Type</th>
                                        <th>Salary Range</th>
                                        <th>Status</th>
                                        <th>Posted Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                        <?php while ($job = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td>
                                                    <strong class="text-dark d-block"><?php echo htmlspecialchars($job['job_title']); ?></strong>
                                                    <span class="badge bg-light text-dark border px-2 py-0.5 font-monospace">#<?php echo $job['job_id']; ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-secondary fw-semibold"><?php echo htmlspecialchars($job['company_name'] ?? 'GD Edu Tech'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-dark small"><i class="bi bi-geo-alt-fill me-1 text-danger"></i><?php echo htmlspecialchars($job['location'] ?? 'Remote'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1 rounded-pill">
                                                        <?php echo htmlspecialchars($job['job_type'] ?? 'Full-time'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-dark small"><i class="bi bi-cash-stack me-1 text-success"></i><?php echo htmlspecialchars($job['salary_range'] ?? 'Negotiable'); ?></span>
                                                </td>
                                                <td>
                                                    <?php if (($job['status'] ?? 'Active') === 'Active'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2.5 py-1 rounded-pill">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill"><?php echo htmlspecialchars($job['status'] ?? 'Closed'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-muted small text-nowrap">
                                                    <?php echo !empty($job['created_at']) ? date('M d, Y', strtotime($job['created_at'])) : '-'; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1.5">
                                                        <button type="button" class="action-icon btn border-0 p-0 text-info" onclick="viewCareer(<?php echo $job['job_id']; ?>)" title="View Job Details">
                                                            <i class="bi bi-eye-fill fs-6"></i>
                                                        </button>
                                                        <a href="edit_career.php?id=<?php echo $job['job_id']; ?>" class="action-icon text-warning" title="Edit Listing">
                                                            <i class="bi bi-pencil-fill fs-6"></i>
                                                        </a>
                                                        <a href="delete_career.php?id=<?php echo $job['job_id']; ?>" class="action-icon text-danger" onclick="return confirm('Are you sure you want to delete this job posting?');" title="Delete Listing">
                                                            <i class="bi bi-trash-fill fs-6"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No job openings added yet. Click "Add New Job Opening" above to create one.</td>
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

    <!-- View Career Details Modal -->
    <div class="modal fade" id="viewCareerModal" tabindex="-1" aria-labelledby="viewCareerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold" id="viewCareerModalLabel"><i class="bi bi-briefcase-fill text-info me-2"></i>Job Opening Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="viewCareerModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewCareer(jobId) {
        const modalBody = document.getElementById('viewCareerModalBody');
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading job details...</span>
                </div>
                <p class="text-muted small mt-2">Fetching job specification...</p>
            </div>
        `;
        const modal = new bootstrap.Modal(document.getElementById('viewCareerModal'));
        modal.show();

        fetch(`view_career.php?id=${jobId}`)
            .then(response => response.text())
            .then(data => {
                modalBody.innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div class="alert alert-danger mb-0">Error loading job details.</div>';
            });
    }
    </script>
</body>
</html>
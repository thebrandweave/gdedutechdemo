<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Database connection
require_once '../../Configurations/config.php';

// Get application ID
$application_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

// Fetch application details
$query = "SELECT ja.*, c.job_title, c.company_name, c.location, c.job_type 
          FROM job_applications ja
          JOIN Careers c ON ja.job_id = c.job_id 
          WHERE ja.application_id = '$application_id'";
$result = mysqli_query($conn, $query);
$application = mysqli_fetch_assoc($result);

if (!$application) {
    echo '<div class="alert alert-danger">Application not found.</div>';
    exit();
}
?>

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="bi bi-file-person me-2"></i>Application Details</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row g-4">
        <!-- Job Information -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i>Job Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-bookmark-star text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Job Title</small>
                                    <strong><?php echo htmlspecialchars($application['job_title']); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Company</small>
                                    <strong><?php echo htmlspecialchars($application['company_name']); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Location</small>
                                    <strong><?php echo htmlspecialchars($application['location']); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Job Type</small>
                                    <strong><?php echo htmlspecialchars($application['job_type']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applicant Information -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Applicant Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-person-badge text-info me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Full Name</small>
                                    <strong><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></strong>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-envelope text-info me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Email</small>
                                    <strong><?php echo htmlspecialchars($application['email']); ?></strong>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-telephone text-info me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Phone</small>
                                    <strong><?php echo htmlspecialchars($application['phone']); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-calendar-event text-info me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Application Date</small>
                                    <strong><?php echo date('M d, Y', strtotime($application['application_date'])); ?></strong>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-briefcase text-info me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Current Position</small>
                                    <strong><?php echo isset($application['current_position']) ? htmlspecialchars($application['current_position']) : 'Not specified'; ?></strong>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-star text-info me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Experience</small>
                                    <strong><?php echo isset($application['years_of_experience']) ? htmlspecialchars($application['years_of_experience']) . ' years' : 'Not specified'; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cover Letter -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Cover Letter</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <a href="<?php echo '../../uploads/resumes/' . basename($application['resume_path']); ?>" 
       class="btn btn-primary" target="_blank">
        <i class="bi bi-file-earmark-pdf me-2"></i>View Resume
    </a>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="bi bi-x-circle me-2"></i>Close
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle view button clicks
    document.querySelectorAll('a[href^="view_application.php"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalContent').innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById('applicationModal'));
                    modal.show();
                })
                .catch(error => console.error('Error:', error));
        });
    });
});
</script>

<!-- Add modal container -->
<div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div> 
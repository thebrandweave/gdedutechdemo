<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
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
    echo '<div class="alert alert-danger mb-0">Application record not found.</div>';
    exit();
}

$resume_file = !empty($application['resume_path']) ? '../../uploads/resumes/' . basename($application['resume_path']) : '';
?>

<!-- Job & Applicant Grid -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="p-3 bg-light rounded-3 border h-100">
            <span class="text-muted small d-block mb-1"><i class="bi bi-briefcase-fill text-primary me-1"></i> Job Position</span>
            <strong class="text-dark d-block fs-6"><?php echo htmlspecialchars($application['job_title']); ?></strong>
            <span class="text-secondary small"><?php echo htmlspecialchars($application['company_name']); ?> (<?php echo htmlspecialchars($application['location']); ?> - <?php echo htmlspecialchars($application['job_type']); ?>)</span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 bg-light rounded-3 border h-100">
            <span class="text-muted small d-block mb-1"><i class="bi bi-person-fill text-info me-1"></i> Candidate Name</span>
            <strong class="text-dark d-block fs-6"><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></strong>
            <span class="text-secondary small">Applied on <?php echo date('M d, Y', strtotime($application['application_date'])); ?></span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 bg-light rounded-3 border h-100">
            <span class="text-muted small d-block mb-1"><i class="bi bi-envelope-fill text-primary me-1"></i> Email Address</span>
            <strong class="text-dark"><?php echo htmlspecialchars($application['email']); ?></strong>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 bg-light rounded-3 border h-100">
            <span class="text-muted small d-block mb-1"><i class="bi bi-telephone-fill text-success me-1"></i> Phone Number</span>
            <strong class="text-dark"><?php echo htmlspecialchars($application['phone']); ?></strong>
        </div>
    </div>
</div>

<!-- Cover Letter Section -->
<div class="mb-4">
    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-text-fill text-secondary me-2"></i>Cover Letter / Notes</h6>
    <div class="p-3 bg-white border rounded-3 text-dark" style="font-size: 0.9rem; line-height: 1.6; max-height: 200px; overflow-y: auto;">
        <?php echo !empty($application['cover_letter']) ? nl2br(htmlspecialchars($application['cover_letter'])) : '<em class="text-muted">No cover letter submitted by candidate.</em>'; ?>
    </div>
</div>

<!-- Resume & Action Footer -->
<div class="d-flex justify-content-between align-items-center pt-3 border-top">
    <div>
        <?php if (!empty($resume_file)): ?>
            <a href="<?php echo $resume_file; ?>" target="_blank" class="btn btn-success rounded-pill px-4 fw-semibold">
                <i class="bi bi-file-earmark-pdf-fill me-1.5"></i> Open Resume PDF
            </a>
        <?php else: ?>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">No Resume Uploaded</span>
        <?php endif; ?>
    </div>
    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
</div>
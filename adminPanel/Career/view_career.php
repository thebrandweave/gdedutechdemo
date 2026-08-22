<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
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
        echo '<div class="alert alert-danger mb-0">Career listing not found.</div>';
        exit();
    }

    $deadline = !empty($career['application_deadline']) ? date('M d, Y', strtotime($career['application_deadline'])) : 'Open Until Filled';
    $created_at = !empty($career['created_at']) ? date('M d, Y', strtotime($career['created_at'])) : '-';

    // Output clean modal details HTML
    ?>
    <div class="p-3 bg-light rounded-4 border mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, #0d7298, #0f172a); font-size: 22px;">
                <i class="bi bi-briefcase-fill"></i>
            </div>
            <div>
                <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($career['job_title']); ?></h4>
                <span class="text-secondary small me-2"><i class="bi bi-building me-1"></i><?php echo htmlspecialchars($career['company_name']); ?></span>
                <span class="badge bg-primary bg-opacity-10 text-primary border px-2.5 py-1 rounded-pill"><?php echo htmlspecialchars($career['job_type']); ?></span>
            </div>
        </div>
        <div>
            <?php if (($career['status'] ?? 'Active') === 'Active'): ?>
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-1.5 rounded-pill fs-6">Active</span>
            <?php else: ?>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill fs-6"><?php echo htmlspecialchars($career['status']); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 bg-white border rounded-3 h-100">
                <span class="text-muted small d-block mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Location</span>
                <strong class="text-dark"><?php echo htmlspecialchars($career['location'] ?? 'Not specified'); ?></strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white border rounded-3 h-100">
                <span class="text-muted small d-block mb-1"><i class="bi bi-cash-stack text-success me-1"></i> Salary / Stipend</span>
                <strong class="text-dark"><?php echo htmlspecialchars($career['salary_range'] ?? 'Negotiable'); ?></strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white border rounded-3 h-100">
                <span class="text-muted small d-block mb-1"><i class="bi bi-calendar-event-fill text-primary me-1"></i> Application Deadline</span>
                <strong class="text-dark"><?php echo $deadline; ?></strong>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <h6 class="fw-bold text-dark"><i class="bi bi-card-text text-primary me-1.5"></i>Job Description</h6>
        <div class="p-3 bg-white border rounded-3 text-secondary" style="font-size: 0.9rem; line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($career['job_description'])); ?>
        </div>
    </div>

    <div class="mb-3">
        <h6 class="fw-bold text-dark"><i class="bi bi-check2-square text-success me-1.5"></i>Requirements & Qualifications</h6>
        <div class="p-3 bg-white border rounded-3 text-secondary" style="font-size: 0.9rem; line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($career['requirements'])); ?>
        </div>
    </div>

    <?php if (!empty($career['benefits'])): ?>
    <div class="mb-3">
        <h6 class="fw-bold text-dark"><i class="bi bi-gift-fill text-warning me-1.5"></i>Benefits & Perks</h6>
        <div class="p-3 bg-white border rounded-3 text-secondary" style="font-size: 0.9rem; line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($career['benefits'])); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-4">
        <span class="text-muted small"><i class="bi bi-clock me-1"></i>Posted on: <?php echo $created_at; ?></span>
        <div class="d-flex gap-2">
            <a href="edit_career.php?id=<?php echo $career['job_id']; ?>" class="btn btn-warning px-4 rounded-pill fw-semibold text-dark">
                <i class="bi bi-pencil-fill me-1"></i> Edit Opening
            </a>
            <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
    <?php
    exit();
}

header('Location: index.php');
exit();
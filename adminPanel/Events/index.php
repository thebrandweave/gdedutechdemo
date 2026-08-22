<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once '../../Configurations/config.php';

// Delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $event_id = intval($_GET['id']);
    $q = "DELETE FROM Events WHERE event_id = $event_id";
    if (mysqli_query($conn, $q)) {
        $_SESSION['message'] = 'Event deleted successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error deleting event: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'danger';
    }
    header('Location: index.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$total_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM Events");
$total = ($total_q && ($r = mysqli_fetch_assoc($total_q))) ? intval($r['c']) : 0;
$total_pages = max(1, ceil($total / $limit));

$events_q = mysqli_query($conn, "SELECT e.*, u.username AS organizer_name FROM Events e LEFT JOIN Users u ON e.organizer_id = u.user_id ORDER BY e.created_at DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management - GD Edu Tech</title>
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
                        <span class="text-success small fw-semibold">● System Online</span>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100" id="menu">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="../Admissions/" class="nav-link"><i class="bi bi-person-plus me-2"></i> Student Admission</a></li>
                    <li class="w-100"><a href="../Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="../Applications/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="../Events/" class="nav-link active"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
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
                        <h4 class="fw-bold text-dark mb-0">Events Management</h4>
                        <span class="text-muted small">Create workshops, seminars, and campus events</span>
                    </div>

                    <a href="./add_event.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add New Event
                    </a>
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

                    <!-- Events Cards Grid -->
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                        <?php if ($events_q && mysqli_num_rows($events_q) > 0): ?>
                            <?php while ($ev = mysqli_fetch_assoc($events_q)): ?>
                                <?php
                                    $cover = $ev['main_cover_image'] ?? '';
                                    $coverSrc = '';
                                    if ($cover) {
                                        $coverSrc = $cover;
                                        if (!preg_match('/\/(?:uploads|Images|assets)\//', $cover)) {
                                            $coverSrc = '../../uploads/events/' . htmlspecialchars($cover);
                                        }
                                    }
                                    $statusBadge = 'bg-primary';
                                    if ($ev['status'] === 'upcoming') $statusBadge = 'bg-success';
                                    elseif ($ev['status'] === 'ongoing') $statusBadge = 'bg-warning text-dark';
                                    elseif ($ev['status'] === 'completed') $statusBadge = 'bg-secondary';
                                ?>
                                <div class="col">
                                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden d-flex flex-column">
                                        <div class="position-relative">
                                            <img src="<?php echo $coverSrc ?: '../../Images/Others/events.jpeg'; ?>" class="w-100 object-fit-cover" style="height: 200px;" alt="<?php echo htmlspecialchars($ev['title']); ?>" loading="lazy">
                                            <span class="badge <?php echo $statusBadge; ?> position-absolute top-0 start-0 m-3 px-3 py-1.5 rounded-pill fw-semibold text-uppercase shadow-sm">
                                                <?php echo htmlspecialchars($ev['status']); ?>
                                            </span>
                                        </div>

                                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                            <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($ev['title']); ?></h5>
                                            
                                            <div class="text-secondary small mb-2 d-flex align-items-center gap-1.5">
                                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                                <span><?php echo htmlspecialchars($ev['location'] ?: 'GD Edu Tech Campus'); ?></span>
                                            </div>

                                            <div class="text-secondary small mb-3 d-flex align-items-center gap-1.5">
                                                <i class="bi bi-calendar-event-fill text-primary"></i>
                                                <span><?php echo !empty($ev['event_date']) ? date('M d, Y', strtotime($ev['event_date'])) : 'TBD'; ?> · <?php echo htmlspecialchars(substr($ev['event_time'] ?? '', 0, 5)); ?></span>
                                            </div>

                                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                                <div class="d-flex gap-1">
                                                    <a href="edit_event.php?id=<?php echo $ev['event_id']; ?>" class="action-icon" title="Edit Event">
                                                        <i class="bi bi-pencil-fill text-warning"></i>
                                                    </a>
                                                    <a href="index.php?delete=1&id=<?php echo $ev['event_id']; ?>" class="action-icon text-danger" onclick="return confirm('Delete this event?');" title="Delete Event">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </a>
                                                </div>

                                                <a href="../../event-details.php?event_id=<?php echo $ev['event_id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold">
                                                    <span>View Details</span>
                                                    <i class="bi bi-arrow-right-short"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                No events created yet.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link rounded-3 mx-1" href="?page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

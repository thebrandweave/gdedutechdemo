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
	header('Location: ./');
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
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
	<link rel="stylesheet" href="../css/style.css">
	<style>
		.event-card .cover-img { height: 180px; object-fit: cover; width: 100%; border-top-left-radius:.5rem; border-top-right-radius:.5rem; }
	</style>
</head>
<body>
<div class="container-fluid">
	<div class="row">
		<div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
			<div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
				<a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
					<span class="fs-5 fw-bolder" style="display:flex;align-items:center;color:black;"><img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
				</a>
				<ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
					<li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
					<li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
					<li class="w-100"><a href="../Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
					<li class="w-100">  <a href="../Applications/" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i> Scholarship Applications
                            </a></li>
					<li class="w-100"><a href="../Events/" class="nav-link active"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
					<li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-lightbulb me-2"></i> Quick Links
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                                <li><a class="dropdown-item" href="../index.php">Career portal</a></li>
                                <li><a class="dropdown-item" href="./Shop/shop.php">Shop</a></li>
                            </ul>
            </li>
            <li class="w-100"><a href="../Schedule/" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
					<li class="w-100"><a href="../Messages/" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
					<li class="w-100"><a href="../FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
					<li class="w-100"><a href="../Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
					<li class="w-100"><a href="../manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
					<li class="w-100"><a href="../pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
					<li class="w-100 mt-auto"><a href="../logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
				</ul>
			</div>
		</div>

		<div class="col py-3">
			<div class="container-fluid">
				<div class="row mb-4">
					<div class="col">
						<h2>Events Management</h2>
						<p class="text-muted">Create and manage events</p>
					</div>
					<div class="col-auto">
						<a href="./add_event.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Event</a>
					</div>
				</div>

				<?php if (isset($_SESSION['message'])): ?>
					<div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
						<?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php endif; ?>

				<div class="container">
					<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
						<?php if ($events_q): while ($ev = mysqli_fetch_assoc($events_q)): ?>
							<?php
								$cover = $ev['main_cover_image'] ?? '';
								$coverSrc = '';
								if ($cover) {
									$coverSrc = $cover;
									if (!preg_match('/\/(?:uploads|Images|assets)\//', $cover)) {
										$coverSrc = '../../uploads/events/' . htmlspecialchars($cover);
									}
								}
							?>
							<div class="col">
								<div class="card event-card h-100">
									<img src="<?php echo $coverSrc ?: '../images/default-course.png'; ?>" class="cover-img" alt="<?php echo htmlspecialchars($ev['title']); ?>" loading="lazy">
									<div class="card-body d-flex flex-column">
										<h5 class="card-title mb-1"><?php echo htmlspecialchars($ev['title']); ?></h5>
										<p class="text-muted mb-1"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($ev['location'] ?? ''); ?></p>
										<p class="text-muted mb-3"><i class="bi bi-calendar-event me-1"></i><?php echo htmlspecialchars($ev['event_date'] . ' ' . $ev['event_time']); ?></p>
										<div class="d-flex justify-content-between align-items-center mt-auto">
											<span class="badge bg-secondary text-capitalize"><?php echo htmlspecialchars($ev['status']); ?></span>
											<div class="btn-group">
												<a href="edit_event.php?id=<?php echo $ev['event_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
												<a href="?delete=1&id=<?php echo $ev['event_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this event?');" title="Delete"><i class="bi bi-trash"></i></a>
											</div>
										</div>
									</div>
									<div class="card-footer d-flex justify-content-between align-items-center">
										<small class="text-muted"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($ev['organizer_name'] ?? ''); ?></small>
										<small class="text-muted"><i class="bi bi-clock me-1"></i><?php echo date('M d, Y', strtotime($ev['created_at'])); ?></small>
									</div>
								</div>
							</div>
						<?php endwhile; endif; ?>
					</div>
				</div>

				<nav aria-label="Page navigation" class="mt-3">
					<ul class="pagination justify-content-center">
						<?php for ($i=1;$i<=$total_pages;$i++): ?>
							<li class="page-item <?php echo $i==$page?'active':''; ?>">
								<a class="page-link" href="index.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
							</li>
						<?php endfor; ?>
					</ul>
				</nav>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



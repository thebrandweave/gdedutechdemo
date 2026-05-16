<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../admin_login.php');
	exit();
}

require_once '../../Configurations/config.php';

// Handle blog deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
	$blog_id = intval($_GET['id']);
	$query = "DELETE FROM Blogs WHERE blog_id = $blog_id";
	if (mysqli_query($conn, $query)) {
		$_SESSION['message'] = "Blog deleted successfully.";
		$_SESSION['message_type'] = "success";
	} else {
		$_SESSION['message'] = "Error deleting blog: " . mysqli_error($conn);
		$_SESSION['message_type'] = "danger";
	}
	header("Location: ./");
	exit();
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 9; // 3 per row * 3 rows like courses
$offset = ($page - 1) * $limit;

// Fetch total number of blogs
$total_blogs_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM Blogs");
$total_blogs_row = mysqli_fetch_assoc($total_blogs_query);
$total_blogs = $total_blogs_row['count'] ?? 0;
$total_pages = max(1, ceil($total_blogs / $limit));

// Fetch blogs with pagination
$blogs_query = "SELECT b.*, u.username AS author_name
               FROM Blogs b
               LEFT JOIN Users u ON b.author_id = u.user_id
               ORDER BY b.created_at DESC
               LIMIT $limit OFFSET $offset";
$blogs_result = mysqli_query($conn, $blogs_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Blog Management - GD Edu Tech</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
	<link rel="stylesheet" href="../css/style.css">
	<style>
		.blog-card .cover-img {
			height: 180px;
			object-fit: cover;
			width: 100%;
			border-top-left-radius: .5rem;
			border-top-right-radius: .5rem;
		}
		.badge-status { text-transform: capitalize; }
		.blog-description { color: #6c757d; min-height: 48px; }
	</style>
</head>

<body>
	<div class="container-fluid">
		<div class="row">
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
							<a href="../Courses/" class="nav-link">
								<i class="bi bi-book me-2"></i> Courses
							</a>
						</li>
                        <li class="w-100">
                            <a href="../Blogs/" class="nav-link active">
                                <i class="bi bi-journal-text me-2"></i> Blogs
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Events/" class="nav-link">
                                <i class="bi bi-calendar2-event me-2"></i> Events
                            </a>
                        </li>
						<li class="w-100 dropdown">
							<a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="bi bi-lightbulb me-2"></i> Quick Links
							</a>
							<ul class="dropdown-menu" aria-labelledby="quizDropdown">
								<li><a class="dropdown-item" href="../Career/index.php">Career portal</a></li>
								<li><a class="dropdown-item" href="../Shop/shop.php">Shop</a></li>
							</ul>
						</li>
						<li class="w-100">
							<a href="../Schedule/index.php" class="nav-link">
								<i class="bi bi-calendar-event me-2"></i> Schedule
							</a>
						</li>
						<li class="w-100">
							<a href="../Messages/index.php" class="nav-link">
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
							<h2>Blog Management</h2>
							<p class="text-muted">Create, manage and publish blogs</p>
						</div>
						<div class="col-auto">
							<a href="./add_blog.php" class="btn btn-primary">
								<i class="bi bi-plus-circle me-2"></i>Add New Blog
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

					<div class="container">
						<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
						<?php if ($blogs_result): ?>
							<?php while ($blog = mysqli_fetch_assoc($blogs_result)): ?>
								<div class="col">
									<div class="card blog-card h-100">
										<?php
											$cover = $blog['main_cover_image'] ?? '';
											$coverSrc = '';
                                            if (!empty($cover)) {
												// If stored path looks absolute or relative, use as-is; else try uploads/blogs
												$coverSrc = $cover;
                                if (!preg_match('/\/(?:uploads|Images|assets)\//', $cover)) {
                                    $coverSrc = '../../uploads/blogs/' . htmlspecialchars($cover);
												}
											}
										?>
                                        <img src="<?php echo $coverSrc ?: '../../uploads/1732891498_images.jpg'; ?>" class="cover-img" alt="<?php echo htmlspecialchars($blog['title']); ?>" loading="lazy">
										<div class="card-body d-flex flex-column">
											<h5 class="card-title mb-3"><?php 
											echo htmlspecialchars(mb_strimwidth($blog['title'], 0, 40, '...')); ?></h5>
											<p class="blog-description mb-3"><?php
												$excerpt = $blog['content'] ?? '';
												$excerpt = strip_tags($excerpt);
												$excerpt = strlen($excerpt) > 140 ? substr($excerpt, 0, 100) . '...' : $excerpt;
												echo htmlspecialchars($excerpt);
											?></p>
											<div class="d-flex justify-content-between align-items-center mt-auto">
												<span class="badge bg-secondary badge-status"><?php echo htmlspecialchars($blog['status']); ?></span>
												<div class="btn-group">
													<?php if ($blog['status'] === 'draft'): ?>
														<a href="publish_blog.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Publish this blog?');"><i class="bi bi-send me-1"></i>Publish</a>
													<?php endif; ?>
													<a href="edit_blog.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Blog"><i class="bi bi-pencil"></i></a>
													<a href="?delete=1&id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this blog?');" title="Delete Blog"><i class="bi bi-trash"></i></a>
												</div>
											</div>
										</div>
										<div class="card-footer d-flex justify-content-between align-items-center">
											<small class="text-muted"><i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></small>
											<small class="text-muted"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($blog['author_name'] ?? 'Unknown'); ?></small>
										</div>
									</div>
								</div>
							<?php endwhile; ?>
						<?php else: ?>
							<div class="col-12">
								<div class="alert alert-warning mb-0">Failed to load blogs: <?php echo htmlspecialchars(mysqli_error($conn)); ?></div>
							</div>
						<?php endif; ?>
						</div>
					</div>

					<!-- Pagination -->
					<nav aria-label="Page navigation" class="mt-3">
						<ul class="pagination justify-content-center">
							<?php for ($i = 1; $i <= $total_pages; $i++): ?>
								<li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
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



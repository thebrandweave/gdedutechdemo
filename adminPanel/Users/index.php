<?php
session_start();

// Database connection
require_once '../../Configurations/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Handle user deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    $query = "DELETE FROM Users WHERE user_id = $user_id";
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "User deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting user: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }

    header("Location: index.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch total number of users
$total_users_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM Users");
$total_users_row = mysqli_fetch_assoc($total_users_query);
$total_users = $total_users_row['count'] ?? 0;
$total_pages = ceil($total_users / $limit);

// Fetch users with pagination
$query = "SELECT * FROM Users ORDER BY date_joined DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$admin_name = $_SESSION['username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - GD Edu Tech Admin</title>
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
                    <li class="w-100"><a href="../Career/" class="nav-link"><i class="bi bi-briefcase me-2"></i> Careers</a></li>
                    <li class="w-100"><a href="../social_links.php" class="nav-link"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100"><a href="../Schedule/index.php" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="../feedback/feedback.php" class="nav-link"><i class="bi bi-chat-square-heart me-2"></i> Feedback</a></li>
                    <li class="w-100"><a href="../Messages/index.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="../FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="../manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="../pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col min-vh-100 d-flex flex-column">
                
                <!-- Top Header -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">User Directory</h4>
                        <span class="text-muted small">Manage platform registered users, student accounts, and admin roles</span>
                    </div>

                    <a href="add_user.php" class="btn btn-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>Add New User
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

                    <!-- Users Table Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Platform Accounts Directory</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-1.5 rounded-pill fw-semibold">
                                Total: <?php echo $total_users; ?> Users
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>User Account</th>
                                        <th>Full Name</th>
                                        <th>Email Address</th>
                                        <th>Role</th>
                                        <th>Account Status</th>
                                        <th>Joined Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                        <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                            <?php
                                                $roleBadge = 'bg-info bg-opacity-10 text-info border-info-subtle';
                                                if ($user['role'] === 'admin') $roleBadge = 'bg-primary bg-opacity-10 text-primary border-primary-subtle';
                                                elseif ($user['role'] === 'instructor') $roleBadge = 'bg-warning bg-opacity-20 text-dark border-warning-subtle';

                                                $statusBadge = ($user['status'] ?? 'active') === 'active' ? 'bg-success bg-opacity-10 text-success border-success-subtle' : 'bg-danger bg-opacity-10 text-danger border-danger-subtle';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark fs-6 d-block"><?php echo htmlspecialchars($user['username']); ?></strong>
                                                            <span class="text-muted small">ID: #<?php echo $user['user_id']; ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-dark font-weight-medium">
                                                        <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="font-monospace text-secondary"><?php echo htmlspecialchars($user['email']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge border px-2.5 py-1 rounded-pill fw-semibold <?php echo $roleBadge; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge border px-2.5 py-1 rounded-pill fw-semibold <?php echo $statusBadge; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($user['status'] ?? 'active')); ?>
                                                    </span>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo !empty($user['date_joined']) ? date('M d, Y', strtotime($user['date_joined'])) : ( !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : '-' ); ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="action-icon" title="Edit User">
                                                            <i class="bi bi-pencil-fill text-warning"></i>
                                                        </a>
                                                        <a href="index.php?delete=1&id=<?php echo $user['user_id']; ?>" class="action-icon text-danger" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete User">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No users registered yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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
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

    header("Location: users.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch total number of users
$total_users_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM Users");
$total_users_row = mysqli_fetch_assoc($total_users_query);
$total_users = $total_users_row['count'];
$total_pages = ceil($total_users / $limit);

// Fetch users with pagination
$query = "SELECT * FROM Users ORDER BY date_joined DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Get admin details from session
$admin_name = $_SESSION['first_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                            <img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
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
                            <a href="./" class="nav-link active">
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
                            <h2>User Management</h2>
                            <p class="text-muted">Manage and monitor platform users</p>
                        </div>
                        <div class="col-auto">
                            <a href="add_user.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Add New User
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

                    <!-- Users Table -->
                    <!-- Replace the existing table section with this code -->
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th class="py-3 px-4 fw-bold">ID</th>
                                            <th class="py-3 px-4 fw-bold">Username</th>
                                            <th class="py-3 px-4 fw-bold">Email</th>
                                            <th class="py-3 px-4 fw-bold">First Name</th>
                                            <th class="py-3 px-4 fw-bold">Last Name</th>
                                            <th class="py-3 px-4 fw-bold">Role</th>
                                            <th class="py-3 px-4 fw-bold">Status</th>
                                            <th class="py-3 px-4 fw-bold">Date Joined</th>
                                            <th class="py-3 px-4 fw-bold text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                            <tr class="align-middle">
                                                <td class="px-4"><?php echo htmlspecialchars($user['user_id']); ?></td>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                        </div>
                                                        <?php echo htmlspecialchars($user['username']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-4"><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td class="px-4"><?php echo htmlspecialchars($user['first_name']); ?></td>
                                                <td class="px-4"><?php echo htmlspecialchars($user['last_name']); ?></td>
                                                <td class="px-4">
                                                    <span class="badge bg-secondary text-white">
                                                        <?php echo htmlspecialchars($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-4">
                                                    <span class="badge 
                            <?php
                                            echo $user['status'] == 'active' ? 'bg-success' : ($user['status'] == 'inactive' ? 'bg-warning' : 'bg-danger');
                            ?> text-white">
                                                        <?php echo htmlspecialchars($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-4"><?php echo date('Y-m-d', strtotime($user['date_joined'])); ?></td>
                                                <td class="px-4 text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_user.php?php echo $user['user_id']; ?>"
                                                            class="btn btn-sm btn-outline-primary rounded me-1"
                                                            title="Edit User">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="users.php?delete=1&id=<?php echo $user['user_id']; ?>"
                                                            class="btn btn-sm btn-outline-danger rounded"
                                                            onclick="return confirm('Are you sure you want to delete this user?');"
                                                            title="Delete User">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
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
</body>

</html>
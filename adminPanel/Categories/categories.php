<?php
session_start();

// Database connection
require_once '../../Configurations/config.php';

// Handle category deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $category_id = intval($_GET['id']);

    $query = "DELETE FROM Categories WHERE category_id = $category_id";
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Category deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting category: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }

    header("Location: categories.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch total number of categories
$total_categories_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM Categories ");
$total_categories_row = mysqli_fetch_assoc($total_categories_query);
$total_categories = $total_categories_row['count'];
$total_pages = ceil($total_categories / $limit);

// Fetch categories with pagination
$query = "SELECT * FROM Categories ORDER BY created_at ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Get admin details from session
$admin_name = $_SESSION['first_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - GD Edu Tech</title>
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
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;"><img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link active">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                             <a href="./Applications/" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i> Scholarship Applications
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
                            <h2>Category Management</h2>
                            <p class="text-muted">Manage and organize course categories</p>
                        </div>
                        <div class="col-auto">
                            <a href="./insert_category.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Add New Category
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

                    <!-- Categories Table -->
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th class="py-3 px-4 fw-bold">ID</th>
                                            <th class="py-3 px-4 fw-bold">Name</th>
                                            <th class="py-3 px-4 fw-bold">Description</th>
                                            <th class="py-3 px-4 fw-bold">Created At</th>
                                            <th class="py-3 px-4 fw-bold">Last Updated</th>
                                            <th class="py-3 px-4 fw-bold text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($category = mysqli_fetch_assoc($result)): ?>
                                            <tr class="align-middle">
                                                <td class="px-4"><?php echo htmlspecialchars($category['category_id']); ?></td>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <?php echo strtoupper(substr($category['name'], 0, 1)); ?>
                                                        </div>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-4"><?php echo htmlspecialchars(substr($category['description'], 0, 100)) . (strlen($category['description']) > 100 ? '...' : ''); ?></td>
                                                <td class="px-4"><?php echo date('Y-m-d H:i', strtotime($category['created_at'])); ?></td>
                                                <td class="px-4"><?php echo date('Y-m-d H:i', strtotime($category['updated_at'])); ?></td>
                                                <td class="px-4 text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_category.php?id=<?php echo $category['category_id']; ?>"
                                                            class="btn btn-sm btn-outline-primary rounded me-1"
                                                            title="Edit Category">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="categories.php?delete=1&id=<?php echo $category['category_id']; ?>"
                                                            class="btn btn-sm btn-outline-danger rounded"
                                                            onclick="return confirm('Are you sure you want to delete this category?');"
                                                            title="Delete Category">
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

                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
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
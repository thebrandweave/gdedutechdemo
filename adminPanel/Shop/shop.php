<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}


// Fetch accessories from the database
$accessories_query = "SELECT * FROM Accessories"; // Assuming you have an Accessories table
$accessories_result = mysqli_query($conn, $accessories_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - GD Edu Tech</title>
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
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;"><img height="35px" src="./images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../index.php" class="nav-link">
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
                          <a href="../Applications/" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i> Scholarship Applications
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
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Shop Accessories</h2>
                            <p class="text-muted">Manage and add accessories available for purchase.</p>
                        </div>
                    </div>

                    <!-- Display Success/Error Messages -->
                    <?php if (isset($_GET['message'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>

                    <!-- Add Item Button -->
                    <div class="mb-3">
                        <a href="add_item.php" class="btn btn-danger">
                            <i class="bi bi-plus-circle me-2"></i> Add Item
                        </a>
                    </div>

                    <!-- Accessories Cards -->
                    <div class="row">
                        <?php while ($accessory = mysqli_fetch_assoc($accessories_result)): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <img src="<?php echo htmlspecialchars($accessory['image']); ?>" class="card-img-top img-square" alt="<?php echo htmlspecialchars($accessory['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($accessory['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($accessory['description']); ?></p>
                                        <p class="card-text"><strong>Price: ₹<?php echo number_format($accessory['price'], 2); ?></strong></p>
                                        <a href="edit_item.php?id=<?php echo $accessory['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="delete_item.php?id=<?php echo $accessory['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete();">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item? This action cannot be undone.');
        }
    </script>

    <style>
        .img-square {
            width: 100%; /* Make the image take the full width of the card */
            height: 200px; /* Set a fixed height */
            object-fit: cover; /* Cover the area while maintaining aspect ratio */
        }
    </style>
</body>

</html>
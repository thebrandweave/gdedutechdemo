<?php
session_start();
require_once '../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
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
    <link rel="stylesheet" href="../assets/css/student_dashboard.css">

    <link rel="icon" type="image/png" href="../Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="../Images/Logos/GD_Only_logo.png">
    <style>
        .img-square {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .course-card {
            transition: transform 0.2s;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        @media (max-width: 768px) {

        /* Sidebar styles */
        .sidebar {
            transition: transform 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #2c3e50;
            z-index: 1000;
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }
    }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;">
                            <img height="35px" src="../Images/Logos/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="./" class="nav-link text-white">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./MyCourses/" class="nav-link text-white">
                                <i class="bi bi-book me-2"></i> My Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Categories/" class="nav-link text-white">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Schedule/" class="nav-link text-white">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Messages/" class="nav-link text-white">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Profile/" class="nav-link text-white">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Resources/index.php" class="nav-link text-white">
                                <i class="bi bi-file-earmark-text me-2"></i> Resources
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./shop.php" class="nav-link text-white active">
                                <i class="bi bi-shop me-2"></i> Shop
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
            <div class="col py-3 ">
                <div class="d-md-none">
                <button id="sidebarToggle" class="btn btn- ">
                    <i class="bi bi-list"></i> 
                </button></div>
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Shop Accessories</h2>
                            <p class="text-muted">Browse and purchase accessories available for you.</p>
                        </div>
                    </div>

                    <!-- Accessories Cards -->
                    <div class="row">
                        <?php while ($accessory = mysqli_fetch_assoc($accessories_result)): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card course-card">
                                    <img src="<?php echo '../uploads/shop_items/' . htmlspecialchars($accessory['image']); ?>" class="card-img-top img-square" alt="<?php echo htmlspecialchars($accessory['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($accessory['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($accessory['description']); ?></p>
                                        <p class="card-text"><strong>Price: ₹<?php echo number_format($accessory['price'], 2); ?></strong></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="https://wa.me/8867575821?text=I%20am%20interested%20in%20the%20product%20<?php echo urlencode($accessory['name']); ?>.%20Details:%20<?php echo urlencode($accessory['description']); ?>%20Price:%20₹<?php echo number_format($accessory['price'], 2); ?>" class="btn btn-success me-2" target="_blank">
                                                <i class="bi bi-whatsapp me-1"></i> Buy Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : 'auto';
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = 'auto';
            });

            // Close sidebar when a menu item is clicked
            const menuItems = document.querySelectorAll('.sidebar-menu .nav-link');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.style.overflow = 'auto';
                });
            });
        });
    </script>
</body>

</html>
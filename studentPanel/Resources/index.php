<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch all question papers from the database
$papers_query = "SELECT * FROM question_papers";
$papers_result = mysqli_query($conn, $papers_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">
    <link rel="stylesheet" href="../../css/customBootstrap.css">
            
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="../../Images/Logos/GD_Only_logo.png">
    <style>
        :root {
            --primary-color: #2c3e50;
        }
        /* Sidebar styles (copied from MyCourses) */
        .sidebar {
            transition: transform 0.3s ease;
            transform: translateX(-100%);
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #2c3e50;
            z-index: 1000;
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
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
            }
            .sidebar-overlay {
                display: none;
            }
            .main-content {
                margin-left: 250px;
            }
        }
        @media (max-width: 767.98px) {
            .main-content {
                margin-top: 0;
                margin-left: 250px;
            }
        }
        .main-content {
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <!-- Hamburger Button -->
    <div class="topbar d-flex justify-content-between align-items-center p-2">
        <button class="btn btn-outline-secondary sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <span class="fw-bold"></span>
    </div>
    <!-- Mobile Navigation -->
    <!-- <div class="mobile-nav d-flex align-items-center">
        <button class="btn text-white" id="sidebarToggle">
            <i class="bi bi-list fs-3"></i>
        </button>
        <span class="ms-3 fs-4 text-white">GD Edu Tech</span>
    </div> -->

    <!-- Sidebar Backdrop -->
    <!-- <div class="sidebar-backdrop" id="sidebarBackdrop"></div> -->

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar" id="sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;">
                            <img height="35px" src="../../Images/Logos/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link text-white">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../MyCourses/" class="nav-link text-white">
                                <i class="bi bi-book me-2"></i> My Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link text-white">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule/" class="nav-link text-white">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/" class="nav-link text-white">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link text-white active">
                                <i class="bi bi-file-earmark-text me-2"></i> Resources
                            </a>
                        </li>
                        <li class="w-100 mt-auto">
                            <a href="../../logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Sidebar Overlay -->
            <div class="sidebar-overlay" id="sidebar-overlay"></div>

            <!-- Main Content -->
            <div class="col py-3 main-content">
                <h3 class="mb-4">Available Question Papers</h3>
                <div class="row g-4">
                    <?php while ($paper = mysqli_fetch_assoc($papers_result)): ?>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($paper['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($paper['description']); ?></p>
                                    <?php
                                    // Check if the user has access to the paper
                                    $user_id = $_SESSION['user_id'];
                                    $access_query = "SELECT * FROM access_requests WHERE paper_id = {$paper['id']} AND user_id = $user_id AND status = 'granted'";
                                    $access_result = mysqli_query($conn, $access_query);
                                    $has_access = mysqli_num_rows($access_result) > 0;

                                    if ($paper['status'] === 'open' || $has_access): ?>
                                        <a href="<?php echo '../uploads/question_papers/' . htmlspecialchars($paper['pdf']); ?>" class="btn btn-primary" target="_blank">View Paper</a>
                                    <?php else: ?>
                                        <a href="https://api.whatsapp.com/send?phone=8867575821&text=Request%20to%20access%20the%20paper%20entitled%20<?php echo urlencode($paper['title']); ?>"
                                            class="btn btn-info"
                                            target="_blank"
                                            onclick="event.preventDefault(); requestAccess(<?php echo $paper['id']; ?>); window.open(this.href, '_blank');">
                                            Access Paper
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

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
        });

        // Access Request Function
        function requestAccess(paperId) {
            var formData = new FormData();
            formData.append('paper_id', paperId);

            fetch('request_access.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    console.log('Access request sent successfully');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>
</body>

</html>
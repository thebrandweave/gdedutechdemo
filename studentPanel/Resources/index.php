<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_page.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all question papers from the database
$papers_query = "SELECT * FROM question_papers ORDER BY id DESC";
$papers_result = mysqli_query($conn, $papers_query);
$paper_count = $papers_result ? mysqli_num_rows($papers_result) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources & Question Papers - GD Edu Tech</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">

    <style>
        :root {
            --admin-primary: #0d7298;
            --admin-primary-dark: #065d7d;
            --admin-dark-bg: #0f172a;
            --admin-dark-surface: #1e293b;
            --admin-light-bg: #f8fafc;
            --admin-border-color: #e2e8f0;
        }

        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: var(--admin-light-bg);
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
            min-height: 100vh;
            color: #ffffff;
            box-shadow: 4px 0 25px rgba(15, 23, 42, 0.15);
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: #94a3b8 !important;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 12px 18px;
            margin: 3px 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.25s ease;
            text-decoration: none !important;
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
        }

        .sidebar .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(90deg, rgba(13, 114, 152, 0.9) 0%, rgba(6, 93, 125, 0.95) 100%) !important;
            box-shadow: 0 8px 20px rgba(13, 114, 152, 0.35);
            font-weight: 700 !important;
        }

        .sidebar .nav-link.active i {
            color: #38bdf8 !important;
        }

        .sidebar .nav-link.text-danger {
            color: #f87171 !important;
            background: rgba(239, 68, 68, 0.1) !important;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .sidebar .nav-link.text-danger:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
        }

        /* Layout bounds */
        .row.flex-nowrap > .col,
        .main-content {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        /* Paper Cards */
        .paper-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
            background: #ffffff;
            transition: all 0.3s ease;
            height: 100%;
        }

        .paper-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 35px -10px rgba(15, 23, 42, 0.12) !important;
        }

        .paper-icon-box {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
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
                        <span class="text-info small fw-semibold">● Student Portal</span>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../MyCourses/" class="nav-link"><i class="bi bi-journal-bookmark"></i> My Courses</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid"></i> Categories</a></li>
                    <li class="w-100"><a href="../Schedule/" class="nav-link"><i class="bi bi-calendar-event"></i> Schedule</a></li>
                    <li class="w-100"><a href="../Messages/" class="nav-link"><i class="bi bi-chat-dots"></i> Messages</a></li>
                    <li class="w-100"><a href="../Profile/" class="nav-link"><i class="bi bi-person"></i> Profile</a></li>
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-file-earmark-text"></i> Resources</a></li>

                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col main-content min-vh-100 d-flex flex-column" style="min-width: 0; overflow-x: hidden;">
                
                <!-- Top Header Bar -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Question Papers & Resources</h4>
                        <span class="text-muted small">Access exam preparation archives, sample papers, and study material</span>
                    </div>

                    <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-2 rounded-pill font-monospace">
                        <i class="bi bi-file-earmark-pdf me-1.5"></i><?php echo $paper_count; ?> Papers Available
                    </span>
                </div>

                <div class="p-4 flex-grow-1">

                    <!-- Question Papers Grid -->
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
                        <?php if ($papers_result && mysqli_num_rows($papers_result) > 0): ?>
                            <?php while ($paper = mysqli_fetch_assoc($papers_result)): ?>
                                <?php
                                    $paper_id = intval($paper['id']);
                                    $access_query = "SELECT status FROM access_requests WHERE paper_id = $paper_id AND user_id = $user_id AND status = 'granted'";
                                    $access_result = mysqli_query($conn, $access_query);
                                    $has_access = ($access_result && mysqli_num_rows($access_result) > 0);
                                    $isOpen = ($paper['status'] === 'open' || $has_access);
                                ?>
                                <div class="col">
                                    <div class="card paper-card border-0 d-flex flex-column p-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="paper-icon-box">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                            </div>
                                            <?php if ($isOpen): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
                                                    <i class="bi bi-unlock-fill me-1"></i>Open Access
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning bg-opacity-20 text-dark border border-warning-subtle rounded-pill px-2.5 py-1 small">
                                                    <i class="bi bi-lock-fill me-1"></i>Access Required
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <h6 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($paper['title']); ?></h6>
                                        <p class="text-secondary small flex-grow-1 mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo htmlspecialchars($paper['description'] ?: 'Official question paper and sample exam resource.'); ?>
                                        </p>

                                        <?php if ($isOpen): ?>
                                            <a href="<?php echo '../uploads/question_papers/' . htmlspecialchars($paper['pdf']); ?>" class="btn btn-primary w-100 rounded-pill fw-semibold py-2 mt-auto" target="_blank">
                                                <i class="bi bi-eye-fill me-1.5"></i>View Question Paper
                                            </a>
                                        <?php else: ?>
                                            <a href="https://api.whatsapp.com/send?phone=8867575821&text=Request%20to%20access%20the%20paper%20entitled%20<?php echo urlencode($paper['title']); ?>"
                                               class="btn btn-success w-100 rounded-pill fw-semibold py-2 mt-auto"
                                               target="_blank"
                                               onclick="event.preventDefault(); requestAccess(<?php echo $paper_id; ?>); window.open(this.href, '_blank');"
                                            >
                                                <i class="bi bi-whatsapp me-1.5"></i>Request Paper Access
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <div class="card border-0 shadow-sm rounded-4 p-5">
                                    <i class="bi bi-folder-x text-secondary opacity-50 display-1 mb-3"></i>
                                    <h4 class="fw-bold text-dark mb-2">No Question Papers Found</h4>
                                    <p class="text-muted mb-0">Question papers and exam materials will be listed here when made available.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
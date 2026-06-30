<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Fetch question papers from the database
$papers_query = "SELECT * FROM question_papers";
$papers_result = mysqli_query($conn, $papers_query);

// Fetch access requests from the database
$requests_query = "SELECT ar.id, qp.title, u.username, ar.request_time FROM access_requests ar
                   JOIN question_papers qp ON ar.paper_id = qp.id
                   JOIN Users u ON ar.user_id = u.user_id
                   WHERE ar.status = 'pending'";
$requests_result = mysqli_query($conn, $requests_query);

if (!$requests_result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - GD Edu Tech</title>
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
                            <a href="./Categories/" class="nav-link">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
<li class="w-100">
                            <a href="./Admissions/" class="nav-link">
                                <i class="bi bi-person-plus me-2"></i> Student Admission
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                         <li class="w-100">
                           <a href="../Applications/" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i> Scholarship Applications
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Resources/index.php" class="nav-link active">
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

            <!-- Main Content -->
            <div class="col py-3">
                <h3 class="mb-4">Manage Resources</h3>
                <a href="add_resource.php" class="btn btn-primary mb-3">Add Resource</a>
                <div class="row g-4">
                    <?php while ($paper = mysqli_fetch_assoc($papers_result)): ?>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($paper['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($paper['description']); ?></p>
                                    <a href="<?php echo '../../uploads/question_papers/' . htmlspecialchars($paper['pdf']); ?>" class="btn btn-success" target="_blank">View Paper</a>
                                    <a href="edit_resource.php?id=<?php echo $paper['id']; ?>" class="btn btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="delete_resource.php" method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $paper['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this resource?');" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <h3 class="mb-4">Access Requests</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Paper Title</th>
                            <th>User</th>
                            <th>Request Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($request = mysqli_fetch_assoc($requests_result)): ?>
                            <tr>
                                <td><?php echo $request['id']; ?></td>
                                <td><?php echo htmlspecialchars($request['title']); ?></td>
                                <td><?php echo htmlspecialchars($request['username']); ?></td>
                                <td><?php echo htmlspecialchars($request['request_time']); ?></td>
                                <td>
                                    <form action="grant_access.php" method="POST" class="d-inline">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" class="btn btn-success">Grant Access</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
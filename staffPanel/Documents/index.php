<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

// Get staff details from session
$staff_name = $_SESSION['username'] ?? 'Staff';

// Handle document deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $document_id = intval($_GET['id']);
    
    // Delete the document
    $delete_document_query = "DELETE FROM Documents WHERE document_id = $document_id";
    if (mysqli_query($conn, $delete_document_query)) {
        $_SESSION['message'] = "Document deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting document: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    header("Location: index.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Fetch total number of documents
$total_documents_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM Documents");
$total_documents_row = mysqli_fetch_assoc($total_documents_query);
$total_documents = $total_documents_row['count'];
$total_pages = ceil($total_documents / $limit);

// Fetch documents
$query = "SELECT * FROM Documents ORDER BY upload_date DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="./course.css">
    <link rel="stylesheet" href="../../adminPanel/css/style.css">

    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                            <img height="35px" src="../../staffPanel/images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../index.php" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link ">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Quiz/" class="nav-link">
                                <i class="bi bi-lightbulb me-2"></i> Quiz
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Documents/" class="nav-link active">
                                <i class="bi bi-chat-dots me-2"></i> Documents
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
                            <h2>Document Management</h2>
                            <p class="text-muted">Manage and organize documents</p>
                        </div>
                        <div class="col-auto">
                            <a href="add_document.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Create New Document
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

                    <!-- Documents Grid -->
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <?php while ($document = mysqli_fetch_assoc($result)): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($document['title']); ?></h5>
                                        <p class="card-text">Price: <?php echo htmlspecialchars($document['price']); ?></p>
                                        <p class="card-text">Uploaded by: <?php echo htmlspecialchars($document['user_id']); ?></p>
                                        <p class="card-text">Upload Date: <?php echo date('M d, Y', strtotime($document['upload_date'])); ?></p>
                                        <div class="btn-group" role="group">
                                            <a href="edit_document.php?id=<?php echo $document['document_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Document">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="index.php?delete=1&id=<?php echo $document['document_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure? This will delete the document.');" title="Delete Document">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4">
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

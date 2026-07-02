<?php
session_start();

// Admin auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once '../../Configurations/config.php';

// Fetch applications
$result = mysqli_query($conn, "SELECT * FROM applications ORDER BY id DESC");

// Admin name
$admin_name = $_SESSION['username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applications - Admin Panel</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Your custom CSS -->
<link rel="stylesheet" href="../css/style.css">

<style>
    .photo {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ddd;
    }
    .anyclass{
        background-color:#b1e7fb;
        padding: 8px;
        border-radius: 4px;
        text-decoration: none;
        color:black;
        font-weight: bold;
    }
        .hide-scrollbar::-webkit-scrollbar {
            display: none; /* Chrome, Safari and Opera */
        }
        .hide-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
</style>
</head>

<body>

<div class="container-fluid">
        <div class="row flex-nowrap"> <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar"> 
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 h-100">
        <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-decoration-none">
            <span class="fs-5 fw-bolder d-flex align-items-center">
                <img height="35px" src="../images/edutechLogo.png">
                &nbsp; GD Edu Tech
            </span>
        </a>

        <ul class="nav nav-pills flex-column w-100">

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
                <a href="../Admissions/" class="nav-link">
                    <i class="bi bi-person-plus me-2"></i> Student Admission
                </a>
            </li>
  <li class="w-100">
                <a href="../Courses/" class="nav-link">
                    <i class="bi bi-book me-2"></i> Courses
                </a>
            </li>
            <li class="w-100">
                <a href="../Applications/" class="nav-link active">
                    <i class="bi bi-journal-text me-2"></i> Scholarship Applications
                </a>
            </li>
             <li class="w-100">
                <a href="../Events/" class="nav-link">
                    <i class="bi bi-calendar2-event me-2"></i> Events
                </a>
            </li>
               <li class="w-100">
                            <a href="../social_links.php" class="nav-link">
                                <i class="bi bi-link-45deg me-2"></i> Social Links
                            </a>
                        </li>
            <li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-lightbulb me-2"></i> Quick Links
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                                <li><a class="dropdown-item" href="../index.php">Career portal</a></li>
                                <li><a class="dropdown-item" href="./Shop/shop.php">Shop</a></li>
                            </ul>
            </li>
            <li class="w-100">
                <a href="../Schedule/" class="nav-link">
                    <i class="bi bi-calendar-event me-2"></i> Schedule
                </a>
            </li>
             <li class="w-100">
                            <a href="../feedback/feedback.php" class="nav-link">
                                <i class="bi bi-chat-square-heart"></i> Feedback
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

            <li class="w-100">
                <a href="../logout.php" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
            

        </ul>
    </div>
</div>

<!-- ✅ Main Content -->
<div class="col py-3">
<div class="container-fluid">

<!-- Header -->
<div class="row mb-4">
    <div class="col">
        <h2>Applications</h2>
        <p class="text-muted">Manage student applications</p>
    </div>
</div>

<!-- Table -->
<div class="card shadow-sm">
<div class="card-body p-0">
<div class="table-responsive">

<table class="table table-hover mb-0">
<thead class="bg-primary text-white">
<tr>
    <th>Name</th>
    <th>Phone</th>
    <th>Course</th>
    <th>Medium</th>
    <th>Languages</th>
    <th>School</th>
    <th>Document</th>
    <th>Date</th>
    <th>Photo</th>
</tr>
</thead>

<tbody>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr class="align-middle">

<td>
    <?php echo $row['first_name']." ".$row['last_name']; ?>
</td>

<td><?php echo $row['phone']; ?></td>
<td><?php echo $row['course']; ?></td>
<td><?php echo $row['medium']; ?></td>
<td><?php echo $row['languages']; ?></td>
<td><?php echo $row['school']; ?></td>

<td>
<?php if($row['document']) { ?>
<a href="../../uploads/<?php echo $row['document']; ?>" target="_blank"
   class="anyclass">
   <i class="bi bi-file-earmark"></i> View
</a>
<?php } ?>

</td>




<td>
<?php echo date('Y-m-d', strtotime($row['created_at'])); ?>
</td>
<td>
<?php if($row['photo']) { ?>
<a href="../../uploads/<?php echo $row['photo']; ?>" target="_blank" class="d-block mt-1">
    <img src="../../uploads/<?php echo $row['photo']; ?>" 
         alt="Profile Photo" 
         class="photo">
         
    
      
    </a>
<?php } else { ?>
    <div class="photo" style="background: #eee; display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-person"></i>
    </div>
<?php } ?>
</td>
</tr>
<?php } ?>
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
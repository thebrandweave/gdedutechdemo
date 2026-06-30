<?php
echo "PHP is running<br>";
require_once '../../Configurations/config.php';
echo "Config loaded, conn status: " . ($conn->connect_error ? $conn->connect_error : "Connected OK");
$r = mysqli_query($conn, "SELECT COUNT(*) c FROM student_admissions");
if (!$r) { echo "<br>Query error: " . mysqli_error($conn); }
else { $row = mysqli_fetch_assoc($r); echo "<br>Row count: " . $row['c']; }
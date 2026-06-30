<?php
require_once 'Configurations/config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM student_admissions ORDER BY id DESC LIMIT 10 OFFSET 0";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "Query successful! Number of rows: " . mysqli_num_rows($result) . "\n";
    while ($row = mysqli_fetch_assoc($result)) {
        print_r($row);
    }
} else {
    echo "Query failed: " . mysqli_error($conn) . "\n";
}
?>

<?php 
$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'gdedutech.in') !== false) {
    // If the URL contains 'gdgoldanddiamonds.in', use this connection
    $conn = new mysqli("localhost", "u593219986_root", "GdGold&Diamonds1234", "u593219986_gd_gold");
    // echo "this is in server";

} else {
    // Otherwise, use this connection
    $conn = new mysqli("localhost", "root", "", "gd_edu_tech");
    // echo "this is in local host";

}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);

}
?>

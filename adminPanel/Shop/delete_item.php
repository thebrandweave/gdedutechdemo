<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}


// Check if the ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the image path before deleting the accessory
    $stmt = $conn->prepare("SELECT image FROM Accessories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $accessory = $result->fetch_assoc();
        $imagePath = $accessory['image'];

        // Delete the accessory from the database
        $delete_stmt = $conn->prepare("DELETE FROM Accessories WHERE id = ?");
        $delete_stmt->bind_param("i", $id);

        if ($delete_stmt->execute()) {
            // Delete the image file from the server
            if (file_exists($imagePath)) {
                unlink($imagePath); // Remove the image file
            }
            // Redirect back to the shop page with a success message
            header("Location: shop.php?message=Item deleted successfully.");
            exit();
        } else {
            // Redirect back to the shop page with an error message
            header("Location: shop.php?error=Error deleting item.");
            exit();
        }
    } else {
        // Redirect back to the shop page if no accessory found
        header("Location: shop.php?error=Item not found.");
        exit();
    }
} else {
    // Redirect back to the shop page if no ID is provided
    header("Location: shop.php");
    exit();
}
?>

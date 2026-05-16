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

    // Fetch the existing accessory details
    $stmt = $conn->prepare("SELECT * FROM Accessories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Redirect if no accessory found
        header("Location: shop.php?error=Item not found.");
        exit();
    }

    $accessory = $result->fetch_assoc();
} else {
    // Redirect if no ID is provided
    header("Location: shop.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_FILES['image'];

    // Prepare the update statement
    $update_stmt = $conn->prepare("UPDATE Accessories SET name = ?, description = ?, price = ? WHERE id = ?");
    $update_stmt->bind_param("ssdi", $name, $description, $price, $id);

    if ($update_stmt->execute()) {
        // Handle image upload if a new image is provided
        if ($image['error'] == 0) {
            $imagePath = '../../uploads/shop_items/' . basename($image['name']);
            move_uploaded_file($image['tmp_name'], $imagePath);
            // Update the image path in the database
            $conn->query("UPDATE Accessories SET image = '$imagePath' WHERE id = $id");
        }
        header("Location: shop.php?message=Item updated successfully.");
        exit();
    } else {
        $error = "Error updating item.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h2>Edit Accessory</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($accessory['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($accessory['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo number_format($accessory['price'], 2); ?>" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="form-text text-muted">Leave blank to keep the current image.</small>
            </div>
            <button type="submit" class="btn btn-primary">Update Item</button>
            <a href="shop.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

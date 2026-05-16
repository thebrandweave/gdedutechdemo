<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../admin_login.php');
	exit();
}

require_once '../../Configurations/config.php';

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
	$upd = $conn->prepare("UPDATE Blogs SET status = 'published' WHERE blog_id = ?");
	$upd->bind_param('i', $id);
	if ($upd->execute()) {
		$_SESSION['message'] = 'Blog published successfully.';
		$_SESSION['message_type'] = 'success';
	} else {
		$_SESSION['message'] = 'Failed to publish blog: ' . $conn->error;
		$_SESSION['message_type'] = 'danger';
	}
}

header('Location: ./');
exit();



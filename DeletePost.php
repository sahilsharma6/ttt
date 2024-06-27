<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header('Location: login.php');
    exit();
}

$post_id = $_GET['id'];

$stmt = mysqli_prepare($connection, "DELETE FROM posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $post_id);

if (mysqli_stmt_execute($stmt)) {
    header('Location: AllPost.php');
    exit();
} else {
    echo "Delete failed.";
}

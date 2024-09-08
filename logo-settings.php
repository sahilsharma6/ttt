<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed = ['png', 'jpg', 'jpeg', 'gif'];
        $filename = $_FILES['logo']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array($filetype, $allowed)) {
            $new_filename = 'logo.' . $filetype;
            $upload_dir = 'uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                // Optionally, you could update a record in your database with the new logo path here
                $success = "Logo updated successfully!";
            } else {
                $error = "Failed to upload the file.";
            }
        } else {
            $error = "Invalid file type. Only PNG, JPG, JPEG, and GIF are allowed.";
        }
    } else {
        $error = "Please select a file to upload.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Logo</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include_once 'sidebar.php'; ?>
    <div class="dash-content">
        <h1>Upload New Logo</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="Settings.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="logo" class="form-label">Choose Logo</label>
                <input type="file" name="logo" id="logo" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</body>

</html>
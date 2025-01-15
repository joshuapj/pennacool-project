<?php
require_once 'db_queries.php';

if (!isset($_GET['id'])) {
    die("User ID not provided.");
}

$id = intval($_GET['id']);
$error_message = null; // To hold any error messages

// Check if the user is a principal
$check_principal_sql = "SELECT role, school_id FROM users WHERE id = ?";
$stmt = $conn->prepare($check_principal_sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error_message = "User not found.";
} else {
    $user = $result->fetch_assoc();

    // Prevent deletion if the user is a principal assigned to a school
    if ($user['role'] === 'principal' && !empty($user['school_id'])) {
        $error_message = "Error: Cannot delete a principal who is assigned to a school.";
    } else {
        // Proceed with deletion if the user is not a principal or not assigned to a school
        if (deleteUser($conn, $id)) {
            $success_message = "User deleted successfully.";
        } else {
            $error_message = "Error deleting user: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Delete User</h1>

        <!-- Display alert messages -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <a href="user_management.php" class="btn btn-secondary">Back to User Management</a>
    </div>
</body>
</html>
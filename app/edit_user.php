<?php
require_once 'db_queries.php';

if (!isset($_GET['id'])) {
    die("User ID not provided.");
}

$id = intval($_GET['id']);

$user = getUserById($conn, $id);
if (!$user) {
    die("User not found.");
}

$schools = getSchools($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $_POST['role'];
    $school_id = ($_POST['school_id'] === '') ? NULL : intval($_POST['school_id']);  // Set to NULL if "No School" is selected

    if ($role === 'principal') {
        $check_principal_sql = "SELECT id FROM users WHERE role = 'principal' AND school_id = ? AND id != ?";
        $stmt = $conn->prepare($check_principal_sql);
        $stmt->bind_param('ii', $school_id, $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "The selected school already has a principal.";
        } else {
            $success = updateUser($conn, $id, $name, $email, $role, $school_id);

            if ($success) {
                header("Location: user_management.php?message=User updated successfully");
                exit;
            } else {
                $error_message = "Error updating user: " . $conn->error;
            }
        }
    } else {
        $success = updateUser($conn, $id, $name, $email, $role, $school_id);

        if ($success) {
            header("Location: user_management.php?message=User updated successfully");
            exit;
        } else {
            $error_message = "Error updating user: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="card-title text-center mb-4">Edit User</h1>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($error_message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="name" class="form-label fw-semibold">User Name</label>
                                <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                                <div class="invalid-feedback">Please enter a user name.</div>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <div class="mb-4">
                                <label for="role" class="form-label fw-semibold">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="teacher" <?= $user['role'] == 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                    <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Student</option>
                                    <option value="principal" <?= $user['role'] == 'principal' ? 'selected' : '' ?>>Principal</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <div class="invalid-feedback">Please select a role.</div>
                            </div>

                            <div class="mb-4">
                                <label for="school_id" class="form-label fw-semibold">School</label>
                                <select class="form-select" id="school_id" name="school_id">
                                    <option value="">-- No School --</option>
                                    <?php foreach ($schools as $school): ?>
                                        <option value="<?= $school['id'] ?>" <?= $school['id'] == $user['school_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($school['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Update User</button>
                                <a href="user_management.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable Bootstrap form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>

<?php
$conn->close();
?>
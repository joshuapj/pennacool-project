<?php
require_once 'db_queries.php';

$schools = getSchools($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    // Allow school_id to be NULL if "No School" is selected
    $school_id = ($_POST['school_id'] === '') ? NULL : intval($_POST['school_id']);  

    // Validation checks
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error_message = "All fields except School are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 7 || !preg_match('/\d/', $password)) {
        $error_message = "Password must be at least 7 characters long and contain at least one number.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (!in_array($role, ['teacher', 'student', 'principal', 'admin'])) {
        $error_message = "Invalid role selected.";
    } elseif ($role === 'principal' && $school_id !== NULL) {
        // Check if the selected school already has a principal, only if a school is selected
        $check_principal_sql = "SELECT id FROM users WHERE role = 'principal' AND school_id = ?";
        $stmt = $conn->prepare($check_principal_sql);
        $stmt->bind_param('i', $school_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "The selected school already has a principal.";
        }
    }

    // If no errors, proceed with user creation
    if (!isset($error_message)) {
        // Check if email is unique
        $email_check_sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($email_check_sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Email is already taken.";
        } else {
            // Hash the password before saving it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the users table
            $insert_sql = "INSERT INTO users (name, email, password, role, school_id) 
                           VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param('ssssi', $name, $email, $hashed_password, $role, $school_id);

            if ($insert_stmt->execute()) {
                // Get the ID of the newly created user
                $user_id = $insert_stmt->insert_id;

                // If the new user is a principal and a school is assigned, update the school
                if ($role === 'principal' && $school_id !== NULL) {
                    if (updateSchoolPrincipal($conn, $school_id, $user_id)) {
                        // Successfully updated the school with the new principal
                        header("Location: user_management.php?message=User added and school principal updated successfully.");
                    } else {
                        // Error updating the school
                        $error_message = "Error updating the school with the new principal.";
                    }
                } else {
                    header("Location: user_management.php?message=User added successfully.");
                }
                exit;
            } else {
                $error_message = "Error adding user: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="card-title text-center mb-4">Create New User</h1>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($error_message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="name" class="form-label fw-semibold">Name</label>
                                <input type="text" class="form-control form-control-lg" id="name" name="name" required>
                                <div class="invalid-feedback">Please enter a name.</div>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="form-text text-muted">Password must be at least 7 characters long and contain at least one number.</small>
                                <div class="invalid-feedback">Please enter a password.</div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div class="invalid-feedback">Please confirm your password.</div>
                            </div>

                            <div class="mb-4">
                                <label for="role" class="form-label fw-semibold">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="teacher">Teacher</option>
                                    <option value="student">Student</option>
                                    <option value="principal">Principal</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <div class="invalid-feedback">Please select a role.</div>
                            </div>

                            <div class="mb-4">
                                <label for="school_id" class="form-label fw-semibold">School</label>
                                <select class="form-select" id="school_id" name="school_id">
                                    <option value="">-- No School --</option>
                                    <?php while ($row = $schools->fetch_assoc()): ?>
                                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="invalid-feedback">Please select a school.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Create User</button>
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
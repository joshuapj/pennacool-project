<?php
require_once 'db_queries.php';

if (!isset($_GET['id'])) {
    die("School ID not provided.");
}

$id = intval($_GET['id']);

$school = getSchoolById($conn, $id);

if (!$school) {
    die("School not found.");
}

$principals = getPrincipals($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $principal_id = isset($_POST['principal_id']) && $_POST['principal_id'] !== '' 
                    ? intval($_POST['principal_id']) 
                    : null; // Set to null if no principal is selected

    // Begin transaction to ensure data consistency
    $conn->begin_transaction();

    try {
        $updateSchoolSuccess = updateSchool($conn, $id, $name, $address, $phone);
        $updatePrincipalSuccess = updateSchoolPrincipal($conn, $id, $principal_id);

        if ($updateSchoolSuccess && $updatePrincipalSuccess) {
            $conn->commit();
            header("Location: school_management.php?message=School updated successfully");
            exit;
        } else {
            throw new Exception("Error updating school.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error updating school: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Edit School</h1>
        <form method="post">
            <div class="mb-3">
                <label for="name" class="form-label">School Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($school['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($school['address']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($school['phone']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="principal_id" class="form-label">Principal</label>
                <select class="form-control" id="principal_id" name="principal_id">
                    <option value="">-- No Principal Selected --</option>
                    <?php foreach ($principals as $principal): ?>
                        <option value="<?= $principal['id'] ?>" 
                            <?= isset($school['principal_id']) && $principal['id'] == $school['principal_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($principal['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="school_management.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
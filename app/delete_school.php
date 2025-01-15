<?php
require_once 'db_queries.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $school_id = $_GET['id'];

    if (deleteSchool($conn, $school_id)) {
        header("Location: school_management.php?message=School deleted successfully");
        exit();
    } else {
        header("Location: school_management.php?error=Failed to delete school");
        exit();
    }
} else {
    header("Location: school_management.php?error=Invalid school ID");
    exit();
}
?>
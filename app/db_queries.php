<?php
$servername = "db";
$username = "root";
$password = "rootpassword";
$database = "school_management";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getSchools($conn) {
    $sql = "SELECT id, name, address, phone, principal_id FROM schools";
    return $conn->query($sql);
}

function getAllUsers($conn) {
    $sql = "SELECT users.id, users.name, users.email, users.role, schools.name AS school_name
            FROM users
            LEFT JOIN schools ON users.school_id = schools.id";
    
    return $conn->query($sql);
}

function getUserRolesSummary($conn) {
    $sql = "SELECT role, COUNT(*) AS count FROM users GROUP BY role";
    return $conn->query($sql); // Make sure to return the result
}

function getSchoolById($conn, $id) {
    $sql = "SELECT name, address, phone, principal_id FROM schools WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function updateSchool($conn, $id, $name, $address, $phone) {
    $sql = "UPDATE schools SET name = ?, address = ?, phone = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $address, $phone, $id);
    return $stmt->execute();
}

function getPrincipals($conn) {
    $sql = "SELECT id, name FROM users WHERE role = 'principal'";
    $result = $conn->query($sql);
    $principals = [];
    while ($row = $result->fetch_assoc()) {
        $principals[] = $row;
    }
    return $principals;
}

function updateSchoolPrincipal($conn, $id, $principal_id) {
    $sql = "UPDATE schools SET principal_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $principal_id, $id);
    return $stmt->execute();
}

function addSchool($conn, $name, $address, $phone, $principal_id) {
    $stmt = $conn->prepare("INSERT INTO schools (name, address, phone, principal_id) VALUES (?, ?, ?, ?)");
    
    // for dealing with situation when making a new school.
    if (empty($principal_id)) {
        $principal_id = null;
    }
    
    $stmt->bind_param("sssi", $name, $address, $phone, $principal_id);
    return $stmt->execute();
}

function deleteSchool($conn, $school_id) {
    $stmt = $conn->prepare("DELETE FROM schools WHERE id = ?");
    $stmt->bind_param("i", $school_id);
    return $stmt->execute();
}

function getUserById($conn, $id) {
    $sql = "SELECT users.id, users.name, users.email, users.role, users.school_id, schools.name AS school_name 
            FROM users 
            LEFT JOIN schools ON users.school_id = schools.id
            WHERE users.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id); 
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
    
    $stmt->close();
}

function updateUser($conn, $user_id, $name, $email, $role, $school_id) {
    $sql = "UPDATE users SET name = ?, email = ?, role = ?, school_id = ? WHERE id = ?";

    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param('sssii', $name, $email, $role, $school_id, $user_id);
    $result = $stmt->execute();

    if ($result) {
        return true;
    } else {
        return false;
    }
}

function deleteUser($conn, $id) {
    $sql = "DELETE FROM users WHERE id = $id";
    return $conn->query($sql);
}

function getUserByEmail($conn, $email) {
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

?>
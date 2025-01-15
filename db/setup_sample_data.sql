-- Create the database
CREATE DATABASE school_management;
USE school_management;

CREATE TABLE schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    principal_id INT DEFAULT NULL
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student', 'principal', 'admin') NOT NULL,
    school_id INT DEFAULT NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL
);

ALTER TABLE schools
ADD CONSTRAINT fk_principal_id
FOREIGN KEY (principal_id) REFERENCES users(id) ON DELETE SET NULL;

-- Insert sample schools
INSERT INTO schools (name, address, phone, principal_id)
VALUES
    ('Bright Future Academy', '123 Main St, Port of Spain', '868-123-4567', NULL),
    ('Harmony High School', '456 West Ave, San Fernando', '868-987-6543', NULL),
    ('Success Primary', '789 East Rd, Arima', '868-555-1212', NULL);

-- Insert sample users
INSERT INTO users (name, email, password, role, school_id)
VALUES
    -- Principals
    ('John Doe', 'jdoe@brightfuture.edu.tt', SHA2('password123', 256), 'principal', 1),
    ('Jane Smith', 'jsmith@harmony.edu.tt', SHA2('password123', 256), 'principal', 2),
    ('Michael Brown', 'mbrown@success.edu.tt', SHA2('password123', 256), 'principal', 3),
    
    -- Admin
    ('Admin User', 'admin@schoolmanagement.com', SHA2('adminpassword', 256), 'admin', NULL),

    -- Teachers
    ('Emma Davis', 'edavis@brightfuture.edu.tt', SHA2('teacherpass', 256), 'teacher', 1),
    ('James Wilson', 'jwilson@harmony.edu.tt', SHA2('teacherpass', 256), 'teacher', 2),
    ('Liam Miller', 'lmiller@success.edu.tt', SHA2('teacherpass', 256), 'teacher', 3),
    ('Sophia Lee', 'slee@brightfuture.edu.tt', SHA2('teacherpass', 256), 'teacher', 1),
    
    -- Students
    ('Ava Taylor', 'ataylor@brightfuture.edu.tt', SHA2('studentpass', 256), 'student', 1),
    ('Noah Anderson', 'nanderson@brightfuture.edu.tt', SHA2('studentpass', 256), 'student', 1),
    ('Isabella Moore', 'imoore@brightfuture.edu.tt', SHA2('studentpass', 256), 'student', 1),
    ('Oliver Jackson', 'ojackson@harmony.edu.tt', SHA2('studentpass', 256), 'student', 2),
    ('Mia White', 'mwhite@harmony.edu.tt', SHA2('studentpass', 256), 'student', 2),
    ('Ethan Harris', 'eharris@harmony.edu.tt', SHA2('studentpass', 256), 'student', 2),
    ('Amelia Martin', 'amartin@success.edu.tt', SHA2('studentpass', 256), 'student', 3),
    ('Lucas Thompson', 'lthompson@success.edu.tt', SHA2('studentpass', 256), 'student', 3),
    ('Charlotte Garcia', 'cgarcia@success.edu.tt', SHA2('studentpass', 256), 'student', 3),
    ('Harper Martinez', 'hmartinez@success.edu.tt', SHA2('studentpass', 256), 'student', 3),
    ('Benjamin Young', 'byoung@success.edu.tt', SHA2('studentpass', 256), 'student', 3);

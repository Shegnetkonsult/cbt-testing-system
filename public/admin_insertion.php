<?php
/**
 * =========================================================
 * SAMPLE ADMIN INSERTION
 * Project: CBT Testing System
 * Author: Adigun Joseph
 * Use this code to insert your sample admin in case of the one in SQL since it uses password hash
 * =========================================================
 */
// Database connection settings
$host = 'localhost';
$dbname = 'cbt_system';
$username = 'root';
$password = '';

// Create a PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Data to be inserted
$fullName = 'System Administrator';
$email = 'admin@cbt.local';
$password = 'admin123';
$role = 'admin';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert data into the users table
$sql = "INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, :role)";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':full_name', $fullName);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $hashedPassword);
$stmt->bindParam(':role', $role);

try {
    $stmt->execute();
    echo "User created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

Note: Make sure to replace the database connection settings with your own.

Alternatively, you can use the following code to insert data using MySQLi:

<?php

// Database connection settings
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_database_username';
$password = 'your_database_password';

// Create a MySQLi instance
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Data to be inserted
$fullName = 'System Administrator';
$email = 'admin@cbt.local';
$password = 'admin123';
$role = 'admin';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert data into the users table
$sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $fullName, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "User created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>

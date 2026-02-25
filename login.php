<?php
// Start session to store user data across pages
session_start();

$email = $_POST['email'];
$password = $_POST['password'];

// First, connect without database to create it if needed
$conn = new mysqli("localhost", "root", "");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS registration_db");
$conn->select_db("registration_db");

// Create registration table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS registration (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(50) NOT NULL,
    mname VARCHAR(50),
    lname VARCHAR(50) NOT NULL,
    address TEXT,
    faculty VARCHAR(100),
    gender VARCHAR(10),
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($table_sql);

// Add username column if it doesn't exist (for backward compatibility)
$conn->query("ALTER TABLE registration ADD COLUMN IF NOT EXISTS username VARCHAR(100) AFTER id");

// Update username from fname and lname if empty
$conn->query("UPDATE registration SET username = CONCAT(fname, ' ', lname) WHERE username IS NULL OR username = ''");

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT username, password FROM registration WHERE email = ?");
if ($stmt === false) {
    die("Database error. Please register first. <a href='registration.html'>Go to Register</a>");
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($username, $hashed_password);
    $stmt->fetch();
    
    if (password_verify($password, $hashed_password)) {
        // Store username in session for use on success page
        $_SESSION['username'] = $username;
        header("Location: success.php");
        exit();
    } else {
        echo "Invalid password. <a href='login.html'>Try again</a>";
    }
} else {
    echo "Email not found. <a href='registration.html'>Register here</a> or <a href='login.html'>Try again</a>";
}

$stmt->close();
$conn->close();
?>

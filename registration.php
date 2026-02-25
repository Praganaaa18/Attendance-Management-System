<?php
// Connect to MySQL
$servername = "localhost";
$username = "root"; // default user
$password = ""; // no password by default
$dbname = "registration_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Create table if not exists
$table_sql = "CREATE TABLE IF NOT EXISTS registration (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
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

if ($conn->query($table_sql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

// Get form data
$fname = $_POST['fname'];
$mname = $_POST['mname'];
$lname = $_POST['lname'];
$address = $_POST['address'];
$faculty = $_POST['faculty'];
$gender = $_POST['gender'];
$phone_number = $_POST['phone_number'];
$password = $_POST['password'];
$email = $_POST['email'];

// Create username from first and last name
$username = $fname . " " . $lname;

// Optional: hash password
$password = password_hash($password, PASSWORD_DEFAULT);

// Insert data
$sql = "INSERT INTO registration (username, fname, mname, lname, address, faculty, gender, email, phone_number, password)
        VALUES ('$username','$fname','$mname','$lname','$address','$faculty','$gender','$email','$phone_number','$password')";

if ($conn->query($sql) === TRUE) {
  // Registration successful - redirect to login page after 2 seconds
  echo "
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset='UTF-8'>
    <title>Registration Successful</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }
      .message-box {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        text-align: center;
      }
      .success-icon {
        font-size: 60px;
        color: #28a745;
        margin-bottom: 20px;
      }
      h2 {
        color: #333;
        margin-bottom: 10px;
      }
      p {
        color: #666;
        margin-bottom: 20px;
      }
      a {
        display: inline-block;
        padding: 12px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 25px;
        transition: transform 0.3s;
      }
      a:hover {
        transform: translateY(-2px);
      }
    </style>
    <meta http-equiv='refresh' content='2;url=login.html'>
  </head>
  <body>
    <div class='message-box'>
      <div class='success-icon'>&#10003;</div>
      <h2>Registration Successful!</h2>
      <p>Welcome, $username! Redirecting to login page...</p>
      <a href='login.html'>Go to Login Now</a>
    </div>
  </body>
  </html>
  ";
} else {
  echo "Error: " . $conn->error;
}

$conn->close();
?>

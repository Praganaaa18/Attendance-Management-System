<?php
// Attendance Tracking System - Success Page
// This page displays after successful login and handles attendance marking

// Start session to access logged-in user data
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Get the logged-in username from session
$username = $_SESSION['username'];

// Database connection for attendance - connect without database first
$conn = new mysqli("localhost", "root", "");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Create attendance database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS attendance_db");
$conn->select_db("attendance_db");

// Create attendance table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    attendance_date DATE NOT NULL,
    attendance_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_daily_attendance (username, attendance_date)
)";
$conn->query($table_sql);

// Initialize variables
$message = "";
$messageType = "";
$todayStatus = "Not marked";
$attendanceHistory = [];
$markedTime = null;

// Get today's date
$today = date("Y-m-d");
$currentTime = date("H:i:s");

// Check if attendance already marked for today
$checkStmt = $conn->prepare("SELECT attendance_time FROM attendance WHERE username = ? AND attendance_date = ?");
if ($checkStmt) {
    $checkStmt->bind_param("ss", $username, $today);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $todayStatus = "Present";
        $checkStmt->bind_result($markedTime);
        $checkStmt->fetch();
    }
    $checkStmt->close();
}

// Handle Mark Attendance button click
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_attendance'])) {
    if ($todayStatus === "Present") {
        // Attendance already marked for today
        $message = "Attendance already recorded for today.";
        $messageType = "warning";
    } else {
        // Insert new attendance record
        $insertStmt = $conn->prepare("INSERT INTO attendance (username, attendance_date, attendance_time) VALUES (?, ?, ?)");
        $insertStmt->bind_param("sss", $username, $today, $currentTime);
        
        if ($insertStmt->execute()) {
            $message = "Attendance Marked Successfully";
            $messageType = "success";
            $todayStatus = "Present";
            $markedTime = $currentTime;
        } else {
            $message = "Error marking attendance. Please try again.";
            $messageType = "error";
        }
        $insertStmt->close();
    }
}

// Fetch attendance history for this user
$historyStmt = $conn->prepare("SELECT attendance_date, attendance_time FROM attendance WHERE username = ? ORDER BY attendance_date DESC, attendance_time DESC LIMIT 10");
if ($historyStmt) {
    $historyStmt->bind_param("s", $username);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();

    while ($row = $historyResult->fetch_assoc()) {
        $attendanceHistory[] = $row;
    }
    $historyStmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Attendance Tracking</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Main card container */
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        /* Welcome heading */
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        /* Username display */
        .username {
            color: #667eea;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* Date and time display */
        .datetime {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .datetime .date {
            font-size: 18px;
            color: #555;
            margin-bottom: 5px;
        }

        .datetime .time {
            font-size: 32px;
            color: #333;
            font-weight: bold;
        }

        /* Today's status section */
        .status-section {
            margin-bottom: 25px;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .status-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .status-value {
            font-size: 20px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .status-present {
            color: #28a745;
        }

        .status-absent {
            color: #dc3545;
        }

        .check-icon {
            font-size: 24px;
        }

        .marked-time {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        /* Mark Attendance button */
        .btn-mark {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 50px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }

        .btn-mark:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-mark:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Message display */
        .message {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Attendance History Table */
        .history-section {
            margin-top: 30px;
            text-align: left;
        }

        .history-section h3 {
            color: #333;
            margin-bottom: 15px;
            text-align: center;
            font-size: 18px;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .history-table th,
        .history-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .history-table th {
            background: #f8f9fa;
            color: #555;
            font-weight: 600;
        }

        .history-table tr:hover {
            background: #f8f9fa;
        }

        .no-history {
            text-align: center;
            color: #666;
            padding: 20px;
            font-style: italic;
        }

        /* Logout link */
        .logout-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <!-- Welcome message with username -->
        <h1>Welcome Back!</h1>
        <div class="username"><?php echo htmlspecialchars($username); ?></div>

        <!-- Current date and time display -->
        <div class="datetime">
            <div class="date" id="current-date"></div>
            <div class="time" id="current-time"></div>
        </div>

        <!-- Display success/warning messages -->
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Today's attendance status -->
        <div class="status-section">
            <div class="status-label">Today's Attendance Status</div>
            <div class="status-value <?php echo $todayStatus === 'Present' ? 'status-present' : 'status-absent'; ?>">
                <?php if ($todayStatus === 'Present'): ?>
                    <span class="check-icon">&#10003;</span>
                    <span>Present</span>
                <?php else: ?>
                    <span class="check-icon">&#10007;</span>
                    <span>Not marked</span>
                <?php endif; ?>
            </div>
            <?php if ($todayStatus === 'Present' && isset($markedTime)): ?>
                <div class="marked-time">Marked at: <?php echo date("h:i A", strtotime($markedTime)); ?></div>
            <?php endif; ?>
        </div>

        <!-- Mark Attendance Form -->
        <form method="POST" action="">
            <button type="submit" name="mark_attendance" class="btn-mark" <?php echo $todayStatus === 'Present' ? 'disabled' : ''; ?>>
                <?php echo $todayStatus === 'Present' ? 'Attendance Marked' : 'Mark Attendance'; ?>
            </button>
        </form>

        <!-- Attendance History Table -->
        <div class="history-section">
            <h3>Attendance History (Last 10 Days)</h3>
            <?php if (count($attendanceHistory) > 0): ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceHistory as $record): ?>
                            <tr>
                                <td><?php echo date("M d, Y", strtotime($record['attendance_date'])); ?></td>
                                <td><?php echo date("h:i A", strtotime($record['attendance_time'])); ?></td>
                                <td style="color: #28a745;">&#10003; Present</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-history">No attendance records found</div>
            <?php endif; ?>
        </div>

        <!-- Logout link -->
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <!-- JavaScript for live date/time display -->
    <script>
        // Function to update date and time display
        function updateDateTime() {
            const now = new Date();
            
            // Format date: Wednesday, February 25, 2026
            const dateOptions = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            
            // Format time: 10:30:45 AM
            const timeOptions = { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: true 
            };
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Update immediately and then every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>
</body>
</html>

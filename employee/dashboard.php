<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
            width: 100%;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h4>Employee Panel</h4>
        <a href="time_out.php">⏰ Time Out</a>
        <a href="history.php">📅 Attendance History</a>
        <a href="apply_leave.php">Apply Leave</a>
        <a href="leave_history.php">Leave History</a>
        <a href="../auth/logout.php" class="text-danger">🚪 Logout</a>
    </div>


</body>
</html>

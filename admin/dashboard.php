<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Set cutoff time for Late detection
$cutoff_time = "08:00:00";

// Update status based on leave and time_in
$conn->query("
    UPDATE attendance a
    JOIN employees e ON a.emp_id = e.emp_id
    JOIN users u ON e.emp_id = u.emp_id
    LEFT JOIN leave_requests l ON a.emp_id = l.emp_id 
        AND l.status = 'Approved' 
        AND CURDATE() BETWEEN l.start_date AND l.end_date
    SET a.status = 
        CASE 
            WHEN l.id IS NOT NULL THEN 'On Leave'
            WHEN a.time_in IS NULL THEN 'Absent'
            WHEN TIME(a.time_in) > '$cutoff_time' THEN 'Late'
            ELSE 'Present'
        END
    WHERE a.date = CURDATE()
");

// Get total employees (including admin)
$totalEmployees = $conn->query("
    SELECT COUNT(*) AS total FROM employees e 
    JOIN users u ON e.emp_id = u.emp_id
")->fetch_assoc()['total'];

// Get counts: Present, Late, Absent, On Leave
$presentToday = $conn->query("
    SELECT COUNT(*) AS present FROM attendance a
    JOIN employees e ON a.emp_id = e.emp_id
    JOIN users u ON e.emp_id = u.emp_id
    WHERE a.date = CURDATE() AND a.status = 'Present'
")->fetch_assoc()['present'];

$lateToday = $conn->query("
    SELECT COUNT(*) AS late FROM attendance a
    JOIN employees e ON a.emp_id = e.emp_id
    JOIN users u ON e.emp_id = u.emp_id
    WHERE a.date = CURDATE() AND a.status = 'Late'
")->fetch_assoc()['late'];

$onLeaveToday = $conn->query("
    SELECT COUNT(*) AS on_leave FROM attendance a
    JOIN employees e ON a.emp_id = e.emp_id
    JOIN users u ON e.emp_id = u.emp_id
    WHERE a.date = CURDATE() AND a.status = 'On Leave'
")->fetch_assoc()['on_leave'];

// Compute Absent Employees
$absentToday = max(0, $totalEmployees - ($presentToday + $lateToday + $onLeaveToday));

// Get recent attendance
$recentAttendance = $conn->query("
    SELECT e.full_name, u.role, a.time_in, a.time_out, a.status 
    FROM attendance a 
    JOIN employees e ON a.emp_id = e.emp_id 
    JOIN users u ON e.emp_id = u.emp_id
    WHERE a.date = CURDATE()
    ORDER BY a.time_in ASC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/admincss/dashboard.css">
</head>
<body>

    <div class="sidebar">
        <h4>Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage_employees.php"><i class="fas fa-users"></i> Manage Employees</a>
        <a href="view_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
        <a href="generate_payroll.php"><i class="fas fa-money-bill"></i> Payroll</a>
        <a href="admin_leave_requests.php"><i class="fas fa-clipboard-list"></i> Leave Request</a>
        <a href="../auth/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h2>Daily Attendance Record Dashboard</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="card p-3">
                    <h5>Total Employees</h5>
                    <h3><?= $totalEmployees ?></h3>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card p-3 bg-success text-white">
                    <h5>Present</h5>
                    <h3><?= $presentToday ?></h3>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card p-3 bg-warning text-dark">
                    <h5>Late</h5>
                    <h3><?= $lateToday ?></h3>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card p-3 bg-info text-white">
                    <h5>On Leave</h5>
                    <h3><?= $onLeaveToday ?></h3>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card p-3 bg-danger text-white">
                    <h5>Absent</h5>
                    <h3><?= $absentToday ?></h3>
                </div>
            </div>
        </div>

        <canvas id="attendanceChart" width="400" height="150"></canvas>

        <h3 class="mt-4">Recent Attendance</h3>
        <table class="table table-bordered">
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $recentAttendance->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td>
                        <?php if ($row['role'] == 'admin'): ?>
                            <span class="badge bg-primary">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Employee</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['time_in'] ?: 'N/A' ?></td>
                    <td><?= $row['time_out'] ?: 'N/A' ?></td>
                    <td><?= $row['status'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
        var ctx = document.getElementById('attendanceChart').getContext('2d');
        var attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Present', 'Late', 'On Leave', 'Absent'],
                datasets: [{
                    label: `Today's Attendance`,
                    data: [<?= $presentToday ?>, <?= $lateToday ?>, <?= $onLeaveToday ?>, <?= $absentToday ?>],
                    backgroundColor: ['green', 'yellow', 'skyblue', 'red']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

</body>
</html>

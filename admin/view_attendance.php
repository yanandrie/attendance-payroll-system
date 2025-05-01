<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Filter attendance records based on date range
$where = "";
$from_date = $to_date = "";
if (isset($_GET['from_date']) && isset($_GET['to_date'])) {
    $from_date = $_GET['from_date'];
    $to_date = $_GET['to_date'];
    $where = "WHERE a.date BETWEEN '$from_date' AND '$to_date'";
}

// Get attendance records with computed work hours & overtime
$sql = "SELECT 
            a.*, 
            e.full_name, 
            u.role,
            IFNULL(a.work_hours, 0) AS work_hours, 
            IFNULL(a.overtime_hours, 0) AS overtime_hours,
            lr.leave_type,
            lr.status AS leave_status
        FROM attendance a 
        JOIN employees e ON a.emp_id = e.emp_id 
        JOIN users u ON e.emp_id = u.emp_id 
        LEFT JOIN leave_requests lr 
            ON a.emp_id = lr.emp_id 
            AND a.date BETWEEN lr.start_date AND lr.end_date 
            AND lr.status = 'approved'
        $where 
        ORDER BY a.date DESC";

$result = $conn->query($sql);

// Helper function for status badge
function getStatusBadge($row) {
    if ($row['leave_type'] && $row['leave_status'] === 'approved') {
        return "<span class='badge bg-info'>On Leave</span>";
    } else {
        $status = $row['status'];
        $badgeColor = match($status) {
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            default => 'secondary',
        };
        return "<span class='badge bg-{$badgeColor}'>" . ucfirst($status) . "</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { display: flex; height: 100vh; background-color: #f8f9fa; }
        .sidebar { width: 250px; background: #343a40; color: white; padding: 20px; height: 100vh; position: fixed; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .sidebar a:hover { background: #495057; }
        .main-content { margin-left: 270px; padding: 20px; width: 100%; }
        .table th { background-color: #343a40; color: white; text-align: center; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage_employees.php"><i class="fas fa-users"></i> Manage Employees</a>
        <a href="view_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
        <a href="generate_payroll.php"><i class="fas fa-money-bill"></i> Payroll</a>
        <a href="../auth/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="text-center">Attendance Report</h2>

        <!-- Filter Form -->
        <form method="GET" class="d-flex justify-content-center mb-3">
            <input type="date" name="from_date" value="<?= $from_date ?>" required class="form-control w-25 me-2">
            <input type="date" name="to_date" value="<?= $to_date ?>" required class="form-control w-25 me-2">
            <button type="submit" class="btn btn-primary">📅 Filter</button>
            <a href="view_attendance.php" class="btn btn-secondary ms-2">🔄 Reset</a>
        </form>

        <!-- Attendance Table -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Work Hours</th>
                    <th>Overtime Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="attendance-table">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= $row['time_in'] ?: 'N/A' ?></td>
                        <td><?= $row['time_out'] ?: 'N/A' ?></td>
                        <td><?= $row['work_hours'] ?> hrs</td>
                        <td><?= $row['overtime_hours'] ?> hrs</td>
                        <td><?= getStatusBadge($row) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Export Buttons -->
        <div class="d-flex justify-content-center mt-3">
            <a href="export_excel.php?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>" class="btn btn-success me-2">📊 Export to Excel</a>
            <a href="export_pdf.php?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>" class="btn btn-danger">📄 Export to PDF</a>
        </div>
    </div>

    <script>
    function fetchAttendance() {
        $.ajax({
            url: "fetch_attendance.php",
            method: "GET",
            dataType: "json",
            success: function (data) {
                let tableBody = "";
                data.forEach(row => {
                    let badgeColor = 'secondary';
                    let badgeText = row.status;

                    if (row.leave_type && row.leave_status === 'approved') {
                        badgeColor = 'info';
                        badgeText = 'On Leave';
                    } else {
                        switch (row.status) {
                            case 'present':
                                badgeColor = 'success';
                                break;
                            case 'late':
                                badgeColor = 'warning';
                                break;
                            case 'absent':
                                badgeColor = 'danger';
                                break;
                        }
                    }

                    tableBody += `
                        <tr>
                            <td>${row.full_name}</td>
                            <td>${row.date}</td>
                            <td>${row.time_in || 'N/A'}</td>
                            <td>${row.time_out || 'N/A'}</td>
                            <td>${row.work_hours} hrs</td>
                            <td>${row.overtime_hours} hrs</td>
                            <td><span class="badge bg-${badgeColor}">${badgeText.charAt(0).toUpperCase() + badgeText.slice(1)}</span></td>
                        </tr>
                    `;
                });
                $("#attendance-table").html(tableBody);
            }
        });
    }

    // Auto-refresh attendance table every 5 seconds
    setInterval(fetchAttendance, 5000);
    fetchAttendance();
    </script>

</body>
</html>

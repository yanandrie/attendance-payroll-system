<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../auth/login.php");
    exit();
}

$emp_id = $_SESSION['emp_id'];

// Fetch Payroll History
$payroll_sql = "SELECT * FROM payroll WHERE emp_id = {$_SESSION['user_id']}";
$payroll_result = $conn->query($payroll_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { display: flex; height: 100vh; background-color: #f8f9fa; }
        .sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
        }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .sidebar a:hover { background: #495057; }
        .main-content { margin-left: 270px; padding: 20px; width: 100%; }
        .card { margin-bottom: 20px; }
        .table th, .table td { text-align: center; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h4>👨‍💼 Employee Panel</h4>
        <a href="time_out.php">⏰ Time Out</a>
        <a href="history.php">📅 Attendance History</a>
        <a href="#" class="active">💰 Payroll History</a>
        <a href="../auth/logout.php" class="text-danger">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>🏠 Employee Dashboard</h2>

        <!-- Payroll History Section -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                💰 Payroll History
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Pay Date</th>
                            <th>Days Worked</th>
                            <th>Gross Salary</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($payroll_result->num_rows > 0): ?>
                            <?php while ($row = $payroll_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date("M d, Y", strtotime($row['pay_date'])) ?></td>
                                    <td><?= $row['days_worked'] ?></td>
                                    <td>₱<?= number_format($row['gross_salary'], 2) ?></td>
                                    <td>₱<?= number_format($row['deductions'], 2) ?></td>
                                    <td><strong>₱<?= number_format($row['net_salary'], 2) ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No payroll records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>

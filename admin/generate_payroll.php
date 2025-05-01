<?php
session_start();
include '../config/db_connect.php';
$salary_rates = include '../config/salary_rates.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Pagination setup
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Get total number of records
$count_sql = "SELECT COUNT(DISTINCT e.emp_id) AS total FROM employees e LEFT JOIN attendance a ON e.emp_id = a.emp_id WHERE a.status IN ('Present', 'Late')";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch payroll data with limit
$sql = "SELECT e.emp_id, e.full_name, e.position, 
               COUNT(DISTINCT a.date) AS days_worked, 
               SUM(a.work_hours) AS work_hours,
               SUM(a.overtime_hours) AS overtime_hours
        FROM employees e
        LEFT JOIN attendance a ON e.emp_id = a.emp_id
        WHERE a.status IN ('Present', 'Late')
        GROUP BY e.emp_id
        LIMIT $start, $limit";
$result = $conn->query($sql);

$payroll_data = [];
while ($row = $result->fetch_assoc()) {
    $emp_id = $row['emp_id'];
    $name = $row['full_name'];
    $position = $row['position'];
    $days_worked = $row['days_worked'] ?? 0;
    $work_hours = $row['work_hours'] ?? 0;
    $overtime_hours = $row['overtime_hours'] ?? 0;

    // Salary Computation
    $basic_salary = $salary_rates[$position] ?? 0;
    $daily_rate = $basic_salary / 22;
    $hourly_rate = $daily_rate / 8;

    // Overtime Computation
    $overtime_pay = $overtime_hours * ($hourly_rate * 1.25);

    // Gross Salary
    $gross_salary = ($work_hours * $hourly_rate) + $overtime_pay;

    // Government Deductions
    $gsis = $gross_salary * 0.09;
    $pagibig = $gross_salary * 0.02;
    $philhealth = ($gross_salary * 0.04) / 2;

    // Tax Computation
    $tax = ($gross_salary > 50000) ? $gross_salary * 0.12 : (($gross_salary > 30000) ? $gross_salary * 0.08 : 0);

    // Check for leave requests (unpaid leave)
    $leave_sql = "SELECT * FROM leave_requests WHERE emp_id = $emp_id AND status = 'approved'";
    $leave_result = $conn->query($leave_sql);
    $unpaid_leave_days = 0;

    while ($leave = $leave_result->fetch_assoc()) {
        // If leave type is unpaid, deduct from salary
        if ($leave['leave_type'] == 'Unpaid') {
            $unpaid_leave_days++;
        }
    }

    // Calculate deductions for unpaid leave
    $unpaid_leave_deduction = $unpaid_leave_days * $daily_rate;

    // Total Deductions
    $total_deductions = $gsis + $pagibig + $philhealth + $tax + $unpaid_leave_deduction;
    $net_salary = $gross_salary - $total_deductions;

    // Store Payroll Data
    $payroll_data[] = compact('emp_id', 'name', 'position', 'days_worked', 'work_hours', 'overtime_hours', 'basic_salary', 'gross_salary', 'overtime_pay', 'gsis', 'pagibig', 'philhealth', 'tax', 'unpaid_leave_deduction', 'total_deductions', 'net_salary');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/admincss/generate_payroll.css">
</head>
<body>
    <div class="sidebar">
        <h4>Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage_employees.php"><i class="fas fa-users"></i> Manage Employees</a>
        <a href="view_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
        <a href="generate_payroll.php"><i class="fas fa-money-bill"></i> Payroll</a>
        <a href="../auth/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="main-content">
        <h2>Payroll Report</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Days Worked</th>
                    <th>Total Work Hours</th>
                    <th>Overtime Hours</th>
                    <th>Basic Salary</th>
                    <th>Gross Salary</th>
                    <th>Overtime Pay</th>
                    <th>GSIS</th>
                    <th>Pag-IBIG</th>
                    <th>PhilHealth</th>
                    <th>Tax</th>
                    <th>Unpaid Leave Deduction</th>
                    <th>Total Deductions</th>
                    <th>Net Salary</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payroll_data as $pay): ?>
                    <tr>
                        <td><?= $pay['emp_id'] ?></td>
                        <td><?= $pay['name'] ?></td>
                        <td><?= $pay['position'] ?></td>
                        <td><?= $pay['days_worked'] ?></td>
                        <td><?= $pay['work_hours'] ?></td>
                        <td><?= $pay['overtime_hours'] ?></td>
                        <td>₱<?= number_format($pay['basic_salary'], 2) ?></td>
                        <td>₱<?= number_format($pay['gross_salary'], 2) ?></td>
                        <td>₱<?= number_format($pay['overtime_pay'], 2) ?></td>
                        <td>₱<?= number_format($pay['gsis'], 2) ?></td>
                        <td>₱<?= number_format($pay['pagibig'], 2) ?></td>
                        <td>₱<?= number_format($pay['philhealth'], 2) ?></td>
                        <td>₱<?= number_format($pay['tax'], 2) ?></td>
                        <td>₱<?= number_format($pay['unpaid_leave_deduction'], 2) ?></td>
                        <td>₱<?= number_format($pay['total_deductions'], 2) ?></td>
                        <td><strong>₱<?= number_format($pay['net_salary'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

        <a href="export_excel.php" class="btn btn-success">Export to Excel</a>
        <a href="export_pdf.php" class="btn btn-danger">Export to PDF</a>
    </div>
</body>
</html>

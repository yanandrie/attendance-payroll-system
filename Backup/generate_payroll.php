<?php
session_start();
include '../config/db_connect.php';
$salary_rates = include '../config/salary_rates.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$sql = "SELECT e.emp_id, e.full_name, e.position, 
               COUNT(DISTINCT a.date) AS days_worked, 
               SUM(a.work_hours) AS work_hours
        FROM employees e
        LEFT JOIN attendance a ON e.emp_id = a.emp_id
        WHERE a.status IN ('Present', 'Late')
        GROUP BY e.emp_id";
$result = $conn->query($sql);

$payroll_data = [];
while ($row = $result->fetch_assoc()) {
    $emp_id = $row['emp_id'];
    $name = $row['full_name'];
    $position = $row['position'];
    $days_worked = $row['days_worked'] ?? 0;
    $work_hours = $row['work_hours'] ?? 0;
    
    $basic_salary = $salary_rates[$position] ?? 0;
    $daily_rate = $basic_salary / 22;
    $hourly_rate = $daily_rate / 8;
    
    $overtime_hours = max(0, $work_hours - ($days_worked * 8));
    $overtime_pay = $overtime_hours * ($hourly_rate * 1.25);
    
    $gross_salary = ($work_hours * $hourly_rate) + $overtime_pay;
    $gsis = $gross_salary * 0.09;
    $pagibig = $gross_salary * 0.02;
    $philhealth = ($gross_salary * 0.04) / 2;
    
    $tax = ($gross_salary > 50000) ? $gross_salary * 0.12 : (($gross_salary > 30000) ? $gross_salary * 0.08 : 0);
    
    $total_deductions = $gsis + $pagibig + $philhealth + $tax;
    $net_salary = $gross_salary - $total_deductions;

    $payroll_data[] = compact('emp_id', 'name', 'position', 'days_worked', 'work_hours', 'basic_salary', 'gross_salary', 'overtime_pay', 'gsis', 'pagibig', 'philhealth', 'tax', 'total_deductions', 'net_salary');
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
    <style>
        body { display: flex; }
        .sidebar { width: 250px; background: #343a40; color: white; padding: 20px; height: 100vh; position: fixed; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .sidebar a:hover { background: #495057; }
        .main-content { margin-left: 270px; padding: 20px; width: 100%; }
    </style>
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
                    <th>Basic Salary</th>
                    <th>Gross Salary</th>
                    <th>Overtime Pay</th>
                    <th>GSIS</th>
                    <th>Pag-IBIG</th>
                    <th>PhilHealth</th>
                    <th>Tax</th>
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
                        <td>₱<?= number_format($pay['basic_salary'], 2) ?></td>
                        <td>₱<?= number_format($pay['gross_salary'], 2) ?></td>
                        <td>₱<?= number_format($pay['overtime_pay'], 2) ?></td>
                        <td>₱<?= number_format($pay['gsis'], 2) ?></td>
                        <td>₱<?= number_format($pay['pagibig'], 2) ?></td>
                        <td>₱<?= number_format($pay['philhealth'], 2) ?></td>
                        <td>₱<?= number_format($pay['tax'], 2) ?></td>
                        <td>₱<?= number_format($pay['total_deductions'], 2) ?></td>
                        <td><strong>₱<?= number_format($pay['net_salary'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="export_excel.php" class="btn btn-success">Export to Excel</a>
        <a href="export_pdf.php" class="btn btn-danger">Export to PDF</a>
    </div>
</body>
</html>

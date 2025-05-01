<?php
session_start();
include '../config/db_connect.php';
$salary_rates = include '../config/salary_rates.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$sql = "SELECT e.emp_id, e.full_name, e.position, 
               COUNT(CASE WHEN a.work_hours >= 4 THEN a.id END) AS days_worked, 
               SUM(CASE WHEN a.work_hours >= 4 THEN 
                        CASE 
                            WHEN a.time_in < '12:00:00' AND a.time_out > '12:00:00' THEN a.work_hours - 1 
                            ELSE a.work_hours 
                        END 
                    ELSE 0 END) AS total_hours
        FROM employees e
        LEFT JOIN attendance a ON e.emp_id = a.emp_id
        WHERE a.status = 'Present'
        GROUP BY e.emp_id";
$result = $conn->query($sql);

$payroll_data = [];
while ($row = $result->fetch_assoc()) {
    $emp_id = $row['emp_id'];
    $name = $row['full_name'];
    $position = $row['position'];
    $days_worked = $row['days_worked'] ?? 0;
    $total_hours = $row['total_hours'] ?? 0;
    
    // Salary Computation
    $basic_salary = $salary_rates[$position] ?? 0;
    $daily_rate = $basic_salary / 22; // 22 working days
    $hourly_rate = $daily_rate / 8; // 8 hours per day

    // Overtime Computation (Kung lumagpas sa 8 hours per day)
    $regular_hours = $days_worked * 8; // Standard working hours
    $overtime_hours = max(0, $total_hours - $regular_hours); // Any excess is OT
    $overtime_pay = $overtime_hours * ($hourly_rate * 1.25); // 25% increase for OT

    // Gross Salary Computation
    $gross_salary = ($daily_rate * $days_worked) + $overtime_pay;

    // Deductions
    $gsis = $gross_salary * 0.09;
    $pagibig = $gross_salary * 0.02;
    $philhealth = ($gross_salary * 0.04) / 2;
    
    // Tax Computation
    $tax = ($gross_salary > 50000) ? $gross_salary * 0.12 : 
           (($gross_salary > 30000) ? $gross_salary * 0.08 : 0);
    
    // Total Deductions & Net Salary
    $total_deductions = $gsis + $pagibig + $philhealth + $tax;
    $net_salary = $gross_salary - $total_deductions;

    $payroll_data[] = compact('emp_id', 'name', 'position', 'days_worked', 'total_hours', 'basic_salary', 'gross_salary', 'overtime_pay', 'gsis', 'pagibig', 'philhealth', 'tax', 'total_deductions', 'net_salary');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payroll Report</title>
</head>
<body>
    <h2>Payroll Report</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Position</th>
                <th>Days Worked</th>
                <th>Total Hours</th>
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
                    <td><?= $pay['total_hours'] ?></td>
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
</body>
</html>

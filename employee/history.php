<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../auth/login.php");
    exit();
}

$emp_id = $_SESSION['emp_id'];
$sql = "SELECT * FROM attendance WHERE emp_id = $emp_id ORDER BY time_in DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 800px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        .table th { background-color: #343a40; color: white; text-align: center; }
        .still-working { color: red; font-weight: bold; }
        .back-btn { display: block; width: fit-content; margin: 20px auto; }
    </style>
</head>
<body>

    <div class="container">
        <h2>📅 Attendance History</h2>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('F d, Y', strtotime($row['time_in'])) ?></td>
                        <td><?= date('h:i A', strtotime($row['time_in'])) ?></td>
                        <td>
                            <?php if ($row['time_out']): ?>
                                <?= date('h:i A', strtotime($row['time_out'])) ?>
                            <?php else: ?>
                                <span class="still-working">Still Working</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn btn-secondary back-btn">⬅ Back to Dashboard</a>
    </div>

</body>
</html>

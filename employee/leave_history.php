<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../auth/login.php");
    exit();
}

$emp_id = $_SESSION['emp_id'];

$query = $conn->prepare("SELECT * FROM leave_requests WHERE emp_id = ? ORDER BY requested_at DESC");
$query->bind_param("i", $emp_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leave History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 1000px; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; }
        .status-Pending { color: orange; font-weight: bold; }
        .status-Approved { color: green; font-weight: bold; }
        .status-Denied { color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>📋 My Leave History</h2>
    <a href="apply_leave.php" class="btn btn-primary mb-3">➕ Apply New Leave</a>
    <a href="dashboard.php" class="btn btn-secondary mb-3 ms-2">⬅ Back to Dashboard</a>

    <?php if ($result->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Requested At</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= htmlspecialchars($row['leave_type']) ?></td>
                    <td><?= htmlspecialchars(date('M d, Y', strtotime($row['start_date']))) ?></td>
                    <td><?= htmlspecialchars(date('M d, Y', strtotime($row['end_date']))) ?></td>
                    <td><?= htmlspecialchars($row['reason']) ?></td>
                    <td class="status-<?= $row['status'] ?>"><?= $row['status'] ?></td>
                    <td><?= date('M d, Y h:i A', strtotime($row['requested_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-info">No leave requests found. Apply for your first leave!</div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
session_start();
include '../config/db_connect.php';

// Admin Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch Leave Requests
$query = "SELECT lr.id, e.full_name, lr.leave_type, lr.start_date, lr.end_date, lr.status, lr.reason 
          FROM leave_requests lr
          JOIN employees e ON lr.emp_id = e.emp_id
          ORDER BY lr.requested_at DESC";
$result = $conn->query($query);

// Approve or Deny Action
if (isset($_POST['action']) && isset($_POST['leave_id'])) {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action'];

    // Set the status based on the action
    if ($action == 'approve') {
        $status = 'approved';  // Using lowercase to match ENUM value
    } else if ($action == 'deny') {
        $status = 'rejected';  // Using lowercase to match ENUM value
    } else {
        echo "Invalid action.";
        exit();
    }

    // Prepare the UPDATE query to change the status
    $update_query = "UPDATE leave_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);

    // Check if the statement preparation was successful
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("si", $status, $leave_id);

    // Execute the query and check if it's successful
    if ($stmt->execute()) {
        header("Location: admin_leave_requests.php");
        exit();
    } else {
        echo "Error updating leave request: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Leave Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Leave Requests</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['leave_type']) ?></td>
                        <td><?= htmlspecialchars($row['start_date']) ?></td>
                        <td><?= htmlspecialchars($row['end_date']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="leave_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                    <button type="submit" name="action" value="deny" class="btn btn-danger btn-sm">Deny</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

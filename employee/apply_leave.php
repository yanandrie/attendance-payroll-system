<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../auth/login.php");
    exit();
}

$emp_id = $_SESSION['emp_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = trim($_POST['leave_type']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = trim($_POST['reason']);

    // Basic validation
    if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
        $message = ['type' => 'error', 'text' => 'All fields are required.'];
    } elseif ($start_date > $end_date) {
        $message = ['type' => 'error', 'text' => 'Start date cannot be after End date.'];
    } else {
        // Check for overlapping leave requests
        $check_overlap = $conn->prepare("SELECT id FROM leave_requests WHERE emp_id = ? AND status IN ('Pending', 'Approved') 
            AND (start_date <= ? AND end_date >= ?)");
        $check_overlap->bind_param("iss", $emp_id, $end_date, $start_date);
        $check_overlap->execute();
        $overlap_result = $check_overlap->get_result();

        if ($overlap_result->num_rows > 0) {
            $message = ['type' => 'error', 'text' => 'You already have an existing leave during the selected dates.'];
        } else {
            // Insert leave request
            $insert = $conn->prepare("INSERT INTO leave_requests (emp_id, leave_type, start_date, end_date, status, reason, requested_at) 
                VALUES (?, ?, ?, ?, 'Pending', ?, NOW())");
            $insert->bind_param("issss", $emp_id, $leave_type, $start_date, $end_date, $reason);

            if ($insert->execute()) {
                $message = ['type' => 'success', 'text' => 'Leave request submitted successfully!'];
            } else {
                $message = ['type' => 'error', 'text' => 'Error submitting leave request.'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Leave</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 700px; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2>📝 Apply for Leave</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="leave_type" class="form-label">Leave Type</label>
            <select class="form-select" id="leave_type" name="leave_type" required>
                <option value="">Select Leave Type</option>
                <option value="Vacation Leave">Vacation Leave</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Emergency Leave">Emergency Leave</option>
                <option value="Maternity Leave">Maternity Leave</option>
                <option value="Paternity Leave">Paternity Leave</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>

        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>

        <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <textarea class="form-control" id="reason" name="reason" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit Leave Request</button>
        <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">⬅ Back to Dashboard</a>
    </form>
</div>

<?php if (!empty($message)): ?>
<script>
    Swal.fire({
        icon: '<?= $message['type'] ?>',
        title: '<?= ucfirst($message['type']) ?>',
        text: '<?= $message['text'] ?>',
    }).then(() => {
        if ('<?= $message['type'] ?>' === 'success') {
            window.location.href = 'leave_history.php'; // auto-redirect to history after success
        }
    });
</script>
<?php endif; ?>

</body>
</html>

<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../auth/login.php");
    exit();
}

$emp_id = $_SESSION['emp_id'];

// Fetch today's attendance record
$check_attendance = $conn->prepare("SELECT id, time_in, time_out FROM attendance WHERE emp_id = ? AND DATE(time_in) = CURDATE()");
$check_attendance->bind_param("i", $emp_id);
$check_attendance->execute();
$att_result = $check_attendance->get_result();

$attendance = $att_result->fetch_assoc();
$hasTimedIn = $att_result->num_rows > 0;
$hasTimedOut = $hasTimedIn && $attendance['time_out'] !== NULL;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Out</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 600px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); text-align: center; }
        h2 { margin-bottom: 20px; }
        .btn { width: 100%; }
        .alert { margin-top: 10px; }
    </style>
</head>
<body>

    <div class="container">
        <h2>⏳ Time Out</h2>

        <?php if (!$hasTimedIn): ?>
            <div class="alert alert-warning">⚠ You have not timed in today.</div>
        <?php elseif ($hasTimedOut): ?>
            <div class="alert alert-success">✅ You have already timed out today.</div>
        <?php else: ?>
            <button class="btn btn-danger" onclick="confirmTimeout(<?= $attendance['id'] ?>)">🔴 Time Out</button>
        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
    </div>

    <script>
        function confirmTimeout(attendanceId) {
            Swal.fire({
                title: "Are you sure?",
                text: "You are about to time out. This action cannot be undone.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Time Out!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "time_out.php?timeout=" + attendanceId;
                }
            });
        }
    </script>

</body>
</html>

<?php
if (isset($_GET['timeout'])) {
    $att_id = intval($_GET['timeout']);

    // Fetch existing time_in
    $fetch_timein = $conn->prepare("SELECT time_in, time_out FROM attendance WHERE id = ? AND emp_id = ?");
    $fetch_timein->bind_param("ii", $att_id, $emp_id);
    $fetch_timein->execute();
    $result = $fetch_timein->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        if ($row['time_out'] !== null) {
            echo "<script>
                Swal.fire('Already Timed Out!', 'You have already timed out today.', 'info')
                    .then(() => { window.location.href = 'time_out.php'; });
            </script>";
            exit;
        }

        $time_in = new DateTime($row['time_in']);
        $time_out = new DateTime(); // Now

        // Set standard work schedule
        $start_work = new DateTime(date('Y-m-d') . " 08:00:00");
        $lunch_start = new DateTime(date('Y-m-d') . " 12:00:00");
        $lunch_end = new DateTime(date('Y-m-d') . " 13:00:00");
        $end_work = new DateTime(date('Y-m-d') . " 17:00:00");
        $late_threshold = new DateTime(date('Y-m-d') . " 09:00:00");

        $work_hours = 0;
        $overtime_hours = 0;

        // Correct early time-ins
        if ($time_in < $start_work) {
            $time_in = $start_work;
        }

        // Compute overtime if applicable
        if ($time_out > $end_work) {
            $overtime_hours = ($time_out->getTimestamp() - $end_work->getTimestamp()) / 3600;
            $time_out = $end_work;
        }

        // Compute regular work hours
        if ($time_out > $time_in) {
            $work_hours = ($time_out->getTimestamp() - $time_in->getTimestamp()) / 3600;

            // Deduct lunch if needed
            if ($time_in < $lunch_start && $time_out > $lunch_end) {
                $work_hours -= 1;
            }
        }

        if ($work_hours < 0) $work_hours = 0;
        if ($overtime_hours < 0) $overtime_hours = 0;

        // Status handling
        $status = ($row['time_in'] > $late_threshold->format('Y-m-d H:i:s')) ? 'Late' : 'Present';

        // Final formatting
        $formatted_work = number_format($work_hours, 2);
        $formatted_ot = number_format($overtime_hours, 2);

        // Update attendance record
        $update_sql = $conn->prepare("UPDATE attendance 
            SET time_out = NOW(), 
                work_hours = ?, 
                overtime_hours = ?, 
                status = ? 
            WHERE id = ?");
        $update_sql->bind_param("ddsi", $work_hours, $overtime_hours, $status, $att_id);

        if ($update_sql->execute()) {
            echo "<script>
                Swal.fire({
                    title: 'Time Out Successful!',
                    text: 'Work Hours: $formatted_work, Overtime Hours: $formatted_ot',
                    icon: 'success'
                }).then(() => {
                    window.location.href = 'time_out.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire('Error!', 'Error updating work hours: " . $conn->error . "', 'error');
            </script>";
        }
    }
}
?>

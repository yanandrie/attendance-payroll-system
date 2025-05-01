<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Get employee data
if (isset($_GET['id'])) {
    $emp_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM employees WHERE emp_id = ?");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
}

// Update Employee Data
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['full_name']);
    $position = htmlspecialchars($_POST['position']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $salary = $_POST['salary'];

    // Salary validation (must be positive and not zero)
    if (!is_numeric($salary) || $salary <= 0) {
        $error = "Salary must be a positive number and greater than zero!";
    } else {
        $stmt = $conn->prepare("UPDATE employees SET full_name=?, position=?, phone=?, address=?, salary=? WHERE emp_id=?");
        $stmt->bind_param("ssssdi", $name, $position, $phone, $address, $salary, $emp_id);
        
        if ($stmt->execute()) {
            $success = "Employee updated successfully!";
            echo "<script>alert('$success'); window.location='manage_employees.php';</script>";
        } else {
            $error = "Error updating employee: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">
    <h2>Edit Employee</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($employee['full_name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Position</label>
                <select name="position" class="form-control" required>
                    <option value="">Select Position</option>
                    <?php 
                    $positions = [
                        "Administrator","Police Officer", "Firefighter", "Public School Teacher", "Government Accountant",
                        "Civil Engineer", "Health Officer", "IT Specialist", "Administrative Officer",
                        "Social Worker", "Municipal Treasurer"
                    ];
                    foreach ($positions as $pos): ?>
                        <option value="<?= $pos ?>" <?= ($employee['position'] == $pos) ? 'selected' : '' ?>><?= $pos ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($employee['phone']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" required><?= htmlspecialchars($employee['address']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Salary</label>
                <input type="number" name="salary" class="form-control" value="<?= htmlspecialchars($employee['salary']) ?>" step="0.01" min="0.01" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Employee</button>
            <a href="manage_employees.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

</body>
</html>

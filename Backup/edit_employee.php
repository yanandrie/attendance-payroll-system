<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// get id in url
if (isset($_GET['id'])) {
    $emp_id = $_GET['id'];
    $sql = "SELECT * FROM employees WHERE emp_id = '$emp_id'";
    $result = $conn->query($sql);
    $employee = $result->fetch_assoc();
}

// Update Employee Data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $position = $_POST['position'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $update_sql = "UPDATE employees SET full_name='$name', position='$position', phone='$phone', address='$address' WHERE emp_id='$emp_id'";
    
    if ($conn->query($update_sql)) {
        echo "Employee updated successfully!";
        header("Location: employee_list.php");
        exit();
    } else {
        echo "Error updating employee: " . $conn->error;
    }
}
?>

<h2>Edit Employee</h2>
<form method="post" action="">
    <label>Name:</label>
    <input type="text" name="full_name" value="<?= $employee['full_name'] ?>" required>

    <label>Position:</label>
    <select name="position" required>
        <option value="Administrator" <?= ($employee['position'] == 'Administrator') ? 'selected' : '' ?>>Administrator</option>
        <option value="Police Officer" <?= ($employee['position'] == 'Police Officer') ? 'selected' : '' ?>>Police Officer</option>
        <option value="Firefighter" <?= ($employee['position'] == 'Firefighter') ? 'selected' : '' ?>>Firefighter</option>
        <option value="Public School Teacher" <?= ($employee['position'] == 'Public School Teacher') ? 'selected' : '' ?>>Public School Teacher</option>
        <option value="Government Accountant" <?= ($employee['position'] == 'Government Accountant') ? 'selected' : '' ?>>Government Accountant</option>
        <option value="Civil Engineer" <?= ($employee['position'] == 'Civil Engineer') ? 'selected' : '' ?>>Civil Engineer</option>
        <option value="Health Officer" <?= ($employee['position'] == 'Health Officer') ? 'selected' : '' ?>>Health Officer</option>
        <option value="IT Specialist" <?= ($employee['position'] == 'IT Specialist') ? 'selected' : '' ?>>IT Specialist</option>
        <option value="Administrative Officer" <?= ($employee['position'] == 'Administrative Officer') ? 'selected' : '' ?>>Administrative Officer</option>
        <option value="Social Worker" <?= ($employee['position'] == 'Social Worker') ? 'selected' : '' ?>>Social Worker</option>
        <option value="Municipal Treasurer" <?= ($employee['position'] == 'Municipal Treasurer') ? 'selected' : '' ?>>Municipal Treasurer</option>
    </select><br>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= $employee['phone'] ?>" required><br>

    <label>Address:</label>
    <input type="text" name="address" value="<?= $employee['address'] ?>" required><br>

    <button type="submit">Update Employee</button>
</form>

<a href="employee_list.php">Back to Employee List</a>

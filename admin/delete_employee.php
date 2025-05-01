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

    // Delete query
    $sql = "DELETE FROM employees WHERE emp_id = '$emp_id'";

    if ($conn->query($sql)) {
        echo "Employee deleted successfully!";
        header("Location: manage_employees.php");
        exit();
    } else {
        echo "Error deleting employee: " . $conn->error;
    }
} else {
    header("Location: manage_employees.php");
    exit();
}
?>

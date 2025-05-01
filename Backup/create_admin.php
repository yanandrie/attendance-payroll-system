<?php
include 'config/db_connect.php';

// Admin details
$full_name = "Administrator";
$phone = "09123456789"; 
$address = "Company HQ";
$username = "admin";
$password = "admin123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check 
$check_admin = "SELECT * FROM users WHERE role = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {

    $sql_employee = "INSERT INTO employees (full_name, phone, address) VALUES ('$full_name', '$phone', '$address')";
    
    if ($conn->query($sql_employee) === TRUE) {
        $emp_id = $conn->insert_id; 

        $sql_user = "INSERT INTO users (emp_id, username, password, role) VALUES ('$emp_id', '$username', '$hashed_password', 'admin')";
        
        if ($conn->query($sql_user) === TRUE) {
            echo "Admin account created successfully! <br>";
            echo "Username: admin <br> Password: admin123";
        } else {
            echo "Error inserting admin into users table: " . $conn->error;
        }
    } else {
        echo "Error inserting admin into employees table: " . $conn->error;
    }
} else {
    echo "Admin account already exists!";
}

$conn->close();
?>

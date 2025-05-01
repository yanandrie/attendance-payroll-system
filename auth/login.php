<?php
session_start();
include '../config/db_connect.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['emp_id'] = $user['emp_id'];

            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                // Automatic Time-in for Employees
                $emp_id = $user['emp_id'];
                $check_attendance = "SELECT * FROM attendance WHERE emp_id = ? AND date = CURDATE()";
                $stmt = $conn->prepare($check_attendance);
                $stmt->bind_param("i", $emp_id);
                $stmt->execute();
                $att_result = $stmt->get_result();

                if ($att_result->num_rows == 0) {
                    $time_in_sql = "INSERT INTO attendance (emp_id, time_in, date) VALUES (?, NOW(), CURDATE())";
                    $stmt = $conn->prepare($time_in_sql);
                    $stmt->bind_param("i", $emp_id);
                    $stmt->execute();
                }

                header("Location: ../employee/dashboard.php");
            }
            exit();
        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "⚠️ User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Payroll & Attendance System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1abc9c, #16a085);
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
        }
        .login-container h2 {
            font-weight: bold;
            margin-bottom: 20px;
            color: #16a085;
        }
        .form-control {
            border-radius: 20px;
            padding-left: 40px;
        }
        .btn-login {
            background: #16a085;
            color: white;
            font-weight: bold;
            border-radius: 20px;
            transition: 0.3s ease-in-out;
        }
        .btn-login:hover {
            background: #1abc9c;
        }
        .form-group {
            position: relative;
        }
        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2><i class="fas fa-user-lock"></i> Login</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error; ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group mb-3">
                <i class="fas fa-user"></i>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-group mb-3">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-login btn-block w-100">Login</button>
        </form>
    </div>

</body>
</html>

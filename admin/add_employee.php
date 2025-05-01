<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db_connect.php';
$salaryRates = include '../config/salary_rates.php';

$positions = array_keys($salaryRates);
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $full_name = $firstname . " " . $lastname;

    $position = htmlspecialchars($_POST['position']);
    $salary = isset($salaryRates[$position]) ? $salaryRates[$position] : 0;

    $barangay = htmlspecialchars($_POST['barangay']);
    $city = htmlspecialchars($_POST['city']);
    $province = htmlspecialchars($_POST['province']);
    $country = htmlspecialchars($_POST['country']);
    $address = "$barangay, $city, $province, $country";

    $phone = htmlspecialchars($_POST['phone']);
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    if (!preg_match('/^\+63\d{10}$/', $phone)) {
        $error = "Phone number must start with +63 and be 13 digits total.";
    } elseif ($salary <= 0) {
        $error = "Salary must be greater than zero!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO employees (full_name, position, salary, phone, address) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdss", $full_name, $position, $salary, $phone, $address);
                
                if ($stmt->execute()) {
                    $emp_id = $conn->insert_id;
                    $stmt = $conn->prepare("INSERT INTO users (emp_id, username, password, role) VALUES (?, ?, ?, 'employee')");
                    $stmt->bind_param("iss", $emp_id, $username, $hashed_password);
                    
                    if ($stmt->execute()) {
                        $success = "Employee added successfully!";
                    } else {
                        throw new Exception("Error adding user: " . $conn->error);
                    }
                } else {
                    throw new Exception("Error adding employee: " . $conn->error);
                }
            } else {
                $error = "Username already exists!";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: #343a40;
            padding: 20px;
            color: white;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 10px 0;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .main-content {
            margin-left: 270px;
            padding: 30px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h4>Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage_employees.php"><i class="fas fa-users"></i> Manage Employees</a>
        <a href="view_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
        <a href="generate_payroll.php"><i class="fas fa-money-bill"></i> Payroll</a>
        <a href="../auth/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h2>Add New Employee</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card p-4">
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="firstname" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="lastname" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Position</label>
                        <select name="position" class="form-control" id="positionSelect" required>
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?= htmlspecialchars($pos) ?>"><?= htmlspecialchars($pos) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Monthly Salary</label>
                        <input type="number" name="salary" id="salaryInput" class="form-control" readonly required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+63XXXXXXXXXX" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <hr class="my-4">

                <h5>Address</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Barangay</label>
                        <input type="text" name="barangay" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Province</label>
                        <input type="text" name="province" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Employee</button>
                    <a href="manage_employees.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const salaryRates = <?= json_encode($salaryRates) ?>;
        document.getElementById('positionSelect').addEventListener('change', function () {
            const selected = this.value;
            document.getElementById('salaryInput').value = salaryRates[selected] || '';
        });
    </script>
</body>
</html>

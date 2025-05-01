<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Pagination setup
$limit = 5; // Number of employees per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Sorting setup
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'full_name';
$sort_order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'desc' : 'asc';
$new_order = $sort_order == 'asc' ? 'desc' : 'asc';

// Fetch employees with sorting and pagination
$sql = "SELECT * FROM employees ORDER BY $sort_column $sort_order LIMIT $start, $limit";
$result = $conn->query($sql);

// Get total employee count for pagination
$count_sql = "SELECT COUNT(*) as total FROM employees";
$count_result = $conn->query($count_sql);
$total_employees = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_employees / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/admincss/manage_employees.css">
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage_employees.php"><i class="fas fa-users"></i> Manage Employees</a>
        <a href="view_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
        <a href="generate_payroll.php"><i class="fas fa-money-bill"></i> Payroll</a>
        <a href="../auth/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="text-center">Employee List</h2>

        <!-- Add Employee & Search Bar -->
        <div class="d-flex justify-content-between mb-3">
            <a href="add_employee.php" class="btn btn-success">➕ Add Employee</a>
            <input type="text" id="searchInput" class="form-control w-25" placeholder="🔍 Search Employee...">
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th><a href="?sort=full_name&order=<?= $new_order ?>" class="text-white">Name</a></th>
                    <th><a href="?sort=position&order=<?= $new_order ?>" class="text-white">Position</a></th>
                    <th><a href="?sort=phone&order=<?= $new_order ?>" class="text-white">Phone</a></th>
                    <th><a href="?sort=address&order=<?= $new_order ?>" class="text-white">Address</a></th>
                    <th><a href="?sort=salary&order=<?= $new_order ?>" class="text-white">Salary</a></th> <!-- ADDED SALARY COLUMN -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="employeeTable">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td>₱<?= number_format($row['salary'], 2) ?></td> <!-- FORMATTED SALARY -->
                        <td class="text-center action-buttons">
                            <a href="edit_employee.php?id=<?= $row['emp_id'] ?>" class="btn btn-primary btn-sm">✏️ Edit</a>
                            <a href="delete_employee.php?id=<?= $row['emp_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&sort=<?= $sort_column ?>&order=<?= $sort_order ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <script>
        // Live search function
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#employeeTable tr');
            
            rows.forEach(row => {
                let name = row.cells[0].textContent.toLowerCase();
                row.style.display = name.includes(filter) ? '' : 'none';
            });
        });
    </script>

</body>
</html>

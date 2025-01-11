<?php
include('header.php');

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to execute queries with error handling
function executeQuery($conn, $sql, $types = null, $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($types && $params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        die("Query failed: " . mysqli_stmt_error($stmt)); // Add this to catch errors
    }
    mysqli_stmt_close($stmt);
}

// Add Admin
if (isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password hashing
    $user_type = $_POST['user_type'];
    $status = $_POST['status'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];

    // Check if the username already exists
    $sql_check = "SELECT COUNT(*) FROM admin WHERE username = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, 's', $username);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_bind_result($stmt_check, $username_count);
    mysqli_stmt_fetch($stmt_check);
    mysqli_stmt_close($stmt_check);

    if ($username_count > 0) {
        echo "<script>alert('Username already exists!');</script>";
    } else {
        $sql = "INSERT INTO admin (username, password, user_type, status, first_name, last_name, email, contact_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        executeQuery($conn, $sql, 'ssisssss', [
            $username, $password, $user_type, $status, $first_name, $last_name, $email, $contact_number
        ]);
    }
}

// Update Admin
if (isset($_POST['update_admin'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
    $user_type = $_POST['user_type'];
    $status = $_POST['status'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];

    if ($password) {
        $sql = "UPDATE admin SET username=?, password=?, user_type=?, status=?, first_name=?, last_name=?, email=?, contact_number=? WHERE id=?";
        executeQuery($conn, $sql, 'ssisssssi', [
            $username, $password, $user_type, $status, $first_name, $last_name, $email, $contact_number, $id
        ]);
    } else {
        $sql = "UPDATE admin SET username=?, user_type=?, status=?, first_name=?, last_name=?, email=?, contact_number=? WHERE id=?";
        executeQuery($conn, $sql, 'sisssssi', [
            $username, $user_type, $status, $first_name, $last_name, $email, $contact_number, $id
        ]);
    }
}

// Delete Admin
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $sql = "DELETE FROM admin WHERE id=?";
    executeQuery($conn, $sql, 'i', [$id]);
    header("Location: ./superadmin"); // Redirect to avoid duplicate submissions
    exit;
}


// Fetch Admins
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
}

$sql = "SELECT * FROM admin";
if (!empty($searchQuery)) {
    $sql .= " WHERE LOWER(username) LIKE LOWER(?) 
              OR LOWER(first_name) LIKE LOWER(?) 
              OR LOWER(last_name) LIKE LOWER(?) 
              OR LOWER(email) LIKE LOWER(?)";
    $stmt = mysqli_prepare($conn, $sql);
    $searchParam = '%' . $searchQuery . '%';
    mysqli_stmt_bind_param($stmt, 'ssss', $searchParam, $searchParam, $searchParam, $searchParam);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}
?>

<nav class="sidebar">
    <ul>
        <li><a href="./dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="./superadmin"><i class="fas fa-user-friends"></i> Manage Admins</a></li>
    </ul>
</nav>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" />


<!-- Admin Management UI -->
<main class="main-content">
    <section id="dashboard">
        <h2 class="text-2xl font-bold mb-6">ADMIN MANAGEMENT</h2>
        
        <!-- Add Admin Form -->
        <div class="card">
            <div class="card-header">
                <h3>Add New Admin</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="col-md-4">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Username" required>
                        </div>

                        <div class="col-md-4">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>

                        <div class="col-md-4">
                            <label for="user_type">User Type</label>
                            <select name="user_type" required>
                                <option value="1">Admin</option>
                                <option value="2">User</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="status">Status</label>
                            <select name="status" required>
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                        </div>

                        <div class="col-md-6">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Email" required>
                        </div>

                        <div class="col-md-6">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" class="form-control" name="contact_number" placeholder="Contact Number" required>
                        </div>
                    </div>

                    <div class="form-row justify-content-center mt-4">
                        <div class="col-md-4 text-center">
                            <button type="submit" name="add_admin" class="btn btn-primary">Add Admin</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Admin Table -->
        <div class="card">
    <div class="card-header">
        <h3>Admin List</h3>
        <div class="card-body">
        <table id="myTable" class="employee-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Contact Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['contact_number']) ?></td>
                            <td class="action-buttons">
                            <a href="./edit_employee?id=<?php echo $row['id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to Archive this Admin?');">Archive</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
$(document).ready(function () {
    $('#myTable').DataTable();
});
</script>

<script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<?php include('footer.php'); ?>
</body>
</html>


    <?php
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
            echo "Error: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }

    // Fetch employee details for editing
    $employee = null;
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM admin WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $employee = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }

// Update Admin
if (isset($_POST['update_admin'])) {
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];
        $username = trim($_POST['username']);
        $user_type = trim($_POST['user_type']);
        $status = trim($_POST['status']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $contact_number = trim($_POST['contact_number']);

        // Validate required fields
        if (empty($username) || empty($user_type) || empty($status) || empty($first_name) || empty($last_name) || empty($email) || empty($contact_number)) {
            header("Location: edit_admin?id=$id&error=All fields are required");
            exit();
        }

        // Update query
        $sql = "UPDATE admin SET username=?, user_type=?, status=?, first_name=?, last_name=?, email=?, contact_number=? WHERE id=?";
        executeQuery($conn, $sql, 'sisssssi', [
            $username, $user_type, $status, $first_name, $last_name, $email, $contact_number, $id
        ]);

        // Redirect back to edit page after update
        header("Location: edit_admin?id=$id");
        exit();
    } else {
        header("Location: admin_list?error=Invalid ID");
        exit();
    }
}

?>



    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Employee</title>
        <!-- <link rel="stylesheet" href="styles.css"> -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    </head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" />
<script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome for the eye icon -->
<?php include('header.php'); ?>
    <?php include('includes/sideBar.php'); ?>
    <body>
        <main class="main-content">
            <section id="edit-employee">
                <h2>Edit Employee Details</h2>
                <?php if ($employee): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Edit Employee: <?php echo $employee['last_name'] . ', ' . $employee['first_name']; ?></h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Last Name</label>
                                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Email Address</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Contact Number</label>
                                        <input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($employee['contact_number']); ?>" required> 
                                    </div>
                                </div>

                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Username</label>
                                        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($employee['username']); ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>User Type</label>
                                        <input type="text" class="form-control" name="user_type" value="<?php echo htmlspecialchars($employee['user_type']); ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Status</label>
                                        <input type="text" class="form-control" name="status" value="<?php echo htmlspecialchars($employee['status']); ?>" required>
                                    </div>
                                </div>
                                <div class="form-row">
                            <div class="form-group col-md-6">
                            <button type="submit" name="update_admin" class="btn btn-primary">Update Admin</button>
                        <a href="./superadmin" class="btn btn-cancel">Cancel</a>
                                </div>
                                    </div>
                                </div>


                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-danger">Employee not found.</p>
                <?php endif; ?>
            </section>
        </main>
<style>
        /* Sidebar Dropdown */
.sidebar ul .dropdown {
    position: relative;
}

.sidebar ul .dropdown .dropdown-toggle {
    cursor: pointer;
}

.sidebar ul .dropdown .dropdown-menu {
    display: none; /* Hide by default */
    list-style: none;
    padding: 0;
    margin: 0;
    background-color: #a83a3a;
}

.sidebar ul .dropdown .dropdown-menu li a {
    padding-left: 2rem; /* Indent for dropdown items */
    display: block;
    color: #fff;
}

.sidebar ul .dropdown .dropdown-menu li a:hover {
    background-color: #c45b5b;
}

/* Show dropdown menu when the parent is active */
.sidebar ul .dropdown.active .dropdown-menu {
    display: block; /* Show the dropdown */
}

/* Optional styling for active links */
.sidebar ul li a.active {
    background-color: #c45b5b;
}
</style>

<script>
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function (event) {
        const parent = this.parentElement;

        // Prevent the link's default behavior
        event.preventDefault();

        // Toggle the active class
        parent.classList.toggle('active');
    });
});

</script>
        <?php include('footer.php'); ?>
    </body>
    </html>

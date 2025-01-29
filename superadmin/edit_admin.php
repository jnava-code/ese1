<?php include('header.php'); ?>
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
?>


<nav class="sidebar">
    <ul>
        <li><a href="./dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="./superadmin"><i class="fas fa-user-friends"></i> Manage Admins</a></li>
    </ul>
</nav>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Employee</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    </head>

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
                                        <input type="text" class="form-control" name="last_name" value="<?php echo $employee['last_name']; ?>" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                    <label>First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?php echo $employee['first_name']; ?>" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>Email Address</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo $employee['email']; ?>" required>
                                    </div>
                                </div>

                                    <div class="form-group col-md-6">
                                    <label>Contact Number</label>
                                    <input type="text" class="form-control" name="contact_number" value="<?php echo $employee['contact_number']; ?>" required>
                                    
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>User Type</label>
                                        <input type="text" class="form-control" name="user_type" value="<?php echo $employee['user_type']; ?>" required>
                                    </div>
                                    </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>Username</label>
                                        <input type="text" class="form-control" name="username" value="<?php echo $employee['username']; ?>" required>
                                    </div>
                            </div>

                            
                                <div class="form-row">
                            <div class="form-group col-md-6">
                            <button type="submit" name="update_employee" class="btn btn-primary">Update Employee</button>
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

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
        $sql = "SELECT * FROM employees WHERE id=?";
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
                                    <label>Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name" value="<?php echo $employee['middle_name']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>Email Address</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo $employee['email']; ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                    <label>Job Position</label>
                                        <input type="text" class="form-control" name="position" value="<?php echo $employee['position']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>Hire Date</label>
                                        <input type="date" class="form-control" name="hire_date" value="<?php echo $employee['hire_date']; ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                    <label>Department</label>
                                        <select name="department" class="form-control" required>
                                            <option value="">Select Department</option>
                                            <option value="Sales" <?php echo ($employee['department'] == 'Sales') ? 'selected' : ''; ?>>Sales</option>
                                            <option value="Management" <?php echo ($employee['department'] == 'Management') ? 'selected' : ''; ?>>Management</option>
                                            <option value="Technical" <?php echo ($employee['department'] == 'Technical') ? 'selected' : ''; ?>>Technical</option>
                                            <option value="Purchasing" <?php echo ($employee['department'] == 'Purchasing') ? 'selected' : ''; ?>>Purchasing</option>
                                            <option value="Accounting" <?php echo ($employee['department'] == 'Accounting') ? 'selected' : ''; ?>>Accounting</option>
                                            <option value="Admin" <?php echo ($employee['department'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                            <option value="Chemicals" <?php echo ($employee['department'] == 'Chemicals') ? 'selected' : ''; ?>>Chemicals</option>
                                            <option value="HVAC" <?php echo ($employee['department'] == 'HVAC') ? 'selected' : ''; ?>>HVAC</option>
                                        </select>
                                    </div>
    

                                <!-- Additional fields -->
                                
                                <div class="form-row">
                                <div class="form-group col-md-6">
                                <label>Employee ID</label>
                                    <input type="text" class="form-control" name="employee_id" value="<?php echo $employee['employee_id']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                <label>Password</label>
                                    <input type="text" class="form-control" name="password" value="<?php echo $employee['password']; ?>" required>
                                    </div>
                                </div>

                                    <div class="form-group col-md-6">
                                    <label>Date of Birth</label>
                                        <input type="date" class="form-control" name="date_of_birth" value="<?php echo $employee['date_of_birth']; ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                    <label>Contact Number</label>
                                    <input type="text" class="form-control" name="contact_number" value="<?php echo $employee['contact_number']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>Address</label>
                                        <input type="text" class="form-control" name="perma_address" value="<?php echo $employee['perma_address']; ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                    <label>Civil Status</label>
                                        <input type="text" class="form-control" name="civil_status" value="<?php echo $employee['civil_status']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>SSS Number</label>
                                        <input type="text" class="form-control" name="sss_number" value="<?php echo $employee['sss_number']; ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                    <label>PHILHEALTH Number</label>
                                        <input type="text" class="form-control" name="philhealth_number" value="<?php echo $employee['philhealth_number']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>PAGIBIG Number</label>
                                        <input type="text" class="form-control" name="pagibig_number" value="<?php echo $employee['pagibig_number']; ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                    <label>TIN Number</label>
                                        <input type="text" class="form-control" name="tin_number" value="<?php echo $employee['tin_number']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>Emergency Contact Name</label>
                                        <input type="text" class="form-control" name="emergency_contact_name" value="<?php echo $employee['emergency_contact_name']; ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                    <label>Emergenct contact Number</label>
                                        <input type="text" class="form-control" name="emergency_contact_number" value="<?php echo $employee['emergency_contact_number']; ?>" required>
                                    </div>
                                </div>

                    <div class="form-row">
                            <div class="form-group col-md-6">
                        <label for="educational_background">Educational Attainment</label>
                            <select class="form-control" name="educational_background" id="educational_background" required>
                            <option value="">Select Educational Attainment</option>
                            <option value="High School Graduate" <?php echo ($employee['educational_background'] == 'High School Graduate') ? 'selected' : ''; ?>>High School Graduate</option>
                            <option value="Vocational Graduate" <?php echo ($employee['educational_background'] == 'Vocational Graduate') ? 'selected' : ''; ?>>Vocational Graduate</option>
                            <option value="College Undergraduate" <?php echo ($employee['educational_background'] == 'College Undergraduate') ? 'selected' : ''; ?>>College Undergraduate</option>
                            <option value="College Graduate" <?php echo ($employee['educational_background'] == 'College Graduate') ? 'selected' : ''; ?>>College Graduate</option>
                            <option value="Postgraduate" <?php echo ($employee['educational_background'] == 'Postgraduate') ? 'selected' : ''; ?>>Postgraduate</option>
                     </select>
                       </div>
                       <div class="form-group col-md-6">
                      <label for="skills">Skills</label>
                      <textarea class="form-control" name="skills" id="skills" required><?php echo $employee['skills']; ?></textarea>
                        </div>
                        </div>

                            

                                    <div class="form-row">
                                    <div class="form-group col-md-6">
                                    <label>Employment Status</label>
                                        <select name="employment_status" class="form-control" required>
                                            <option value="">Employment Status</option>
                                            <option value="Active" <?php echo ($employee['employment_status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="Inactive" <?php echo ($employee['employment_status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                            <div class="form-group col-md-6">
                            <button type="submit" name="update_employee" class="btn btn-primary">Update Employee</button>
                        <a href="./employees" class="btn btn-cancel">Cancel</a>
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

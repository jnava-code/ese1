<?php include('header.php'); ?>
    <?php
    // Database connection
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Function to execute queries with error handling
    // function executeQuery($conn, $sql, $types = null, $params = []) {
    //     $stmt = mysqli_prepare($conn, $sql);
    //     if ($types && $params) {
    //         mysqli_stmt_bind_param($stmt, $types, ...$params);
    //     }
    //     if (!mysqli_stmt_execute($stmt)) {
    //         echo "Error: " . mysqli_stmt_error($stmt);
    //     }
    //     mysqli_stmt_close($stmt);
    // }

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

    // Update Employee
    if (isset($_POST['update_employee'])) {
        $id = $_GET['id'];  // Get the ID from the URL
        $last_name = $_POST['last_name'];
        $first_name = $_POST['first_name'];
        $middle_name = $_POST['middle_name'];
        $suffix = $_POST['suffix'];
        $email = $_POST['email'];
        $position = $_POST['position'];
        $hire_date = $_POST['hire_date'];
        $department = $_POST['department'];
        $employment_status = $_POST['employment_status'];
        $employee_id = $_POST['employee_id'];
        // $password = $_POST['password'];  // Ensure password is retrieved
        $date_of_birth = $_POST['date_of_birth'];
        $contact_number = $_POST['contact_number'];
        $perma_address = $_POST['perma_address'];   
        $civil_status = $_POST['civil_status'];
        $sss_number = $_POST['sss_number'];
        $philhealth_number = $_POST['philhealth_number'];
        $pagibig_number = $_POST['pagibig_number'];
        $tin_number = $_POST['tin_number'];
        $emergency_contact_name = $_POST['emergency_contact_name'];
        $emergency_contact_number = $_POST['emergency_contact_number'];
        $educational_background = $_POST['educational_background'];
        $skills = $_POST['skills'];
        $username = $_POST['username'];
        $sick_leave = $_POST['sick_leave'];
        $vacation_leave = $_POST['vacation_leave'];
        $maternity_leave = $_POST['maternity_leave'];
        $paternity_leave = $_POST['paternity_leave'];

        // Ensure that the SQL query is correct
        $sql = "UPDATE employees 
                SET last_name=?, first_name=?, middle_name=?, suffix=?, email=?, 
                    position=?, hire_date=?, department=?, employment_status=?,
                    employee_id=?, date_of_birth=?, contact_number=?, perma_address=?,
                    civil_status=?, sss_number=?, philhealth_number=?, pagibig_number=?,
                    tin_number=?, emergency_contact_name=?, emergency_contact_number=?, 
                    educational_background=?, skills=?, username=?, sick_leave=? , vacation_leave=? , maternity_leave=? , paternity_leave=? 
                WHERE id=?";
    
        // Prepare the statement
        $stmt = $conn->prepare($sql);
    
        // Correct the bind_param call: Match the correct number of placeholders with type definitions
        $stmt->bind_param("sssssssssssssssssssssssssssi", 
                          $last_name, $first_name, $middle_name, $suffix, $email, 
                          $position, $hire_date, $department, $employment_status, 
                          $employee_id, $date_of_birth, $contact_number, 
                          $perma_address, $civil_status, $sss_number, $philhealth_number, 
                          $pagibig_number, $tin_number, $emergency_contact_name, 
                          $emergency_contact_number, $educational_background, $skills, 
                          $username, $sick_leave, $vacation_leave, $maternity_leave, $paternity_leave, $id);
    
        // Execute the statement
        if ($stmt->execute()) {
            echo "Employee details updated successfully.";
        } else {
            echo "Error updating employee details: " . $stmt->error;
        }
    
        // Close the statement
        $stmt->close();
    
        // Redirect to another page after the update
        header("Location: ./employees");
        exit();
    }
    
    // // Execute the query using the appropriate parameters
    // executeQuery($conn, $sql, 'ssssssssssssssssssssssi', [
    //     $last_name, $first_name, $middle_name, $email, 
    //     $position, $hire_date, $department, $employment_status,
    //     $employee_id, $password, $date_of_birth, $contact_number, $perma_address,
    //     $civil_status, $sss_number, $philhealth_number, $pagibig_number,
    //     $tin_number, $emergency_contact_name, $emergency_contact_number, 
    //     $educational_background, $skills, $id
    // ]);     
?>



<nav class="sidebar">
    <ul>
        <li><a href="./dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="./employees"><i class="fas fa-user-friends"></i> Employees Profile</a></li>
        <li class="dropdown">
            <a href="#attendance-dropdown" class="dropdown-toggle"><i class="fas fa-calendar-check"></i> Attendance Management</a>
            <ul class="dropdown-menu" id="attendance-dropdown">
                <li><a href="./daily-attendance">Daily Attendance</a></li>
                <li><a href="./monthly-attendance">Monthly Attendance</a></li>
            </ul>
        </li>
        <li><a href="./leave"><i class="fas fa-paper-plane"></i> Request Leave</a></li>
        <li><a href="./predict"><i class="fas fa-chart-line"></i> Prediction</a></li>
        <li><a href="./reports"><i class="fas fa-file-alt"></i> Reports</a></li>
        <li><a href="./performance-evaluation"><i class="fas fa-trophy"></i> Performance</a></li>
        <li><a href="./satisfaction"><i class="fas fa-smile"></i> Satisfaction</a></li>
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
                <div class="col-md-4">
                <label for="date_of_birth">First Name</label>
                    <input type="text" class="form-control" name="first_name" placeholder="First Name" value="<?php  echo $employee['first_name'] ?>" required>
                </div>
                <div class="col-md-4">
                <label for="date_of_birth">Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" placeholder="Middle Name" value="<?php  echo $employee['middle_name'] ?>">
                </div>
                <div class="col-md-4">
                <label for="date_of_birth">Last Name</label>
                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="<?php  echo $employee['last_name'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="suffix">Suffix</label>
                    <input type="text" class="form-control" name="suffix" placeholder="e.g., Sr., Jr., III" value="<?php  echo $employee['suffix'] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" class="form-control" name="contact_number" placeholder="e.g., +63 912-345-6789" value="<?php  echo $employee['contact_number'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="civil_status">Civil Status</label>
                    <!-- <input type="text" class="form-control" name="civil_status" placeholder="Civil Status" value="<?php  echo $employee['civil_status'] ?>" required> -->
                    <select name="civil_status" class="form-control" required>
                        <option value="" <?php echo ($employee['civil_status'] == '') ? 'selected' : ''; ?>>Select Civil Status</option>
                        <option value="Single" <?php echo ($employee['civil_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo ($employee['civil_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Widowed" <?php echo ($employee['civil_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Divorced" <?php echo ($employee['civil_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                        <option value="Separated" <?php echo ($employee['civil_status'] == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                        <option value="Annulled" <?php echo ($employee['civil_status'] == 'Annulled') ? 'selected' : ''; ?>>Annulled</option>
                        <option value="Domestic Partnership" <?php echo ($employee['civil_status'] == 'Domestic Partnership') ? 'selected' : ''; ?>>Domestic Partnership</option>
                        <option value="Legally Separated" <?php echo ($employee['civil_status'] == 'Legally Separated') ? 'selected' : ''; ?>>Legally Separated</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="educational_background">Education Background</label>
                    <input type="text" class="form-control" name="educational_background" placeholder="Education Background" value="<?php  echo $employee['educational_background'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" class="form-control" name="date_of_birth" placeholder="Date of Birth" value="<?php  echo $employee['date_of_birth'] ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="position">Position</label>
                    <input type="text" class="form-control" name="position" placeholder="Position" value="<?php  echo $employee['position'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="skills">Skills</label>
                    <input type="text" class="form-control" name="skills" placeholder="Skills" value="<?php  echo $employee['skills'] ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="perma_address">Permanent Address</label>
                    <input type="text" class="form-control" name="perma_address" placeholder="Permanent Address" value="<?php  echo $employee['perma_address'] ?>" required>
                </div>              
            </div>
            
            <div class="form-row">
                <div class="col-md-6">
                    <label for="date_of_birth">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="Email" value="<?php  echo $employee['email'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="department">Department</label>
                    <select name="department" class="form-control" required>
                        <option value="" <?php echo ($employee['department'] == '') ? 'selected' : ''; ?>>Select Department</option>
                        <option value="Admin" <?php echo ($employee['department'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="Chemical" <?php echo ($employee['department'] == 'Chemical') ? 'selected' : ''; ?>>Chemical</option>
                        <option value="Procurement" <?php echo ($employee['department'] == 'Procurement') ? 'selected' : ''; ?>>Procurement</option>
                        <option value="Sales" <?php echo ($employee['department'] == 'Sales') ? 'selected' : ''; ?>>Sales</option>
                        <option value="Sales & Marketing" <?php echo ($employee['department'] == 'Sales & Marketing') ? 'selected' : ''; ?>>Sales & Marketing</option>
                        <option value="Technical" <?php echo ($employee['department'] == 'Technical') ? 'selected' : ''; ?>>Technical</option>
                        <option value="Technical Sales" <?php echo ($employee['department'] == 'Technical') ? 'selected' : ''; ?>>Technical Sales</option>
                        <option value="Work Order" <?php echo ($employee['department'] == 'Work Order') ? 'selected' : ''; ?>>Work Order</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" class="form-control" name="emergency_contact_name" placeholder="Emergency Contact Name" value="<?php  echo $employee['emergency_contact_name'] ?>"  required>
                </div>
                <div class="col-md-6">
                    <label for="emergency_contact_number">Emergency Contact Number</label>
                    <input type="text" class="form-control" name="emergency_contact_number" placeholder="Emergency Contact Number" value="<?php  echo $employee['emergency_contact_number'] ?>" required>
                </div>
            </div>

            <div class="form-row">
            <div class="col-md-4">
                <label for="hire_date">Date Hired</label>
                <input type="date" class="form-control" name="hire_date" id="hire_date" placeholder="yyyy-mm-dd" value="<?php  echo $employee['hire_date'] ?>" required>
            </div>
            </div>
         
             <div class="form-row">
                <div class="form-group col-md-6">
                <label>Employment Status</label>
                <select name="employment_status" class="form-control" required>
                        <option value="" <?php echo ($employee['employment_status'] == '') ? 'selected' : ''; ?>>Employment Status</option>
                        <option value="Regular" <?php echo ($employee['employment_status'] == 'Regular') ? 'selected' : ''; ?>>Regular</option>
                        <option value="Probationary" <?php echo ($employee['employment_status'] == 'Probationary') ? 'selected' : ''; ?>>Probationary</option>
                        <option value="Terminated" <?php echo ($employee['employment_status'] == 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
                        <option value="Resigned" <?php echo ($employee['employment_status'] == 'Resigned') ? 'selected' : ''; ?>>Resigned</option>
                    </select>
                </div>
                </div>
      

            <!-- New fields for update_employee -->
            <div class="form-row">

                <div class="col-md-4">
                <label for="date_of_birth">Username</label>
                    <input type="text" class="form-control" name="username" placeholder="Username" value="<?php  echo $employee['username'] ?>" required>
                </div>

                <div class="col-md-4">
                <label for="date_of_birth">Employee ID</label>
                    <input type="text" class="form-control" name="employee_id" placeholder="Employee ID" value="<?php  echo $employee['employee_id'] ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="date_of_birth">SSS Number</label>
                    <input type="text" class="form-control" name="sss_number" placeholder="SSS Number" required pattern="\d{2}-\d{7}-\d{1}" title="SSS should be in the format 00-0000000-0" value="<?php  echo $employee['sss_number'] ?>">
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">PHILHEALTH Number</label>
                    <input type="text" class="form-control" name="philhealth_number" placeholder="PhilHealth Number" required pattern="\d{2}-\d{9}-\d{1}" title="PhilHealth should be in the format 00-000000000-0" value="<?php  echo $employee['philhealth_number'] ?>">
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">TIN Number</label>
                    <input type="text" class="form-control" name="tin_number" placeholder="TIN Number" required pattern="\d{3}-\d{3}-\d{3}-\d{3}" title="TIN should be in the format 000-000-000-000" value="<?php  echo $employee['tin_number'] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                <label for="date_of_birth">PAGIBIG Number</label>
                <input type="text" class="form-control" name="pagibig_number" placeholder="PagIBIG Number" required pattern="\d{4}-\d{4}-\d{4}" title="PagIBIG should be in the format 0000-0000-0000" value="<?php  echo $employee['pagibig_number'] ?>">
                </div>
            </div>
                        
            <div class="form-row">
                    <div class="col-md-6">
                        <label for="sick_leave">Sick Leave</label>
                        <input type="text" id="sick_leave" class="form-control" name="sick_leave" placeholder="Sick Leave" value="<?php  echo $employee['sick_leave'] ?>"readonly>
                    </div>

                    <div class="col-md-6">
                        <label for="vacation_leave">Vacation Credit</label>
                        <input type="text" id="vacation_leave" class="form-control" name="vacation_leave" placeholder="Vacation Credit" value="<?php  echo $employee['vacation_leave'] ?>"readonly>
                    </div>

                    <div class="col-md-6">
                        <label for="maternity_leave">Maternity Credit</label>
                        <input type="text" id="maternity_leave" class="form-control" name="maternity_leave" placeholder="Maternity Credit" value="<?php  echo $employee['maternity_leave'] ?>"readonly>
                    </div>

                    <div class="col-md-6">
                        <label for="paternity_leave">Paternity Credit</label>
                        <input type="text" id="paternity_leave" class="form-control" name="paternity_leave" placeholder="Paternity Credit" value="<?php  echo $employee['paternity_leave'] ?>"readonly>
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

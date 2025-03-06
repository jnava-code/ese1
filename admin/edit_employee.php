
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
        $gender = $_POST['gender'];
        $email = $_POST['email'];
        $position = $_POST['position'];
        $hire_date = $_POST['hire_date'];
        $department = $_POST['department'];
        $employment_status = $_POST['employment_status'];
        $employee_id = $_POST['employee_id'];
        $employee_id = str_replace('-', '', $_POST['employee_id'] ?? '');
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

        // Ensure that the SQL query is correct
        $sql = "UPDATE employees 
                SET last_name=?, first_name=?, middle_name=?, suffix=?, gender=?, email=?, 
                    position=?, hire_date=?, department=?, employment_status=?,
                    employee_id=?, date_of_birth=?, contact_number=?, perma_address=?,
                    civil_status=?, sss_number=?, philhealth_number=?, pagibig_number=?,
                    tin_number=?, emergency_contact_name=?, emergency_contact_number=?, 
                    educational_background=?, skills=?, username=?
                WHERE id=?";
    
        // Prepare the statement
        $stmt = $conn->prepare($sql);
    
        // Correct the bind_param call: Match the correct number of placeholders with type definitions
        $stmt->bind_param("ssssssssssssssssssssssssssssi", 
                          $last_name, $first_name, $middle_name, $suffix, $gender, $email, 
                          $position, $hire_date, $department, $employment_status, 
                          $employee_id, $date_of_birth, $contact_number, 
                          $perma_address, $civil_status, $sss_number, $philhealth_number, 
                          $pagibig_number, $tin_number, $emergency_contact_name, 
                          $emergency_contact_number, $educational_background, $skills, 
                          $username, $id);
    
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

    function generatePasswordFromBday($date_of_birth) {
        // Extract month, day, and year from the date
        $date = DateTime::createFromFormat('Y-m-d', $date_of_birth);
        
        // Format the password as 'mmmddyyyy', where mmm is the 3-letter month, dd is day, and yyyy is year
        $password = $date->format('M') . $date->format('d') . $date->format('Y');
        
        return strtolower($password); // To ensure the month is lowercase
    }

    if (isset($_POST['reset_password'])) {
        // Get the posted data
        $id = $_GET['id'];
        $date_of_birth = $_POST['date_of_birth']; // The date of birth input
    
        // Generate the new password from the date of birth
        $password = generatePasswordFromBday($date_of_birth);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Proceed to update the password in the database
        $sqlReset = "UPDATE employees SET password=? WHERE id=?";
        if ($stmt = $conn->prepare($sqlReset)) {
            $stmt->bind_param("si", $hashed_password, $id);
            
            // Execute the query to update the password
            if ($stmt->execute()) {
                echo "Employee's password has been successfully changed.";
                header("Location: ./employees");
                exit();
            } else {
                echo "Error updating employee's password: " . $stmt->error;
            }
    
            $stmt->close();
        } else {
            echo "Error preparing the statement: " . $conn->error;
        }
        
        exit(); // End script execution after updating password
    }
    
    
    
    
?>

<?php include('header.php'); ?>
<?php include('includes/sideBar.php'); ?>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Employee</title>
        <link rel="stylesheet" href="css/styles.css">
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
                    <input type="text" class="form-control" name="first_name" placeholder="First Name" value="<?php  echo $employee['first_name'] ?>" >
                </div>
                <div class="col-md-4">
                <label for="date_of_birth">Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" placeholder="Middle Name" value="<?php  echo $employee['middle_name'] ?>">
                </div>
                <div class="col-md-4">
                <label for="date_of_birth">Last Name</label>
                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="<?php  echo $employee['last_name'] ?>" >
                </div>
                <div class="col-md-4">
                    <label for="suffix">Suffix</label>
                    <input type="text" class="form-control" name="suffix" placeholder="e.g., Sr., Jr., III" value="<?php  echo $employee['suffix'] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="gender">Sex</label>
                    <select id="gender" name="gender" class="form-control" >
                        <option value="" <?php echo ($employee['gender'] == '') ? 'selected' : ''; ?>>Select Sex</option>
                        <option value="Male" <?php echo ($employee['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($employee['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="contact_number" >Contact Number</label>
                    <input type="text" class="form-control" name="contact_number" placeholder="e.g., +63 912-345-6789" value="<?php  echo $employee['contact_number'] ?>" >
                </div>
                <div class="col-md-6">
                    <label for="civil_status">Civil Status</label>
                    <!-- <input type="text" class="form-control" name="civil_status" placeholder="Civil Status" value="<?php  echo $employee['civil_status'] ?>" > -->
                    <select name="civil_status" class="form-control" >
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
                    <input type="text" class="form-control" name="educational_background" placeholder="Education Background" value="<?php  echo $employee['educational_background'] ?>" >
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" class="form-control" name="date_of_birth" placeholder="Date of Birth" value="<?php  echo $employee['date_of_birth'] ?>" >
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="position">Position</label>
                    <input type="text" class="form-control" name="position" placeholder="Position" value="<?php  echo $employee['position'] ?>" >
                </div>
                <div class="col-md-6">
                    <label for="skills">Skills</label>
                    <input type="text" class="form-control" name="skills" placeholder="Skills" value="<?php  echo $employee['skills'] ?>" >
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="perma_address">Permanent Address</label>
                    <input type="text" class="form-control" name="perma_address" placeholder="Permanent Address" value="<?php  echo $employee['perma_address'] ?>" >
                </div>              
            </div>
            
            <div class="form-row">
                <div class="col-md-6">
                    <label for="date_of_birth">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="Email" value="<?php  echo $employee['email'] ?>" >
                </div>
                <div class="col-md-6">
                    <label for="department">Department</label>
                    <select name="department" class="form-control" >
                        <option value="">Select Department</option>
                        <?php 
                            $deptSelect = "SELECT * FROM departments WHERE is_archived = 0 ORDER BY dept_name ASC";
                            $deptResult = mysqli_query($conn, $deptSelect);

                            if($deptResult) {
                                while($row = mysqli_fetch_assoc($deptResult)) {         
                        ?>
                            <option value="<?php echo $row['dept_name']; ?>" 
                                <?php echo ($employee['department'] == $row['dept_name']) ? 'selected' : ''; ?>>
                                <?php echo $row['dept_name']; ?>
                            </option>
                        <?php }
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" class="form-control" name="emergency_contact_name" placeholder="Emergency Contact Name" value="<?php  echo $employee['emergency_contact_name'] ?>"  >
                </div>
                <div class="col-md-6">
                    <label for="emergency_contact_number">Emergency Contact Number</label>
                    <input type="text" class="form-control" name="emergency_contact_number" placeholder="Emergency Contact Number" value="<?php  echo $employee['emergency_contact_number'] ?>" >
                </div>
            </div>

            <div class="form-row">
            <div class="col-md-4">
                <label for="hire_date">Date Hired</label>
                <input type="date" class="form-control" name="hire_date" id="hire_date" placeholder="yyyy-mm-dd" value="<?php  echo $employee['hire_date'] ?>" >
            </div>
            </div>
         
             <div class="form-row">
                <div class="form-group col-md-6">
                <label>Employment Status</label>
                <select id="employment_status" name="employment_status" class="form-control" >
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
                <label for="username">Username</label>
                    <input type="text" class="form-control" name="username" placeholder="Username" value="<?php  echo $employee['username'] ?>" >
                </div>

                <div class="col-md-4">
                <label for="employee_id">Employee ID</label>
                    <input id="employee_id" type="text" class="form-control" name="employee_id" placeholder="Employee ID" value="<?php  echo $employee['employee_id'] ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="date_of_birth">SSS Number</label>
                    <input type="text" class="form-control" name="sss_number" placeholder="SSS Number"  pattern="\d{2}-\d{7}-\d{1}" title="SSS should be in the format 00-0000000-0" value="<?php  echo $employee['sss_number'] ?>">
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">PHILHEALTH Number</label>
                    <input type="text" class="form-control" name="philhealth_number" placeholder="PhilHealth Number"  pattern="\d{2}-\d{9}-\d{1}" title="PhilHealth should be in the format 00-000000000-0" value="<?php  echo $employee['philhealth_number'] ?>">
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">TIN Number</label>
                    <input type="text" class="form-control" name="tin_number" placeholder="TIN Number"  pattern="\d{3}-\d{3}-\d{3}-\d{3}" title="TIN should be in the format 000-000-000-000" value="<?php  echo $employee['tin_number'] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                <label for="date_of_birth">PAGIBIG Number</label>
                <input type="text" class="form-control" name="pagibig_number" placeholder="PagIBIG Number"  pattern="\d{4}-\d{4}-\d{4}" title="PagIBIG should be in the format 0000-0000-0000" value="<?php  echo $employee['pagibig_number'] ?>">
                </div>
            </div>
                        
            <div class="form-row">
                    <div id="sick_leave_container" class="col-md-6">
                        <label for="sick_leave">Sick Leave</label>
                        <input type="text" id="sick_leave" class="form-control" name="sick_leave" placeholder="Sick Leave" value="<?php  echo $employee['sick_leave'] ?>" disabled>
                    </div>

                    <div id="vacation_leave_container" class="col-md-6">
                        <label for="vacation_leave">Vacation Credit</label>
                        <input type="text" id="vacation_leave" class="form-control" name="vacation_leave" placeholder="Vacation Credit" value="<?php  echo $employee['vacation_leave'] ?>" disabled>
                    </div>

                    <div id="maternity_leave_container" class="col-md-6">
                        <label for="maternity_leave">Maternity Credit</label>
                        <input type="text" id="maternity_leave" class="form-control" name="maternity_leave" placeholder="Maternity Credit" value="<?php  echo $employee['maternity_leave'] ?>" disabled>
                    </div>

                    <div id="paternity_leave_container" class="col-md-6">
                        <label for="paternity_leave">Paternity Credit</label>
                        <input type="text" id="paternity_leave" class="form-control" name="paternity_leave" placeholder="Paternity Credit" value="<?php  echo $employee['paternity_leave'] ?>" disabled>
                    </div>
                </div>

                                <div class="form-row">
                            <div class="form-group col-md-6">
                            <button type="submit" name="update_employee" class="btn btn-primary">Update Employee</button>
                            <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
                            <a href="./employees" class="btn btn-primary">Cancel</a>
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

<script>
    const employeeId = document.getElementById("employee_id");
    const employeeIdDisplay = document.querySelectorAll(".employee_id_display");
    // const employeeIdDataset = document.getElementById("employee_id_display");
    const sickContainer = document.getElementById("sick_leave_container");
    const vacationContainer = document.getElementById("vacation_leave_container");
    const maternityContainer = document.getElementById("maternity_leave_container");
    const paternityContainer = document.getElementById("paternity_leave_container");
    const employmentStatus = document.getElementById("employment_status");
    const genderInput = document.getElementById("gender");
    
    let validDisplayValue = employeeId.value.replace(/[^0-9]/g, '');
    // Apply format: 00-000
    if (employeeId.value.length > 2) {
        employeeId.value = validDisplayValue.slice(0, 2) + '-' + validDisplayValue.slice(2, 5);
    }
    
    // Update leave credits and containers based on gender
    if (genderInput.value == "Male") {
        leaveContainers("block", "block", "none", "block");      
    } else if (genderInput.value == "Female") {
        leaveContainers("block", "block", "block", "none");  
    } else {
        leaveContainers("none", "none", "none", "none");  
    }


    // Function to set leave credits based on the values
    function leaveCredits(sickValue, vacationValue, maternityValue, paternityValue) {
        document.getElementById("sick_leave").value = sickValue;
        document.getElementById("vacation_leave").value = vacationValue;
        document.getElementById("maternity_leave").value = maternityValue;
        document.getElementById("paternity_leave").value = paternityValue;
    }

    // Function to show/hide leave containers
    function leaveContainers(sickDisplay, vacationDisplay, maternityDisplay, paternityDisplay) {
        sickContainer.style.display = sickDisplay;
        vacationContainer.style.display = vacationDisplay;
        maternityContainer.style.display = maternityDisplay;
        paternityContainer.style.display = paternityDisplay;
    }

    // Event listener for gender change
    genderInput.addEventListener("change", e => {
        const genderValue = e.target.value;
        
        // Update leave credits and containers based on gender
        if (genderValue == "Male") {
            leaveCredits(12, 12, 0, 7);
            leaveContainers("block", "block", "none", "block");      
        } else if (genderValue == "Female") {
            leaveCredits(12, 12, 135, 0);
            leaveContainers("block", "block", "block", "none");  
        } else {
            leaveCredits(0, 0, 0, 0);
            leaveContainers("none", "none", "none", "none");  
        }

        // Update leave credits when gender is changed
        updateLeaveBasedOnEmploymentStatus();  
    });

    // Event listener for employment status change
    employmentStatus.addEventListener("change", e => {
        // Trigger leave credits update based on both employment status and gender
        updateLeaveBasedOnEmploymentStatus();
    });

    // Function to update leave credits based on employment status and gender
    function updateLeaveBasedOnEmploymentStatus() {
        const genderValue = genderInput.value;
        const employmentValue = employmentStatus.value;

        // Determine leave credits based on employment status and gender
        if (employmentValue == "Regular") {
            if (genderValue == "Male") {
                leaveCredits(12, 12, 0, 7);
                leaveContainers("block", "block", "none", "block");      
            } else if (genderValue == "Female") {
                leaveCredits(12, 12, 135, 0);
                leaveContainers("block", "block", "block", "none");  
            }
        } else {
            // If employment is not regular, set all leave credits to 0
            leaveCredits(0, 0, 0, 0);
            leaveContainers("none", "none", "none", "none");
        }
    }

    // Initial update based on the current values of gender and employment status
    updateLeaveBasedOnEmploymentStatus();
           
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function (event) {
            const parent = this.parentElement;

            // Prevent the link's default behavior
            event.preventDefault();

            // Toggle the active class
            parent.classList.toggle('active');

            
        });
    }); 

document.querySelectorAll('input').forEach(input => {
    input.addEventListener('input', function (e) {
        let value = e.target.value;
        // Remove any non-numeric characters
        let validValue = value.replace(/[^0-9]/g, '');
        // / Check if the input field is for first name (or other fields where numbers are not allowed)
        if (input.name === "first_name" || input.name === "middle_name" || input.name === "last_name" || input.name === "emergency_contact_name" || input.name === "skills") {
            const validValue = value.replace(/[^a-zA-Z\s]/g, '');
            // If the value changed (i.e., it had invalid characters), set it to the valid value
            if (value !== validValue) {
                e.target.value = validValue;
            }

            return;
        } 
        // Handle contact number fields (allow only numbers and exactly 11 digits)
        if(input.name === "contact_number" || input.name === "emergency_contact_number") {
            // If the value changed (i.e., it had invalid characters), set it to the valid value
            if (value !== validValue) {
                e.target.value = validValue;
            }

            // Prevent input if the value already has 11 digits
            if (validValue.length >= 11) {
                e.target.value = validValue.slice(0, 11); // Ensure the value is exactly 11 digits
                this.style.borderColor = ''; // Reset the border color
                return; // Stop further typing
            } else {
                // If the value is not exactly 11 digits, apply red border
                this.style.borderColor = 'red';
            }

            return;
        } 
        
        // Handle Employee ID fields (allow only numbers and exactly 5 digits in the format 00-000)
        if(input.name === "employee_id") {
            // Apply format: 00-000
            if (value.length > 2) {
                value = validValue.slice(0, 2) + '-' + validValue.slice(2, 5);
            }

            // Limit the value to 5 characters (including the hyphen)
            if (value.length >= 5) {
                this.style.borderColor = ''; // Reset the border color if valid
                e.target.value = value.slice(0, 6);  // Ensure it is capped at 5 characters
                return; // Stop further typing if it's already 5 characters
            } else {
                // If the value is not 5 characters, apply red border
                this.style.borderColor = 'red';
            }

            // Prevent input if the value already has 5 digits (with the hyphen)
            if (validValue.length >= 5) {
                this.style.borderColor = ''; // Reset the border color
                return; // Stop further typing
            } else {
                // If the value is not exactly 5 digits, apply red border
                this.style.borderColor = 'red';
            }

            e.target.value = value
            return;
        }

        if (!this.checkValidity()) {
            this.style.borderColor = 'red';
        } else {
            this.style.borderColor = '';
        }
    });
});

// $(document).ready( function () {
//     $('#myTable').DataTable();
//   });

</script>
        <?php include('footer.php'); ?>
    </body>
    </html>

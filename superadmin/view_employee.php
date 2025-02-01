<?php include('header.php'); ?>
<?php include('includes/sideBar.php'); ?>
    <?php
    // Database connection
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
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
?>





    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>View Employee</title>
        <!-- <link rel="stylesheet" href="styles.css"> -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    </head>

    <body>

    <?php include('includes/sideBar.php'); ?>
    
        <main class="main-content">
            <section id="edit-employee">
                <h2>View Employee Details</h2>
                <?php if ($employee): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Employee: <?php echo $employee['last_name'] . ', ' . $employee['first_name']; ?></h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                            <div class="form-row">
                <div class="col-md-4">
                <label for="date_of_birth">First Name</label>
                    <input type="text" class="form-control" name="first_name" placeholder="First Name" value="<?php  echo $employee['first_name'] ?>" readonly>
                </div>
                <div class="col-md-4">
                <label for="date_of_birth">Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" placeholder="Middle Name" value="<?php  echo $employee['middle_name'] ?>" readonly>
                </div>
                <div class="col-md-4">
                <label for="date_of_birth">Last Name</label>
                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="<?php  echo $employee['last_name'] ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label for="suffix">Suffix</label>
                    <input type="text" class="form-control" name="suffix" placeholder="e.g., Sr., Jr., III" value="<?php  echo $employee['suffix'] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="gender">Sex</label>
                    <input id="gender" type="text" class="form-control" name="gender" value="<?php  echo $employee['gender'] ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" class="form-control" name="contact_number" placeholder="e.g., +63 912-345-6789" value="<?php  echo $employee['contact_number'] ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="civil_status">Civil Status</label>
                    <input type="text" class="form-control" name="civil_status" placeholder="Civil Status" value="<?php  echo $employee['civil_status'] ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="educational_background">Education Background</label>
                    <input type="text" class="form-control" name="educational_background" placeholder="Education Background" value="<?php  echo $employee['educational_background'] ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" class="form-control" name="date_of_birth" placeholder="Date of Birth" value="<?php  echo $employee['date_of_birth'] ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="position">Position</label>
                    <input type="text" class="form-control" name="position" placeholder="Position" value="<?php  echo $employee['position'] ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="skills">Skills</label>
                    <input type="text" class="form-control" name="skills" placeholder="Skills" value="<?php  echo $employee['skills'] ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="perma_address">Permanent Address</label>
                    <input type="text" class="form-control" name="perma_address" placeholder="Permanent Address" value="<?php  echo $employee['perma_address'] ?>" readonly>
                </div>              
            </div>
            
            <div class="form-row">
                <div class="col-md-6">
                    <label for="date_of_birth">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="Email" value="<?php  echo $employee['email'] ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="department">Department</label>
                    <input type="text" class="form-control" name="department" placeholder="department" value="<?php  echo $employee['department'] ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" class="form-control" name="emergency_contact_name" placeholder="Emergency Contact Name" value="<?php  echo $employee['emergency_contact_name'] ?>"  readonly>
                </div>
                <div class="col-md-6">
                    <label for="emergency_contact_number">Emergency Contact Number</label>
                    <input type="text" class="form-control" name="emergency_contact_number" placeholder="Emergency Contact Number" value="<?php  echo $employee['emergency_contact_number'] ?>" readonly>
                </div>
            </div>

            <div class="form-row">
            <div class="col-md-4">
                <label for="hire_date">Date Hired</label>
                <input type="date" class="form-control" name="hire_date" id="hire_date" placeholder="yyyy-mm-dd" value="<?php  echo $employee['hire_date'] ?>" readonly>
            </div>
            </div>
         
             <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Employment Status</label>
                    <input id="employment_status" type="text" class="form-control" name="employment_status" value="<?php  echo $employee['employment_status'] ?>" readonly>
                </div>
                </div>
      

            <!-- New fields for update_employee -->
            <div class="form-row">

                <div class="col-md-4">
                <label for="date_of_birth">Username</label>
                    <input type="text" class="form-control" name="username" placeholder="Username" value="<?php  echo $employee['username'] ?>" readonly>
                </div>

                <div class="col-md-4">
                <label for="date_of_birth">Employee ID</label>
                    <input type="text" id="employee_id" class="form-control" name="employee_id" placeholder="Employee ID" value="<?php  echo $employee['employee_id'] ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="date_of_birth">SSS Number</label>
                    <input type="text" class="form-control" name="sss_number" placeholder="SSS Number" readonly pattern="\d{2}-\d{7}-\d{1}" title="SSS should be in the format 00-0000000-0" value="<?php  echo $employee['sss_number'] ?>">
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">PHILHEALTH Number</label>
                    <input type="text" class="form-control" name="philhealth_number" placeholder="PhilHealth Number" readonly pattern="\d{2}-\d{9}-\d{1}" title="PhilHealth should be in the format 00-000000000-0" value="<?php  echo $employee['philhealth_number'] ?>">
                </div>
                
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="date_of_birth">TIN Number</label>
                    <input type="text" class="form-control" name="tin_number" placeholder="TIN Number" readonly pattern="\d{3}-\d{3}-\d{3}-\d{3}" title="TIN should be in the format 000-000-000-000" value="<?php  echo $employee['tin_number'] ?>">
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">PAGIBIG Number</label>
                    <input type="text" class="form-control" name="pagibig_number" placeholder="PagIBIG Number" readonly pattern="\d{4}-\d{4}-\d{4}" title="PagIBIG should be in the format 0000-0000-0000" value="<?php  echo $employee['pagibig_number'] ?>">
                </div>
            </div>

            <div class="form-row">
                    <div id="sick_leave_container" class="col-md-6">
                        <label for="sick_leave">Sick Leave</label>
                        <input type="text" id="sick_leave" class="form-control" name="sick_leave" placeholder="Sick Leave" value="<?php  echo $employee['sick_leave'] . ' ' . 'days' ?>" readonly>
                    </div>

                    <div id="vacation_leave_container" class="col-md-6">
                        <label for="vacation_leave">Vacation Credit</label>
                        <input type="text" id="vacation_leave" class="form-control" name="vacation_leave" placeholder="Vacation Credit" value="<?php  echo $employee['vacation_leave'] . ' ' . 'days'?>" readonly>
                    </div>

                    <div id="maternity_leave_container" class="col-md-6">
                        <label for="maternity_leave">Maternity Credit</label>
                        <input type="text" id="maternity_leave" class="form-control" name="maternity_leave" placeholder="Maternity Credit" value="<?php  echo $employee['maternity_leave'] . ' ' . 'days'?>" readonly>
                    </div>

                    <div id="paternity_leave_container" class="col-md-6">
                        <label for="paternity_leave">Paternity Credit</label>
                        <input type="text" id="paternity_leave" class="form-control" name="paternity_leave" placeholder="Paternity Credit" value="<?php  echo $employee['paternity_leave'] . ' ' . 'days'?>" readonly>
                    </div>
                </div>

                                </div>
                                <div class="col-md-6 buttons">
                                    <a href="./file_medical?id=<?php echo $employee['id']?>" class="btn btn-cancel">Medical</a>
                                    <a href="./file_tor?id=<?php echo $employee['id']?>" class="btn btn-cancel">TOR</a>
                                    <a href="./file_police?id=<?php echo $employee['id']?>" class="btn btn-cancel">NBI/Police Clearance</a>
                                    <a href="./file_resume?id=<?php echo $employee['id']?>" class="btn btn-cancel">Resume</a>
                                    <a href="./file_prc?id=<?php echo $employee['id']?>" class="btn btn-cancel">PRC</a>
                                    <a href="./file_201?id=<?php echo $employee['id']?>" class="btn btn-cancel">Others</a>
                                    <a href="./employees" class="btn btn-cancel">Back</a>
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
            const sickContainer = document.getElementById("sick_leave_container");
            const vacationContainer = document.getElementById("vacation_leave_container");
            const maternityContainer = document.getElementById("maternity_leave_container");
            const paternityContainer = document.getElementById("paternity_leave_container");
            const employmentStatus = document.getElementById("employment_status");
            const genderInput = document.getElementById("gender");

            const genderValue = genderInput.value;
            const employmentValue = employmentStatus.value;

            // Determine leave credits based on employment status and gender
            if (employmentValue == "Regular") {
                if (genderValue == "Male") {
                    leaveContainers("block", "block", "none", "block");      
                } else if (genderValue == "Female") {
                    leaveContainers("block", "block", "block", "none");  
                }
            } else {
                // If employment is not regular, set all leave credits to 0
                leaveContainers("none", "none", "none", "none");
            }

                // Function to show/hide leave containers
                function leaveContainers(sickDisplay, vacationDisplay, maternityDisplay, paternityDisplay) {
                    sickContainer.style.display = sickDisplay;
                    vacationContainer.style.display = vacationDisplay;
                    maternityContainer.style.display = maternityDisplay;
                    paternityContainer.style.display = paternityDisplay;
                }
        </script>

<script>
    // Handle Employee ID fields (allow only numbers and exactly 5 digits in the format 00-000)
    const employeeId = document.getElementById("employee_id");
    let value = employeeId.value;
            
    if (value.length > 2) {
        value = value.slice(0, 2) + '-' + value.slice(2, 5);
    }
    
    employeeId.value = value;

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

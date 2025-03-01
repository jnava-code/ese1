<?php // Database connection
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    require '../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../vendor/PHPMailer-6.9.3/PHPMailer-6.9.3/src/Exception.php';
    require '../vendor/PHPMailer-6.9.3/PHPMailer-6.9.3/src/PHPMailer.php';
    require '../vendor/PHPMailer-6.9.3/PHPMailer-6.9.3/src/SMTP.php';

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

    // Add Employee
    if (isset($_POST['add_employee'])) {
        // Retrieve POST values
        $last_name = $_POST['last_name'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $middle_name = $_POST['middle_name'] ?? '';
        $suffix = $_POST['suffix'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $email = $_POST['email'] ?? '';
        $position = $_POST['position'] ?? '';
        $hire_date = $_POST['hire_date'] ?? '';
        $department = $_POST['department'] ?? '';
        $employment_status = $_POST['employment_status'] ?? '';
        $employee_id = str_replace('-', '', $_POST['employee_id'] ?? '');
        $date_of_birth = $_POST['date_of_birth'] ?? '';
        // Create DateTime objects
        $dob = new DateTime($date_of_birth);
        $today = new DateTime('today');

        // Calculate the age
        $age = $dob->diff($today)->y;
        $password = generatePasswordFromBday($date_of_birth);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $contact_number = $_POST['contact_number'] ?? '';
        $perma_address = $_POST['perma_address'] ?? '';
        $civil_status = $_POST['civil_status'] ?? '';
        $sss_number = $_POST['sss_number'] ?? '';
        $philhealth_number = $_POST['philhealth_number'] ?? '';
        $pagibig_number = $_POST['pagibig_number'] ?? '';
        $tin_number = $_POST['tin_number'] ?? '';
        $emergency_contact_name = $_POST['emergency_contact_name'] ?? '';
        $emergency_contact_number = $_POST['emergency_contact_number'] ?? '';
        $educational_background = $_POST['educational_background'] ?? '';
        $skills = $_POST['skills'] ?? '';
        $username = $_POST['username'] ?? '';
        $sick_leave = $_POST['sick_leave'] ?? 0;
        $vacation_leave = $_POST['vacation_leave'] ?? 0;
        $maternity_leave = $gender == 'Female' ? $_POST['maternity_leave'] : 0;
        $paternity_leave = $gender == 'Male' ? $_POST['paternity_leave'] : 0;
    
        // File uploads
        function getFileContent($fieldName) {
            return isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK
                ? file_get_contents($_FILES[$fieldName]['tmp_name'])
                : null;
        }
    
        $file_medical = getFileContent('file_medical');
        $file_tor = getFileContent('file_tor');
        $file_police = getFileContent('file_police');
        $file_resume = getFileContent('file_resume');
        $file_prc = getFileContent('file_prc');
        $file_201 = getFileContent('file_201');
        
        // Check if any file upload failed
        foreach ($_FILES as $key => $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                die("File upload error in $key: " . $file['error']);
            }
        }
        
          // Initialize error message
          $errmsg = '';


        // Check for uniqueness
        function isFieldUnique($conn, $field, $value, $fieldName) {
            $sql = "SELECT $field FROM employees WHERE $field = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $value);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return "$fieldName - '$value' is already in use.<br>";
            }
            return '';
        }
    
        $errmsg .= isFieldUnique($conn, 'employee_id', $employee_id, 'Employee ID');
        $errmsg .= isFieldUnique($conn, 'sss_number', $sss_number, 'SSS Number');
        $errmsg .= isFieldUnique($conn, 'philhealth_number', $philhealth_number, 'PhilHealth Number');
        $errmsg .= isFieldUnique($conn, 'pagibig_number', $pagibig_number, 'Pag-IBIG Number');
        $errmsg .= isFieldUnique($conn, 'tin_number', $tin_number, 'TIN Number');
    
        // Proceed if no errors
        if (empty($errmsg)) {
            $sql = "INSERT INTO employees (
                last_name, first_name, middle_name, suffix, gender, email, position, hire_date, department,
                employment_status, employee_id, password, date_of_birth, age, contact_number, perma_address,
                civil_status, sss_number, philhealth_number, pagibig_number, tin_number, emergency_contact_name,
                emergency_contact_number, educational_background, skills, username, sick_leave, vacation_leave,
                maternity_leave, paternity_leave, medical, tor, nbi_clearance, resume, prc, others
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssssssssssssssssssssssssbbbbbb",
                $last_name, $first_name, $middle_name, $suffix, $gender, $email, $position, $hire_date,
                $department, $employment_status, $employee_id, $hashedPassword, $date_of_birth, $age, $contact_number,
                $perma_address, $civil_status, $sss_number, $philhealth_number, $pagibig_number, $tin_number,
                $emergency_contact_name, $emergency_contact_number, $educational_background, $skills, $username,
                $sick_leave, $vacation_leave, $maternity_leave, $paternity_leave, $null, $null, $null, $null, $null, $null
            );
    

                // Ensure binary files are stored properly
                if ($file_medical !== null) $stmt->send_long_data(30, $file_medical);
                if ($file_tor !== null) $stmt->send_long_data(31, $file_tor);
                if ($file_police !== null) $stmt->send_long_data(32, $file_police);
                if ($file_resume !== null) $stmt->send_long_data(33, $file_resume);
                if ($file_prc !== null) $stmt->send_long_data(34, $file_prc);
                if ($file_201 !== null) $stmt->send_long_data(35, $file_201);            

    
            if ($stmt->execute()) {
                 // Send email using PHPMailer
                 $mail = new PHPMailer(true);

                 try {
                     // SMTP Settings
                     $mail->isSMTP();
                     $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
                     $mail->SMTPAuth = true;
                     $mail->Username = 'rroquero26@gmail.com'; // Your SMTP email
                     $mail->Password = 'plxj aziw yqbo wkbs'; // Use an App Password for Gmail
                     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                     $mail->Port = 587;
                                    
                     // Email Headers
                     $mail->setFrom('no-reply@yourwebsite.com', 'ESE-Tech Industrial Solutions Corporation System'); // Corrected "From" email
                     $mail->addAddress($email); // The recipient's email
                     $mail->Subject = 'Your Account from ESE-Tech Industrial Solutions Corporation System';
 
                     // Prepare the email message
                     $message = "Good day! <br><br> Welcome to ESE-Tech Industrial Solutions Corporation System! Below are your login credentials for the ESE-Tech Human Resource System: <br><br>Your Username is: $username <br>Your Password is: $password <br><br> You may log in using the link below: <br>ESE-Tech-HR-System-Login.com <br><br>If you encounter any issues while logging in, please email us at hrsupport@ese-tech.com. <br><br>Thank you! <br>Best regards, <br>ESE-Tech HR Team <br>hrsupport@ese-tech.com";
 
                     // Set email format to plain text
                     $mail->isHTML(true);
                     $mail->Body = $message;
 
                     // Send the email
                     if ($mail->send()) {
                        $_SESSION['success_message'] = "The employee, $first_name $last_name, has been successfully added.";
                        // Redirect to prevent form resubmission
                        header("Location: " . preg_replace('/\.php$/', '', $_SERVER['REQUEST_URI']));
                        exit();
                     } else {
                        $_SESSION['error_message'] = "An error occurred: " . $stmt->error;
                     }
                 } catch (Exception $e) {
                     $_SESSION['error_message'] = "Mailer Error: " . $mail->ErrorInfo;
                 }
            } else {
                $_SESSION['error_message'] = "An error occurred: " . $stmt->error;
            }
        }
    }
    
    // Display messages if they exist (add this after the POST handling)
    if (isset($_SESSION['success_message'])) {
        echo "<div class='alert alert-success' role='alert'>";
        echo $_SESSION['success_message'];
        echo "</div>";
        unset($_SESSION['success_message']);
    } elseif (isset($_SESSION['error_message'])) {
        echo "<div class='alert alert-danger' role='alert'>";
        echo $_SESSION['error_message'];
        echo "</div>";
        unset($_SESSION['error_message']);
    }

    // Archive Employee (instead of delete)
    if (isset($_GET['delete_id'])) {
        $id = $_GET['delete_id'];
        $sql = "UPDATE employees SET is_archived = 1 WHERE id=?";
        executeQuery($conn, $sql, 'i', [$id]);
    }

    // Fetch Employees
    $sql = "SELECT * FROM employees WHERE is_archived = 0";
    $resultEmployees = mysqli_query($conn, $sql);

    // Capture search input if available
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
}

if (!empty($searchQuery)) {
    // Modify the query to include sorting by `id` in descending order
    $sql .= " AND (LOWER(last_name) LIKE LOWER(?) 
              OR LOWER(first_name) LIKE LOWER(?) 
              OR LOWER(middle_name) LIKE LOWER(?) 
              OR LOWER(email) LIKE LOWER(?) 
              OR LOWER(position) LIKE LOWER(?) 
              OR LOWER(department) LIKE LOWER(?) 
              OR LOWER(employment_status) LIKE LOWER(?) 
              OR LOWER(employee_id) LIKE LOWER(?) 
              OR LOWER(date_of_birth) LIKE LOWER(?) 
              OR LOWER(contact_number) LIKE LOWER(?) 
              OR LOWER(perma_address) LIKE LOWER(?) 
              OR LOWER(civil_status) LIKE LOWER(?) 
              OR LOWER(sss_number) LIKE LOWER(?) 
              OR LOWER(philhealth_number) LIKE LOWER(?) 
              OR LOWER(pagibig_number) LIKE LOWER(?) 
              OR LOWER(tin_number) LIKE LOWER(?) 
              OR LOWER(emergency_contact_name) LIKE LOWER(?) 
              OR LOWER(emergency_contact_number) LIKE LOWER(?) 
              OR LOWER(educational_background) LIKE LOWER(?) 
              OR LOWER(skills) LIKE LOWER(?)
              OR LOWER(username) LIKE LOWER(?))";
    
    // Add the ORDER BY clause to sort by 'id' in descending order
    $sql .= " ORDER BY id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    $searchParam = '%' . $searchQuery . '%';
    
    // Bind the parameters for all the fields
    mysqli_stmt_bind_param($stmt, 'ssssssssssssssssssss', 
        $searchParam, $searchParam, $searchParam, $searchParam, 
        $searchParam, $searchParam, $searchParam, $searchParam, 
        $searchParam, $searchParam, $searchParam, $searchParam, 
        $searchParam, $searchParam, $searchParam, $searchParam, 
        $searchParam, $searchParam, $searchParam, $searchParam,
        $searchParam
    );

    // Execute the query
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // If no search query, just retrieve all employees and order by 'id' descending
    $sql .= " ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
}

// Function to validate employee data
function validateEmployeeData($data) {
    $errors = [];
    
    // Email validation
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // TIN validation (000-000-000-000)
    if (!preg_match("/^\d{3}-\d{3}-\d{3}-\d{3}$/", $data['tin_number'])) {
        $errors[] = "TIN should be in the format 000-000-000-000";
    }

    // PAGIBIG validation (0000-0000-0000)
    if (!preg_match("/^\d{4}-\d{4}-\d{4}$/", $data['pagibig_number'])) {
        $errors[] = "PagIBIG should be in the format 0000-0000-0000";
    }

    // SSS validation (00-0000000-0)
    if (!preg_match("/^\d{2}-\d{7}-\d{1}$/", $data['sss_number'])) {
        $errors[] = "SSS should be in the format 00-0000000-0";
    }

    // PhilHealth validation (00-000000000-0)
    if (!preg_match("/^\d{2}-\d{9}-\d{1}$/", $data['philhealth_number'])) {
        $errors[] = "PhilHealth should be in the format 00-000000000-0";
    }

    return $errors;
}

include('header.php'); 

function generatePasswordFromBday($date_of_birth) {
    // Extract month, day, and year from the date
    $date = DateTime::createFromFormat('Y-m-d', $date_of_birth);
    
    // Format the password as 'mmmddyyyy', where mmm is the 3-letter month, dd is day, and yyyy is year
    $password = $date->format('M') . $date->format('d') . $date->format('Y');
    
    return strtolower($password); // To ensure the month is lowercase
}

?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>

<style>
        /* Dropdown styling */
        .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 120px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown-content a:hover {
        background-color: #f1f1f1;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropdown:hover .export_btn {
        background-color:rgb(33, 59, 173);
    }
    .report_btn {
        display: flex;
        align-items: center;

        margin-bottom: 15px;
    }

    .report_btn button {
        border-radius: 0px;
        cursor: pointer;
    }

    @media print {
        header,
        .main-content h2,
        .form-content,
        .card-body h3,
        .card-body .report_btn,
        .card-body .dataTables_length,
        .card-body .dataTables_filter,
        .card-body .dataTables_info,
        .card-body .dataTables_paginate,
        .actions {
            display: none !important;
        }

        th.sorting::before,
        th.sorting_asc::before,
        th.sorting_desc::before,
        th.sorting::after,
        th.sorting_asc::after,
        th.sorting_desc::after {
            content: none !important;
            display: none !important;
        }

        .card {
            border: none;
            border-radius: none;
            margin-bottom: 0px;
            padding: 0px !important;
            box-shadow: none !important;
        }

        .main-content {
            padding: 0px;
        }

        .status-inactive {
            padding: 0px;
            color: #000;
        }

        table th,
        table tr {
            font-size: 12px;
            padding: 5px;
        }

        table.dataTable thead>tr>th.sorting {
            padding-right: 0px;
        }
    }

    .alert {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        padding: 15px;
        border-radius: 4px;
        opacity: 1;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<main class="main-content">
        <section id="dashboard">
            <h2 class="text-2xl font-bold mb-6">EMPLOYEE MANAGEMENT</h2>
            
            <!-- Add Employee Form -->
            <div class="card form-content">
    <div class="card-header">
        <h3>Add New Employee</h3>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
        <?php if (!empty($errmsg)) echo "<p style='color: red;'>$errmsg</p>"; ?>
        <?php if (!empty($successmsg)) echo "<p style='color: green;'>$successmsg</p>"; ?>

      
            <div class="form-row">
            <div class="col-md-4">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" value="<?php echo !empty($errmsg) ? htmlspecialchars($first_name) : ''; ?>" required 
                        pattern="^[A-Za-z\s-]+$" 
                        title="Only letters, spaces, and hyphens are allowed. Numbers are not allowed.">
                </div>
                <div class="col-md-4">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name" value="<?php echo !empty($errmsg) ? htmlspecialchars($middle_name) : ''; ?>"required
                        pattern="^[A-Za-z\s-]+$" 
                        title="Only letters, spaces, and hyphens are allowed. Numbers are not allowed.">
                    </div>
                <div class="col-md-4">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" value="<?php echo !empty($errmsg) ? htmlspecialchars($last_name) : ''; ?>"required
                        pattern="^[A-Za-z\s-]+$" 
                        title="Only letters, spaces, and hyphens are allowed. Numbers are not allowed.">
                    </div>
                <div class="col-md-4">
                    <label for="suffix">Suffix</label>
                    <input type="text" class="form-control" name="suffix" id="suffix" placeholder="e.g., Sr., Jr., III" list="suffixes">
                    <datalist id="suffixes">
                        <option value="Sr.">
                        <option value="Jr.">
                        <option value="III">
                        <option value="II">
                        <option value="IV">
                        <option value="V">
                    </datalist>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="gender">Sex</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="">Select Sex</option>
                        <option value="Male" <?php echo (!empty($errmsg) && htmlspecialchars($gender) == "Male") ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (!empty($errmsg) && htmlspecialchars($gender) == "Female") ? 'selected' : ''; ?>>Female</option>
                    </select>

                </div>
                <div class="col-md-6">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" class="form-control" name="contact_number" placeholder="e.g., 09123456789" value="<?php echo !empty($errmsg) ? htmlspecialchars($contact_number) : ''; ?>"required>
                </div>
                <div class="col-md-6">
                    <label for="civil_status">Civil Status</label>
                    <select name="civil_status" class="form-control" required>
                        <option value="">Select Civil Status</option>
                        <option value="Single" <?php echo (!empty($errmsg) && htmlspecialchars($civil_status) == "Single") ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo (!empty($errmsg) && htmlspecialchars($civil_status) == "Married") ? 'selected' : ''; ?>>Married</option>
                        <option value="Widowed" <?php echo (!empty($errmsg) && htmlspecialchars($civil_status) == "Widowed") ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Divorced" <?php echo (!empty($errmsg) && htmlspecialchars($civil_status) == "Divorced") ? 'selected' : ''; ?>>Divorced</option>
                        <option value="Separated" <?php echo (!empty($errmsg) && htmlspecialchars($civil_status) == "Separated") ? 'selected' : ''; ?>>Separated</option>
                        <option value="Annulled" <?php echo (!empty($errmsg) && htmlspecialchars($civil_status) == "Annulled") ? 'selected' : ''; ?>>Annulled</option>
                        <option value="Domestic Partnership" <?php echo (!empty($errmsg) && htmlspecialchars($civil_status) == "Domestic Partnership") ? 'selected' : ''; ?>>Domestic Partnership</option>
                        <option value="Legally Separated" <?php echo (!empty($errmsg) && htmlspecialchars($civil_status) == "Legally Separated") ? 'selected' : ''; ?>>Legally Separated</option>
                    </select>

                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="educational_background">Education Background</label>
                    <!-- <input type="text" class="form-control" name="educational_background" placeholder="Education Background" required> -->
                    <select name="educational_background" class="form-control" required>
                        <option value="">Select Education Background</option>
                        <?php 
                            // Ensure $educational_background is set and sanitized
                            $educational_background = isset($educational_background) ? htmlspecialchars($educational_background) : ''; 

                            $options = [
                                "Technical-Vocational Program graduate" => "Technical-Vocational Program Graduate",
                                "College graduate" => "College Graduate",
                                "Master's degree graduate" => "Master's degree Graduate",
                                "Doctorate degree graduate" => "Doctorate degree Graduate"
                            ];

                            // Loop through options and generate the select options
                            foreach ($options as $value => $label) {
                                $selected = ($educational_background == $value) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($value) . "' $selected>$label</option>";
                            }
                        ?>
                    </select>

                </div>
                <div class="col-md-6">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" class="form-control" name="date_of_birth" placeholder="Date of Birth" value="<?php echo !empty($errmsg) ? htmlspecialchars($date_of_birth) : ''; ?>" required
                        max="2005-12-31">
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="position">Position</label>
                    <input type="text" class="form-control" name="position" placeholder="Position" value="<?php echo !empty($errmsg) ? htmlspecialchars($position) : ''; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="skills">Skills</label>
                    <input type="text" class="form-control" name="skills" placeholder="Skills" value="<?php echo !empty($errmsg) ? htmlspecialchars($skills) : ''; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="perma_address">Permanent Address</label>
                    <input type="text" class="form-control" name="perma_address" placeholder="Permanent Address" value="<?php echo !empty($errmsg) ? htmlspecialchars($perma_address) : ''; ?>" required>
                </div>              
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="date_of_birth">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo !empty($errmsg) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="department">Department</label>
                    <select name="department" class="form-control" required>
                        <option value="">Select Department</option>
                        <?php 
                            // Query to get departments that are not archived
                            $deptSelect = "SELECT * FROM departments WHERE is_archived = 0 ORDER BY dept_name ASC";
                            $deptResult = mysqli_query($conn, $deptSelect);

                            if ($deptResult) {
                                // Ensure $department is set and sanitized
                                $department = isset($department) ? htmlspecialchars($department) : ''; 

                                while ($row = mysqli_fetch_assoc($deptResult)) {
                                    // Exclude the department named "Admin"
                                    if ($row['dept_name'] != "Admin") {
                                        // Check if the department matches the selected value (for pre-selection)
                                        $selected = ($department == htmlspecialchars($row['dept_name'])) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo htmlspecialchars($row['dept_name']); ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($row['dept_name']); ?>
                                        </option>
                                        <?php
                                    }
                                }
                            } 
                        ?>
                    </select>


                </div>

            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" class="form-control" name="emergency_contact_name" id="emergency_contact_name" placeholder="Emergency Contact Name" required
                        pattern="^[A-Za-z\s-]+$" 
                        title="Only letters, spaces, and hyphens are allowed. Numbers are not allowed."
                        value="<?php echo !empty($errmsg) ? htmlspecialchars($emergency_contact_name) : ''; ?>">
                    </div>
                <div class="col-md-6">
                    <label for="emergency_contact_number">Emergency Contact Number</label>
                    <input type="text" class="form-control" name="emergency_contact_number" placeholder="e.g., 09123456789" value="<?php echo !empty($errmsg) ? htmlspecialchars($emergency_contact_number) : ''; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-4">
                    <label for="hire_date">Date Hired</label>
                    <input type="date" class="form-control" name="hire_date" id="hire_date" placeholder="yyyy-mm-dd" value="<?php echo !empty($errmsg) ? htmlspecialchars($hire_date) : ''; ?>" required>
                </div>
            </div>
             <br>
            <div class="form-row">
                <div class="col-md-6">
                <label for="hire_date">Employment Status </label>
                    <select id="employment_status" name="employment_status" class="form-control" value="<?php echo !empty($errmsg) ? htmlspecialchars($employment_status) : ''; ?>" required disabled>
                        <option value="" selected hidden>Employment Status</option>
                        
                    </select>
                </div>
            </div>
            </br>

            <!-- New fields for update_employee -->
            <div class="form-row">

                <div class="col-md-4">
                    <label for="date_of_birth">Username</label>
                    <input type="text" class="form-control" name="username" placeholder="Username" value="<?php echo !empty($errmsg) ? htmlspecialchars($username) : ''; ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="date_of_birth">Employee ID</label>
                    <input id="employee_id" type="text" class="form-control" name="employee_id" placeholder="Employee ID" value="<?php echo !empty($errmsg) && isset($employee_id) ? htmlspecialchars($employee_id) : ''; ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6">
                    <label for="sss_number">SSS Number</label>
                    <input type="text" class="form-control" name="sss_number" placeholder="SSS Number" required pattern="\d{2}-\d{7}-\d{1}" title="SSS should be in the format 00-0000000-0" value="<?php echo !empty($errmsg) ? htmlspecialchars($sss_number) : ''; ?>">
                </div>

                <div class="col-md-6">
                    <label for="philhealth_number">PHILHEALTH Number</label>
                    <input type="text" class="form-control" name="philhealth_number" placeholder="PhilHealth Number" required pattern="\d{2}-\d{9}-\d{1}" title="PhilHealth should be in the format 00-000000000-0" value="<?php echo !empty($errmsg) ? htmlspecialchars($philhealth_number) : ''; ?>">
                </div>

            </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label for="tin_number">TIN Number</label>
                        <input type="text" class="form-control" name="tin_number" placeholder="TIN Number" required pattern="\d{3}-\d{3}-\d{3}-\d{3}" title="TIN should be in the format 000-000-000-000" value="<?php echo !empty($errmsg) ? htmlspecialchars($tin_number) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="pagibig_number">PAGIBIG Number</label>
                        <input type="text" class="form-control" name="pagibig_number" placeholder="PagIBIG Number" required pattern="\d{4}-\d{4}-\d{4}" title="PagIBIG should be in the format 0000-0000-0000" value="<?php echo !empty($errmsg) ? htmlspecialchars($pagibig_number) : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div id="sick_leave_container" class="col-md-6">
                        <label for="sick_leave">Sick Leave</label>
                        <input type="text" id="sick_leave" class="form-control" name="sick_leave" placeholder="Sick Leave" value="12" readonly>
                    </div>

                    <div id="vacation_leave_container" class="col-md-6">
                        <label for="vacation_leave">Vacation Credit</label>
                        <input type="text" id="vacation_leave" class="form-control" name="vacation_leave" placeholder="Vacation Credit" value="12" readonly>
                    </div>

                    <div id="maternity_leave_container" class="col-md-6">
                        <label for="maternity_leave">Maternity Credit</label>
                        <input type="text" id="maternity_leave" class="form-control" name="maternity_leave" placeholder="Maternity Credit" value="105" readonly>
                    </div>

                    <div id="paternity_leave_container" class="col-md-6">
                        <label for="paternity_leave">Paternity Credit</label>
                        <input type="text" id="paternity_leave" class="form-control" name="paternity_leave" placeholder="Paternity Credit" value="7" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label for="medical">Medical</label>
                        <input type="file" class="form-control" name="file_medical" accept=".pdf,image/*" required>
                    </div>

                    <div class="col-md-6">
                        <label for="tor">TOR</label>
                        <input type="file" class="form-control" name="file_tor" accept=".pdf,image/*" required>
                    </div>

                    <div class="col-md-6">
                        <label for="nbi_clearance">NBI/Police Clearance</label>
                        <input type="file" class="form-control" name="file_police" accept=".pdf,image/*" required>
                    </div>
                </div>


                <div class="form-row">
                    <div class="col-md-6">
                        <label for="resume">Resume</label>
                        <input type="file" class="form-control" name="file_resume" accept=".pdf,image/*" required>
                    </div>

                    <div class="col-md-6">
                        <label for="prc">PRC License (If applicable)</label>
                        <input type="file" class="form-control" name="file_prc" accept=".pdf,image/*">
                    </div>   

                    <div class="col-md-6">
                        <label for="others">Others: (201 files)</label>
                        <input type="file" class="form-control" name="file_201" accept=".pdf,image/*" required>
                    </div>
                </div>
            

            <!-- Center the Add Employee button -->
             <br>
            <div class="form-row justify-content-center mt-4">
                <div class="col-md-4 text-center">
                    <input type="submit" name="add_employee" class="btn btn-primary" value="Add Employee">
                    </div>
                    </form>
                </div>
            </div>
        </div>

            <div class="card">  
            <div class="card-header">

        <div class="card-body">
            <h3>Employee List</h3>
            <div class="report_btn">
                <!-- Export as Dropdown -->
                <div class="dropdown">
                    <button class="btn export_btn">Export as</button>
                    <div class="dropdown-content">
                        <a href="#" class="pdf_btn">PDF</a>
                        <a href="#" class="excel_btn">Excel</a>
                        <a href="#" class="word_btn">Word</a>
                    </div>
                </div>

        <!-- Print Button -->
        <button class="btn print_btn">Print</button>
    </div>


        <table id="myTable" class="employee-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Employee ID</th>
                    <th>Full Name</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>

                <?php 
                    $counter = 1; // Initialize the counter
                    while ($employee = mysqli_fetch_assoc($resultEmployees)): 
                ?>
                    <tr>
                        <input type="hidden" class="employee_js" value="<?php echo $employee['employee_id']; ?>">
                        <td><?php echo $counter++; ?></td> <!-- Display the counter and increment it -->
                        <td class="employee_id_display"><?php echo $employee['employee_id']; ?></td>
                        <td>
                            <?php
                                // Combine first name, middle name, and last name with proper formatting
                                $full_name = trim($employee['first_name'] . ' ' . $employee['middle_name'] . ' ' . $employee['last_name']);
                                
                                // Optionally, check if there's a suffix and include it in the format
                                if (!empty($employee['suffix'])) {
                                    $full_name .= ', ' . $employee['suffix'];
                                }

                                echo htmlspecialchars($full_name);
                            ?>
                        </td>

                        <td><?php echo htmlspecialchars($employee['position']); ?></td>                     
                        <td><?php echo htmlspecialchars($employee['department']); ?></td>
                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                        <td>
                            <span class="<?php echo $employee['employment_status'] === 'Active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo htmlspecialchars($employee['employment_status']); ?>
                            </span>
                        </td>
                        <td class="actions action-buttons">
                            <a href="./edit_employee?id=<?php echo $employee['id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="./view_employee?id=<?php echo $employee['id']; ?>" class="btn btn-danger">View</a>
                            <a href="?delete_id=<?php echo $employee['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to Archive this employee?');">Archive</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/docx/7.1.0/docx.min.js"></script>

<script>
        const employeeId = document.getElementById("employee_id");
        const employeeIdDisplay = document.querySelectorAll(".employee_id_display");
        const sickContainer = document.getElementById("sick_leave_container");
        const vacationContainer = document.getElementById("vacation_leave_container");
        const maternityContainer = document.getElementById("maternity_leave_container");
        const paternityContainer = document.getElementById("paternity_leave_container");
        const employmentStatus = document.getElementById("employment_status");
        const genderInput = document.getElementById("gender");
        const hireDate = document.getElementById("hire_date");

        function leaveContainers(sickDisplay, vacationDisplay, maternityDisplay, paternityDisplay) {
            sickContainer.style.display = sickDisplay;
            vacationContainer.style.display = vacationDisplay;
            maternityContainer.style.display = maternityDisplay;
            paternityContainer.style.display = paternityDisplay;
        }

        function updateLeaveBasedOnEmploymentStatus() {
            const genderValue = genderInput.value;
            const employmentValue = employmentStatus.value;

            if (employmentValue === "Regular") {
                leaveContainers("block", "block", genderValue === "Female" ? "block" : "none", genderValue === "Male" ? "block" : "none");
            } else {
                leaveContainers("none", "none", "none", "none");
            }
        }

        if (hireDate) {
            hireDate.addEventListener("change", (e) => {
                employmentStatus.innerHTML = '';
                employmentStatus.value = ''; 
                employmentStatus.disabled = false;
                
                const value = e.target.value;
                const hireDateObj = new Date(value);
                const currentDate = new Date();

                const hiredYear = hireDateObj.getFullYear();
                const hiredMonth = hireDateObj.getMonth();
                const currentYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth();

                let monthsOfService = (currentYear - hiredYear) * 12 + (currentMonth - hiredMonth);
                if (monthsOfService < 0) monthsOfService = 0; 
                
                // Append new options based on service months
                if (monthsOfService >= 6) {
                    employmentStatus.insertAdjacentHTML('beforeend', `
                        <option value="Regular">Regular</option>
                        <option value="Probationary">Probationary</option>
                        <option value="Resigned">Resigned</option>
                        <option value="Terminated">Terminated</option>
                    `);
                } else {
                    employmentStatus.insertAdjacentHTML('beforeend', `
                        <option value="Probationary">Probationary</option>
                        <option value="Resigned">Resigned</option>
                        <option value="Terminated">Terminated</option>
                    `);
                }

                let highestValue;
                const employees = document.querySelectorAll('.employee_js');
                if (employees.length > 0) {
                    <?php
                    $all_employees_sql = "SELECT employee_id FROM employees"; 
                    $all_employees_result = mysqli_query($conn, $all_employees_sql);
                    $all_employee_ids = [];
                    while($row = mysqli_fetch_assoc($all_employees_result)) {
                        if(!empty($row['employee_id'])) {
                            $all_employee_ids[] = $row['employee_id'];
                        }
                    }
                    echo "const allEmployeeIds = " . json_encode($all_employee_ids) . ";";
                    ?>

                    const lastThreeDigits = allEmployeeIds.map(id => {
                        return parseInt(id.slice(-3), 10);
                    });

                    highestValue = Math.max(...lastThreeDigits);
                } else {
                    highestValue = 0;
                }

                let employeeLatest = (highestValue + 1).toString().padStart(3, '0');
                const hireYear = value.substring(2, 4);
                const hireMonth = value.substring(5, 7);

                document.getElementById("employee_id").value = `${hireYear}-${employeeLatest}`;

                updateLeaveBasedOnEmploymentStatus();
            });

            if (hireDate.value) {
                hireDate.dispatchEvent(new Event("change"));
            }
        }

        genderInput.addEventListener("change", updateLeaveBasedOnEmploymentStatus);
        employmentStatus.addEventListener("change", updateLeaveBasedOnEmploymentStatus);
        updateLeaveBasedOnEmploymentStatus();
          
        if(employeeIdDisplay) {
        employeeIdDisplay.forEach(display => {
            let validDisplayValue = display.textContent.replace(/[^0-9]/g, '');
            // Apply format: 00-000
            if (display.textContent.length > 2) {
                display.textContent = validDisplayValue.slice(0, 2) + '-' + validDisplayValue.slice(2, 5);
            }
        })
    }

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

$(document).ready( function () {
    $('#myTable').DataTable();
  });

  document.addEventListener("DOMContentLoaded", () => {
    // Select each button directly
    const printBtn = document.querySelector(".print_btn");
    const pdfBtn = document.querySelector(".pdf_btn");
    const excelBtn = document.querySelector(".excel_btn");
    const wordBtn = document.querySelector(".word_btn");

    if (printBtn) {
        printBtn.addEventListener("click", () => {
            window.print();
        });
    }

    if (pdfBtn) {
        pdfBtn.addEventListener("click", () => {
            const element = document.getElementById("myTable");
            const style = document.createElement("style");
            style.innerHTML = `
                header, .main-content h2, .form-content, .card-body h3, .card-body .report_btn,
                .card-body .dataTables_length, .card-body .dataTables_filter, .card-body .dataTables_info,
                .card-body .dataTables_paginate, .actions { display: none !important; }
                table th, table tr { font-size: 12px; padding: 5px; }
            `;
            document.head.appendChild(style);
            const clonedElement = element.cloneNode(true);

            html2pdf()
                .set({
                    margin: 1,
                    filename: "employee_list.pdf",
                    image: { type: "jpeg", quality: 0.98 },
                    html2canvas: { dpi: 192, scale: 2, letterRendering: true, useCORS: true },
                    jsPDF: { unit: "mm", format: "a4", orientation: "portrait" }
                })
                .from(clonedElement)
                .toPdf()
                .save()
                .then(() => document.head.removeChild(style));
        });
    }

    if (excelBtn) {
        excelBtn.addEventListener("click", () => {
            const table = document.getElementById("myTable");
            const rows = [];
            table.querySelectorAll("tr").forEach((row) => {
                const rowData = [];
                row.querySelectorAll("th, td").forEach((cell) => {
                    if (!cell.classList.contains("actions")) {
                        rowData.push(cell.innerText);
                    }
                });
                rows.push(rowData);
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(rows);
            XLSX.utils.book_append_sheet(wb, ws, "Employee List");
            XLSX.writeFile(wb, "employee_list.xlsx");
        });
    }

    if (wordBtn) {
        wordBtn.addEventListener("click", () => {
            const table = document.getElementById("myTable").cloneNode(true);
            table.querySelectorAll("th.actions, td.actions").forEach(cell => cell.remove());

            const htmlContent = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office" 
                    xmlns:w="urn:schemas-microsoft-com:office:word" 
                    xmlns="http://www.w3.org/TR/REC-html40">
                <head>
                    <meta charset="UTF-8">
                    <style>
                        body { margin: 5px; padding: 5px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid black; padding: 5px; text-align: left; }
                    </style>
                </head>
                <body>
                    ${table.outerHTML}
                </body>
                </html>`;

            const blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "employee_list.doc";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});

// Function to reset form
function resetForm() {
    document.querySelector('form').reset();
}

// Reset form on page load if it was a redirect
if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
    resetForm();
}

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Add to your existing form submit handler
document.querySelector('form').addEventListener('submit', function(e) {
    // Your existing validation code...
    
    // If validation passes
    if (!hasErrors) {
        // Submit form and reset
        setTimeout(resetForm, 1000);
    }
});

// Fade out messages after 5 seconds
setTimeout(() => {
    const successMsg = document.querySelector('.alert-success');
    const errorMsg = document.querySelector('.alert-danger');
    
    if (successMsg) {
        successMsg.style.transition = 'opacity 1s';
        successMsg.style.opacity = '0';
        setTimeout(() => successMsg.style.display = 'none', 1000);
    }
    if (errorMsg) {
        errorMsg.style.transition = 'opacity 1s';
        errorMsg.style.opacity = '0';
        setTimeout(() => errorMsg.style.display = 'none', 1000);
    }
}, 5000);
</script>

<?php include('footer.php'); ?>
    
<?php 
include('user_header.php'); 

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech'); // Update with actual credentials

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$username = mysqli_real_escape_string($conn, $_SESSION['username']); 
$sql = "SELECT sick_leave, vacation_leave, maternity_leave, paternity_leave FROM employees WHERE username='$username'";
$result = mysqli_query($conn, $sql);

if($result) {
    $row = mysqli_fetch_assoc($result);
    $sick_leave = $row['sick_leave'];
    $vacation_leave = $row['vacation_leave'];
    $maternity_leave = $row['maternity_leave'];
    $paternity_leave = $row['paternity_leave'];
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Get form input values
    $employee_id = $_SESSION['employee_id']; // Assume employee_id is stored in session
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leave_type = $_POST['leave_type']; // Get the selected leave type
    $noOfDays = $_POST['no_of_days'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']); // Escape special characters

    // Ensure that the employee_id exists in the employees table
    $check_employee_sql = "SELECT employee_id FROM employees WHERE employee_id = '$employee_id'";
    $check_result = mysqli_query($conn, $check_employee_sql);

    if (mysqli_num_rows($check_result) > 0) {
        // Insert the leave request into the database
        $sql = "INSERT INTO leave_applications (employee_id, leave_type, start_date, end_date, reason) 
                VALUES ('$employee_id', '$leave_type', '$start_date', '$end_date', '$reason')";

        if (mysqli_query($conn, $sql)) {
            if($leave_type === "Sick") {
                $update_sql = "UPDATE employees SET sick_leave = sick_leave - $noOfDays WHERE employee_id='$employee_id'";
            } else if ($leave_type === "Vacation") {
                $update_sql = "UPDATE employees SET vacation_leave = vacation_leave - $noOfDays WHERE employee_id='$employee_id'";
            } else if($leave_type === "Maternity") {
                $update_sql = "UPDATE employees SET maternity_leave = maternity_leave - $noOfDays WHERE employee_id='$employee_id'";
            } else {
                $update_sql = "UPDATE employees SET paternity_leave = paternity_leave - $noOfDays WHERE employee_id='$employee_id'";
            }
                      
            if(mysqli_query($conn, $update_sql)) {
                $message = "Leave application submitted successfully!";
                $message_type = "success";
            }
        } else {
            $message = "Error submitting leave application: " . mysqli_error($conn);
            $message_type = "error";
        }
    } else {
        $message = "Error: Employee ID not found in the database.";
        $message_type = "error";
    }

    // Close the connection
    mysqli_close($conn);
}


?>

<nav class="sidebar">
    <ul>
        <li><a href="./user_leave"><i class="fas fa-paper-plane"></i> Leave Application</a></li>
        <li><a href="./user_satisfaction"><i class="fas fa-smile"></i> Satisfaction</a></li>
        <li><a href="./user_profile"><i class="fas fa-user"></i> Manage Profile</a></li>
    </ul>
</nav>
    
<!-- Main Content Area with Styling -->
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        color: #333;
    }
    .main-content {
        padding: 30px;
        max-width: 800px;
        margin: auto;
        background-color: #fff;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }
    h2 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #4CAF50;
        text-align: center;
    }
    form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    label {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    input[type="text"],
    input[type="date"],
    select,
    textarea {
        width: 100%;
        padding: 10px;
        font-size: 14px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    textarea {
        resize: none;
    }
    button {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        background-color: #4CAF50;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #45a049;
    }
    .message {
        text-align: center;
        margin-bottom: 20px;
        padding: 10px;
        border-radius: 5px;
    }
    .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<main class="main-content">
    <section id="dashboard">
        <h2>Leave Application</h2>

        <!-- Display feedback message -->
        <?php if (isset($message)): ?>
            <div class="message <?= $message_type; ?>" id="notification">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <!-- Leave Application Form -->
        <form action="" method="POST">
            <div>
                <label for="sick_leave">Sick Leave Credits:</label>
                <input type="text" id="sick_leave" name="sick_leave" value="<?php echo $sick_leave . ' ' . 'days'; ?>" readonly>
            </div>
            <div>
                <label for="vacation_leave">Vacation Leave Credits:</label>
                <input type="text" id="vacation_leave" name="vacation_leave" value="<?php echo $vacation_leave . ' ' . 'days'; ?>" readonly>
            </div>
            <?php 
                if($_SESSION['gender'] == "Male") {
                    echo '<div>
                            <label for="paternity_leave">Paternity Leave Credits:</label>
                            <input type="text" id="paternity_leave" name="paternity_leave" value="' . $paternity_leave . ' days" readonly>
                        </div>';
                } else {
                    echo '<div>
                            <label for="maternity_leave">Maternity Leave Credits:</label>
                            <input type="text" id="maternity_leave" name="maternity_leave" value="' . $maternity_leave . ' days" readonly>
                        </div>';
                }
            ?>
       
            <div>
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required min="<?= date('Y-m-d'); ?>">
            </div>

            <div>
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required min="<?= date('Y-m-d'); ?>" onchange="validateEndDate()">
            </div>

            <div>
                <label for="no_of_days">No. of Days:</label>
                <input type="text" id="no_of_days" name="no_of_days" placeholder="Number of Days" value="" readonly>
            </div>

            <div>
                <label for="leave_type">Leave Type:</label>
                <select id="leave_type" name="leave_type" required>
                    <option value="Sick">Sick</option>                 
                    <option value="Vacation">Vacation</option>
                    <?php 
                        if($_SESSION['gender'] == "Male") {
                            echo '<option value="Paternity">Paternity</option>';                     
                        } else {
                            echo '<option value="Maternity">Maternity</option>';
                        }     
                    ?>              
                </select>
            </div>

            <div>
                <label for="reason">Reason for Leave:</label>
                <textarea id="reason" name="reason" rows="4" required></textarea>
            </div>

            <button type="submit" name="submit">Submit Leave Request</button>
        </form>
    </section>
</main>

<script>
    // Hide the notification after 5 seconds
    setTimeout(() => {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }, 5000); // 5000ms = 5 seconds

    // Function to ensure end date cannot be earlier than start date
    function validateEndDate() {
    const sick_leave = document.getElementById("sick_leave");
    const vacation_leave = document.getElementById("vacation_leave");
    const maternity_leave = document.getElementById("maternity_leave");
    const paternity_leave = document.getElementById("paternity_leave");
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const numberOfDays =document.getElementById("no_of_days");
    const leaveType =document.getElementById("leave_type");

    const startDate = new Date(startDateInput.value);
    
    if (leaveType) {
    this.selectedLeaveType = "Sick"; // Default leave type
    leaveType.addEventListener("change", e => {
        this.selectedLeaveType = e.target.value;

        // Match selected leave type and remove " days" if present
        if (this.selectedLeaveType === "Sick") {
            this.leaveType = sick_leave.value.replace(" days", "").trim();
        } else if (this.selectedLeaveType === "Vacation") {
            this.leaveType = vacation_leave.value.replace(" days", "").trim();
        } else if (this.selectedLeaveType === "Maternity") { // Corrected typo "Meternity"
            this.leaveType = maternity_leave.value.replace(" days", "").trim();
        } else if (this.selectedLeaveType === "Paternity") {
            this.leaveType = paternity_leave.value.replace(" days", "").trim();
        } else {
            this.leaveType = ""; // Default empty value if no match
        }
    });
}
    
    if (startDateInput.value) {
        endDateInput.setAttribute('min', startDateInput.value);
        
        // Calculate the difference in days if endDate is set
        const endDate = new Date(endDateInput.value);
        
        if (endDateInput.value) {
            const timeDiff = endDate - startDate; // Difference in milliseconds
            const dayDiff = timeDiff / (1000 * 3600 * 24); // Convert to days

            numberOfDays.value = dayDiff;
            // this.leaveType.value = this.leaveType.value - dayDiff;
            // Match selected leave type and remove " days" if present
            if (this.selectedLeaveType === "Sick") {
                this.leaveType = sick_leave.value.replace(" days", "").trim() - dayDiff;
            } else if (this.selectedLeaveType === "Vacation") {
                this.leaveType = vacation_leave.value.replace(" days", "").trim() - dayDiff;
            } else if (this.selectedLeaveType === "Maternity") { // Corrected typo "Meternity"
                this.leaveType = maternity_leave.value.replace(" days", "").trim() - dayDiff;
            } else {
                this.leaveType = paternity_leave.value.replace(" days", "").trim() - dayDiff;
            } 

            this.leaveType.value = `${this.leaveType} days`;
        }
    }
}

    // Initial call to set min end date on page load
    validateEndDate();
</script>

<?php include('user_footer.php'); ?>

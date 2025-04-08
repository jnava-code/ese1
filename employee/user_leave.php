<?php 
ob_start();
include('user_header.php'); 

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech'); // Update with actual credentials

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to reset leave credits
function resetLeaveCredits($conn) {
    $currentYear = date('Y');
    $lastResetYear = isset($_SESSION['last_reset_year']) ? $_SESSION['last_reset_year'] : null;

    if ($lastResetYear !== $currentYear) {
        $sql = "UPDATE employees SET 
                sick_leave = 12, 
                vacation_leave = 12, 
                maternity_leave = 105, 
                paternity_leave = 7";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['last_reset_year'] = $currentYear;
        } else {
            echo "Error resetting leave credits: " . mysqli_error($conn);
        }
    }
}

// Call the function to reset leave credits
resetLeaveCredits($conn);

$username = mysqli_real_escape_string($conn, $_SESSION['username']); 
$sql = "SELECT sick_leave, vacation_leave, maternity_leave, paternity_leave, sick_availed, vacation_availed, maternity_availed, paternity_availed FROM employees WHERE username='$username'";
$result = mysqli_query($conn, $sql);

if($result) {
    $row = mysqli_fetch_assoc($result);
    $sick_leave = $row['sick_leave'];
    $vacation_leave = $row['vacation_leave'];
    $maternity_leave = $row['maternity_leave'];
    $paternity_leave = $row['paternity_leave'];
    $sick_availed = $row['sick_availed'];
    $vacation_availed = $row['vacation_availed'];
    $maternity_availed = $row['maternity_availed'];
    $paternity_availed = $row['paternity_availed'];
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Get form input values
    $employee_id = $_SESSION['employee_id'];
    $file_date = $_POST['file_date'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leave_type = $_POST['leave_type'];
    $noOfDays = $_POST['no_of_days'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    // Ensure that the employee_id exists in the employees table
    $check_employee_sql = "SELECT employee_id FROM employees WHERE employee_id = '$employee_id'";
    $check_result = mysqli_query($conn, $check_employee_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $sql = "INSERT INTO leave_applications (employee_id, leave_type, file_date, start_date, end_date, number_of_days, reason) 
        VALUES ('$employee_id', '$leave_type', '$file_date', '$start_date', '$end_date', $noOfDays, '$reason')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_message'] = "Leave application submitted successfully!";
            // Redirect to prevent form resubmission
            header("Location: " . preg_replace('/\.php$/', '', $_SERVER['REQUEST_URI']));
            exit();
        } else {
            $_SESSION['error_message'] = "Error submitting leave application: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "Error: Employee ID not found in the database.";
    }
}

// Display messages if they exist
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $message_type = "success";
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $message_type = "error";
    unset($_SESSION['error_message']);
}

?>


<?php include('includes/sideBar.php'); ?>
    
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
        margin-top: 20px;
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
        <?php if ($_SESSION['employment_status'] === "Regular") { ?>
            <form action="" method="POST" onsubmit="return validateLeaveCredits()">
                <div>
                    <label for="sick_leave">Sick Leave Credits:</label>
                    <input type="text" id="sick_leave" name="sick_leave" value="<?php echo htmlspecialchars($sick_leave - $sick_availed) . ' days'; ?>" readonly>
                </div>
                <div>
                    <label for="vacation_leave">Vacation Leave Credits:</label>
                    <input type="text" id="vacation_leave" name="vacation_leave" value="<?php echo htmlspecialchars($vacation_leave - $vacation_availed) . ' days'; ?>" readonly>
                </div>
                <?php 
                    if ($_SESSION['gender'] == "Male") {
                        echo '<div>
                                <label for="paternity_leave">Paternity Leave Credits:</label>
                                <input type="text" id="paternity_leave" name="paternity_leave" value="' . htmlspecialchars($paternity_leave - $paternity_availed) . ' days" readonly>
                            </div>';
                    } else {
                        echo '<div>
                                <label for="maternity_leave">Maternity Leave Credits:</label>
                                <input type="text" id="maternity_leave" name="maternity_leave" value="' . htmlspecialchars($maternity_leave - $maternity_availed) . ' days" readonly>
                            </div>';
                    }
                ?>

                <div>
                    <label for="file_date">Date of File:</label>
                    <input type="text" id="file_date" name="file_date" value="" required readonly>
                </div>

                <div>
                    <label for="start_date">Date of Start Leave:</label>
                    <input type="date" id="start_date" name="start_date" required min="<?= date('Y-m-d'); ?>">
                </div>

                <div>
                    <label for="end_date">Date of End  Leave:</label>
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
                            if ($_SESSION['gender'] == "Male") {
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
        <?php } else { ?>
                <h2>Leave application is not available for not Regular Employee.</h2>
        <?php } ?>
    </section>
</main>

<script>
    const fileDateInput = document.getElementById('file_date');
    
    const today = new Date();
    const year = today.getFullYear();
    let day = today.getDate().toString().padStart(2, '0'); // Pads single digit day with leading zero
    let month = (today.getMonth() + 1).toString().padStart(2, '0'); // Pads single digit month with leading zero
    
    if(fileDateInput) fileDateInput.value = `${year}-${month}-${day}`;
    
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
        const numberOfDays = document.getElementById("no_of_days");
        const leaveType = document.getElementById("leave_type");

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
                } else if (this.selectedLeaveType === "Maternity") {
                    this.leaveType = maternity_leave.value.replace(" days", "").trim();
                } else if (this.selectedLeaveType === "Paternity") {
                    this.leaveType = paternity_leave.value.replace(" days", "").trim();
                } else {
                    this.leaveType = ""; // Default empty value if no match
                }
            });
        }
        
        if (startDateInput.value && endDateInput.value) {
            const endDate = new Date(endDateInput.value);

            let leaveDays = 0;
            for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                const day = d.getDay();
                if (day !== 0 && day !== 6) { // Exclude Sundays (0) and Saturdays (6)
                    leaveDays++;
                }
            }

            numberOfDays.value = leaveDays;

            // Update leave credits based on the selected leave type
            if (this.selectedLeaveType === "Sick") {
                this.leaveType = sick_leave.value.replace(" days", "").trim() - leaveDays;
            } else if (this.selectedLeaveType === "Vacation") {
                this.leaveType = vacation_leave.value.replace(" days", "").trim() - leaveDays;
            } else if (this.selectedLeaveType === "Maternity") {
                this.leaveType = maternity_leave.value.replace(" days", "").trim() - leaveDays;
            } else {
                this.leaveType = paternity_leave.value.replace(" days", "").trim() - leaveDays;
            }

            this.leaveType.value = `${this.leaveType} days`;
        }
    }

    // Initial call to set min end date on page load
    validateEndDate();

    // Disable weekends in the date picker
    document.querySelectorAll('input[type="date"]').forEach(dateInput => {
        dateInput.addEventListener('input', function() {
            const date = new Date(this.value);
            if (date.getDay() === 0 || date.getDay() === 6) {
                this.value = '';
                alert('Weekends are not allowed. Please select a weekday.');
            }
        });
    });

    // Add event listeners to update the number of days when start or end date changes
    document.getElementById('start_date').addEventListener('change', function() {
        validateEndDate();
    });

    document.getElementById('end_date').addEventListener('change', function() {
        validateEndDate();
    });

    // Function to validate leave credits before form submission
    function validateLeaveCredits() {
        const leaveType = document.getElementById('leave_type').value;
        const numberOfDays = parseInt(document.getElementById('no_of_days').value, 10);
        let availableCredits = 0;

        if (leaveType === 'Sick') {
            availableCredits = parseInt(document.getElementById('sick_leave').value.replace(' days', ''), 10);
        } else if (leaveType === 'Vacation') {
            availableCredits = parseInt(document.getElementById('vacation_leave').value.replace(' days', ''), 10);
        } else if (leaveType === 'Maternity') {
            availableCredits = parseInt(document.getElementById('maternity_leave').value.replace(' days', ''), 10);
        } else if (leaveType === 'Paternity') {
            availableCredits = parseInt(document.getElementById('paternity_leave').value.replace(' days', ''), 10);
        }

        if (numberOfDays > availableCredits) {
            alert('You do not have enough leave credits for this leave type.');
            return false;
        }

        return true;
    }

    // Function to reset form
    function resetForm() {
        document.querySelector('form').reset();
        if(fileDateInput) fileDateInput.value = `${year}-${month}-${day}`; // Reset the date of file
    }

    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Reset form on page load if it was a redirect
    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
        resetForm();
    }

    // Add to your existing form submit handler
    document.querySelector('form').addEventListener('submit', function(e) {
        // If form is valid
        setTimeout(resetForm, 1000);
    });
</script>
<?php ob_end_flush();?>
<?php include('user_footer.php'); ?>

<?php 
ob_start();
include('user_header.php'); 

$conn = mysqli_connect('localhost', 'root', '', 'esetech'); 

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

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

if (isset($_POST['submit'])) {
    $employee_id = $_SESSION['employee_id'];
    $file_date = $_POST['file_date'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leave_type = $_POST['leave_type'];
    $noOfDays = $_POST['no_of_days'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    $check_employee_sql = "SELECT employee_id FROM employees WHERE employee_id = '$employee_id'";
    $check_result = mysqli_query($conn, $check_employee_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $sql = "INSERT INTO leave_applications (employee_id, leave_type, file_date, start_date, end_date, number_of_days, reason) 
        VALUES ('$employee_id', '$leave_type', '$file_date', '$start_date', '$end_date', $noOfDays, '$reason')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_message'] = "Leave application submitted successfully!";
            header("Location: " . preg_replace('/\.php$/', '', $_SERVER['REQUEST_URI']));
            exit();
        } else {
            $_SESSION['error_message'] = "Error submitting leave application: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "Error: Employee ID not found in the database.";
    }
}

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
    
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        color: #333;
    }
    .main-content {
        padding: 30px;
        max-width: 800px;
        margin: 25px auto;
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

<?php include('includes/sideBar.php'); ?>
<main class="main-content">
    <section id="dashboard">
        <h2>Leave Application</h2>

        <?php if (isset($message)): ?>
            <div class="message <?= $message_type; ?>" id="notification">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['employment_status'] === "Regular") { ?>
            <form action="" method="POST">
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
                    <input type="text" id="file_date" name="file_date" value="" required>
                </div>

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
    let day = today.getDate().toString().padStart(2, '0');
    let month = (today.getMonth() + 1).toString().padStart(2, '0');
    
    if(fileDateInput) fileDateInput.value = `${year}-${month}-${day}`;

    setTimeout(() => {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }, 5000);

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

        let availableLeave = 0;

        if (leaveType.value === "Sick") {
            availableLeave = sick_leave.value.replace(" days", "").trim();
        } else if (leaveType.value === "Vacation") {
            availableLeave = vacation_leave.value.replace(" days", "").trim();
        } else if (leaveType.value === "Maternity") {
            availableLeave = maternity_leave.value.replace(" days", "").trim();
        } else if (leaveType.value === "Paternity") {
            availableLeave = paternity_leave.value.replace(" days", "").trim();
        }

        if (startDateInput.value && endDateInput.value) {
            const endDate = new Date(endDateInput.value);

            let totalDays = 0;
            let currentDate = new Date(startDate);

            while (currentDate <= endDate) {
                const dayOfWeek = currentDate.getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                    totalDays++;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }

            if (totalDays > availableLeave) {
                totalDays = availableLeave;
                numberOfDays.value = totalDays;
                endDateInput.value = calculateEndDate(startDate, totalDays);
            } else {
                numberOfDays.value = totalDays;
            }
        }
    }

    function calculateEndDate(startDate, totalDays) {
        let newEndDate = new Date(startDate);
        let daysAdded = 0;

        while (daysAdded < totalDays) {
            newEndDate.setDate(newEndDate.getDate() + 1);
            const dayOfWeek = newEndDate.getDay();
            if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                daysAdded++;
            }
        }

        return newEndDate.toISOString().split('T')[0];
    }

    validateEndDate();
</script>

<?php ob_end_flush();?>
<?php include('user_footer.php'); ?>

<?php include('header.php'); ?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>

<?php 
    $deptSelect = "SELECT * FROM departments WHERE is_archived = 0 ORDER BY dept_name ASC";
    $deptResult = mysqli_query($conn, $deptSelect);

    if ($deptResult) {
        // Create an empty string to hold the options
        $deptOptions = '';

        while ($row = mysqli_fetch_assoc($deptResult)) {
            // Generate the option and append it to the $options string
            $deptOptions .= '<option value="' . htmlspecialchars($row['dept_name'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['dept_name'], ENT_QUOTES, 'UTF-8') . '</option>';
        }
    }
?>

<?php
// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Get first and last day of the month
$firstDayOfMonth = strtotime("first day of $currentMonth $currentYear");
$lastDayOfMonth = strtotime("last day of $currentMonth $currentYear");

// Get an array of all days in the current month
$daysInMonth = [];
for ($day = 1; $day <= date('t', $firstDayOfMonth); $day++) {
    $daysInMonth[] = date('Y-m-d', strtotime("$currentYear-$currentMonth-$day"));
}

// Fetch attendance data for each employee
$month = date('Y-m'); // Get the current month in 'YYYY-MM' format

if(isset($_POST['search'])) {
    $whereClauses = [];

    // Check if employee name is provided
    if(!empty($_POST['employee_name'])) {
        $employee_name = mysqli_real_escape_string($conn, $_POST['employee_name']);
        $whereClauses[] = "(CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE '%$employee_name%' OR CONCAT(first_name, ' ', last_name) LIKE '%$employee_name%')";
    }    

    // Check if department is selected
    if(!empty($_POST['department'])) {
        $department = mysqli_real_escape_string($conn, $_POST['department']);
        $whereClauses[] = "e.department = '$department'";
    }

    // Check if month is selected
    if(!empty($_POST['month']) && isset($_POST['month'])) {
        $month = (int)$_POST['month'] + 1; // Months in PHP are 0-based, so adding 1
        $whereClauses[] = "MONTH(a.date) = $month";
    }

    // Check if year is selected
    if(!empty($_POST['year'])) {
        $year = mysqli_real_escape_string($conn, $_POST['year']);
        $whereClauses[] = "YEAR(a.date) = $year";
    }

    // Construct the query
    $sql = "
        SELECT 
            CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name,
            e.employee_id,
            e.hire_date, 
            a.date,
            a.clock_in_time,
            a.clock_out_time,
            a.total_hours,
            IFNULL(la.status, 'Present') AS attendance_status
        FROM employees e
        LEFT JOIN attendance a ON e.employee_id = a.employee_id
        LEFT JOIN leave_applications la ON 
            e.employee_id = la.employee_id 
            AND a.date BETWEEN la.start_date AND la.end_date
            AND la.status = 'Approved'
        WHERE " . implode(' AND ', $whereClauses) . "
        ORDER BY e.employee_id, a.date";

        $attendanceResult = mysqli_query($conn, $sql);
        $attendanceData = [];
        while ($row = mysqli_fetch_assoc($attendanceResult)) {
            $attendanceData[$row['employee_id']][] = $row;
        }
}
// else {
//     // Default query for displaying attendance of the current month and year
//     $month = date('Y-m'); // Get the current month in 'YYYY-MM' format
//     $sql = "
//         SELECT 
//             CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name,
//             e.employee_id,
//             a.date,
//             IFNULL(la.status, 'Present') AS attendance_status
//         FROM employees e
//         LEFT JOIN attendance a ON e.employee_id = a.employee_id
//         LEFT JOIN leave_applications la ON e.employee_id = la.employee_id AND a.date BETWEEN la.start_date AND la.end_date
//         WHERE a.date LIKE '$month%'
//         ORDER BY e.employee_id, a.date";
// }

// $attendanceResult = mysqli_query($conn, $sql);
// $attendanceData = [];
// while ($row = mysqli_fetch_assoc($attendanceResult)) {
//     $attendanceData[$row['employee_id']][] = $row;
// }

?>

<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>MONTHLY ATTENDANCE MONITORING</h2>
            <div class="action-buttons">
                <button id="by_employee_btn" class="btn btn-danger by_employee_btn">By Employee</button>
                <button id="by_department_btn" class="btn btn-danger by_department_btn">By Department</button>
            </div>

            <form method="POST" action="">
                <div class="form-row">
                    <div id="employee_container" class="col-md-6 employee_container">
                        <label for="employee_name">Employee Name</label>
                        <input id="employee_value" type="text" class="form-control" name="employee_name" placeholder="Employee Name">
                    </div>
                    <div id="department_container" class="col-md-6 department_container">
                        <label for="department">Department</label>
                        <select id="department_value" name="department">
                            <option value="">Select Department</option>
                            <?php echo $deptOptions?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="month">Month</label>
                        <select name="month" required>
                            <option value="0">January</option>
                            <option value="1">February</option>
                            <option value="2">March</option>
                            <option value="3">April</option>
                            <option value="4">May</option>
                            <option value="5">June</option>
                            <option value="6">July</option>
                            <option value="7">August</option>
                            <option value="8">September</option>
                            <option value="9">October</option>
                            <option value="10">November</option>
                            <option value="11">December</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="year">Year</label>
                        <select name="year" required>
                            <?php
                                $currentYear = date('Y');
                                for ($i = 2020; $i <= $currentYear + 10; $i++) {
                                    echo "<option value='$i'>$i</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <input type="submit" name="search" class="btn" value="Search">
                </form>
            </div>
    <div style="overflow-x:auto;">
        <table id="attendance-table" class="table table-striped">
        <thead>
    <tr>
        <?php
        // Determine the number of days in the selected month and year
        $selectedYear = isset($_POST['year']) ? $_POST['year'] : date('Y');
        $selectedMonth = isset($_POST['month']) ? $_POST['month'] + 1 : date('m'); // Adjust for PHP's 0-based months

        // Get the number of days in the selected month and year
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
        ?>
    </tr>
</thead>

        <?php
        // Get current month and year
        $currentMonth = date('m');
        $currentYear = date('Y');
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        $first_day_of_month = date('N', strtotime("$currentYear-$currentMonth-01")); // 1 (Monday) to 7 (Sunday)

        // Adjust first day to start from Sunday
        $prevMonthDaysToShow = ($first_day_of_month % 7); // Adjust to match Sunday-starting week

        // Determine previous month details
        $prevMonth = $currentMonth - 1;
        $prevYear = $currentYear;
        if ($prevMonth == 0) {
            $prevMonth = 12;
            $prevYear--;
        }
        $days_in_prev_month = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);

        // Calculate the total cells needed to fill the last row
        $total_cells = $prevMonthDaysToShow + $days_in_month;
        $remaining_cells = (7 - ($total_cells % 7)) % 7;

        // Get today's date
        $today = date('Y-m-d');

        // Start outputting table
        ?>
        <tbody>
        <?php if (empty($attendanceData)): ?>
            <tr>
                <td style="text-align: center;">No attendance records found for the selected criteria.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($attendanceData as $employee_id => $attendance): ?>
    <?php
    // Initialize counters for statuses
    $absent_count = 0;
    $present_count = 0;
    $late_count = 0;
    $leave_count = 0;

    // Get the hire_date for the current employee
    $hire_date = $attendance[0]['hire_date']; // Assuming all rows for an employee have the same hire date
    $hire_date_timestamp = strtotime($hire_date); // Convert hire_date to timestamp for comparison

    // Start rendering the table for each employee
    echo "<table border='1'>";
    echo "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";
    echo "<tr>";

    // Fill previous month's last few days
    for ($i = $prevMonthDaysToShow; $i > 0; $i--) {
        $date = str_pad($days_in_prev_month - $i + 1, 2, '0', STR_PAD_LEFT);
        echo "<td class='prev-month'>$date</td>";
    }

    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = "$currentYear-$currentMonth-" . str_pad($day, 2, '0', STR_PAD_LEFT);
        $date_timestamp = strtotime($date); // Convert the current day to timestamp for comparison

        // Default values
        $status = 'A'; // Default Absent
        $clock_in_time = '-';
        $clock_out_time = '-';
        $total_hours = '-';

        // Get the day of the week (1 = Monday, 7 = Sunday)
        $dayOfWeek = date('N', strtotime($date)); 

        // If the current date is before the hire_date, show the date but no attendance data
        if ($date_timestamp < $hire_date_timestamp) {
            echo "<td class='before-hire-date'>$day</td>"; // Show the day but no status
        } else {
            // Skip Saturday and Sunday by leaving the status empty
            if ($dayOfWeek == 6 || $dayOfWeek == 7) {
                // Display empty cell for Saturday and Sunday
                echo "<td class='weekend'>$day</td>";
            } else {
                // Check if the date is today or a future date
                $status_display = '';
                
                if ($date > $today) {
                    // If the date is in the future, leave it blank or display "No status yet"
                    $status_display = "";
                } else {
                    // If the date is today or in the past, fetch the status
                    foreach ($attendance as $record) {
                        if ($record['date'] == $date) {
                            $status = htmlspecialchars(substr($record['attendance_status'], 0, 1), ENT_QUOTES, 'UTF-8');
                            $clock_in_time = $record['clock_in_time'] ? htmlspecialchars($record['clock_in_time'], ENT_QUOTES, 'UTF-8') : '-';
                            $clock_out_time = $record['clock_out_time'] ? htmlspecialchars($record['clock_out_time'], ENT_QUOTES, 'UTF-8') : '-';
                            $total_hours = $record['total_hours'] ? htmlspecialchars($record['total_hours'], ENT_QUOTES, 'UTF-8') : '-';
                            break;
                        }
                    }

                    // Check if employee is on approved leave
                    $leave_query = "SELECT leave_type, reason FROM leave_applications WHERE employee_id = ? AND status = 'Approved' AND ? BETWEEN start_date AND end_date";
                    $stmt = $conn->prepare($leave_query);
                    $stmt->bind_param("ss", $employee_id, $date);
                    $stmt->execute();
                    $leave_result = $stmt->get_result();

                    if ($leave = $leave_result->fetch_assoc()) {
                        $status_display = "On Leave <br> <strong>Type:</strong> " . htmlspecialchars($leave['leave_type'], ENT_QUOTES, 'UTF-8') . "<br> <strong>Reason:</strong> " . htmlspecialchars($leave['reason'], ENT_QUOTES, 'UTF-8');
                        $status_color = "#74c0fc";
                        $leave_count++;
                    } elseif ($status == "A") {
                        $status_display = "Absent";
                        $status_color = "#ff8787";
                        $absent_count++;
                    } elseif ($status == "P") {
                        $status_display = "Present";
                        $status_color = "#69db7c";    
                        $present_count++;
                    } elseif ($status == "L") {
                        $status_display = "Late";
                        $status_color = "#f0f0f0";
                        $late_count++;
                    } else {
                        $status_display = "N/A";
                    }
                }

                // Output the table cell for the current day
                echo "<td><strong style='color: $status_color;'>$day</strong><br>";
                echo $status_display == "" ? "" : "<strong>Status:</strong> $status_display <br>";
                if ($clock_in_time != '-') {
                    echo "<strong>In:</strong> $clock_in_time<br>";
                    echo "<strong>Out:</strong> $clock_out_time<br>";
                    echo "<strong>Total hours:</strong> $total_hours<br>";
                }
                echo "</td>";
            }
        }

        // Break to a new row after Saturday (7th day)
        if (date('N', strtotime($date)) == 6) {
            echo "</tr><tr>";
        }
    }

    // Fill next month's first few days to complete the last week
    for ($i = 1; $i <= $remaining_cells; $i++) {
        echo "<td class='next-month'>$i</td>";
    }

    echo "</tr>";
    echo "</table>";
    ?>

    <div class='count-totals-container'>
        <div class='count-totals'>
            <p>Absent: <?php echo $absent_count; ?></p>
            <p>Present: <?php echo $present_count; ?></p>
            <p>Late: <?php echo $late_count; ?></p>
            <p>Leave: <?php echo $leave_count . " days"; ?></p>
        </div>
    </div>
<?php endforeach; ?>


        <?php endif; ?>
        </tbody>
        </table>
    </div>
</div>
            </div>

            <script>
                const employeeIdDisplay = document.querySelectorAll(".employee_id_display");
                const employeeValue = document.getElementById("employee_value");
                const departmentValue = document.getElementById("department_value");
                const employeeBtn = document.getElementById("by_employee_btn");
                const deptBtn = document.getElementById("by_department_btn");
                const empContainer = document.getElementById("employee_container");
                const deptContaienr = document.getElementById("department_container");
            
                if(employeeIdDisplay) {
                    employeeIdDisplay.forEach(display => {
                        let validDisplayValue = display.textContent.replace(/[^0-9]/g, '');
                        // Apply format: 00-000
                        if (display.textContent.length > 2) {
                            display.textContent = validDisplayValue.slice(0, 2) + '-' + validDisplayValue.slice(2, 5);
                        }
                    })
                }

                empContainer.classList.add("show");

                employeeBtn.addEventListener("click", e => {
                    e.preventDefault();
                    empContainer.classList.add("show");
                    deptContaienr.classList.remove("show");
                    departmentValue.value = "";
                });

                deptBtn.addEventListener("click", e => {
                    e.preventDefault();
                    empContainer.classList.remove("show");
                    deptContaienr.classList.add("show");
                    employeeValue.value = "";
                });
            </script>
            </main>
<style>
.count-totals-container {
    display: flex;
    justify-content: end;
}
.count-totals {
    display: flex;
    gap: 25px;
}

.employee_container,
.department_container {
    display: none;
}

.employee_container.show,
.department_container.show {
    display: flex;
    flex-direction: column;
}
#dashboard .action-buttons {
    margin-bottom: 15px;
}
/* Employee List Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background-color: #fff; /* Ensure the table background matches the overall style */
}

table th, table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    font-size: 14px;  /* Adjusted for better readability */
}

table th {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #333;  /* Dark text for better contrast */
}

table td {
    color: #666;  /* Lighter text for data rows */
}

table tr:nth-child(even) {
    background-color: #f9f9f9;  /* Alternating row colors for clarity */
}

table tr:hover {
    background-color: #f1f1f1;  /* Light hover effect for rows */
}

/* Add responsiveness */
@media (max-width: 768px) {
    table th, table td {
        padding: 8px;  /* Adjust padding for smaller screens */
        font-size: 12px; /* Make text smaller */
    }
}

@media (max-width: 480px) {
    table th, table td {
        padding: 6px;  /* Even smaller padding for very small screens */
        font-size: 10px;  /* Even smaller font size */
    }
}

</style>

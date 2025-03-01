<?php include('header.php'); ?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>

<?php
// Database connection
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "esetech";  

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$days_in_month = 0;
// Handle the form submission
if (isset($_POST['search'])) {
    // Get the selected value from the employee_name dropdown
    $selectedValue = $_POST['employee_name'];
    list($employee_id, $employee_name) = explode(',', $selectedValue);
    $employee_id = intval($employee_id); // Sanitize the employee ID

    // Separate the month-year value (e.g., 11-2025)
    $monthYear = $_POST['month-year'];
    list($month, $year) = explode('-', $monthYear);  // Separate into month and year

    $attendance_data = [];
    // SQL to fetch employee details
    $sql_employee = "SELECT employee_id, department, position FROM employees WHERE id = ? LIMIT 1";
    if ($stmt = mysqli_prepare($conn, $sql_employee)) {
        mysqli_stmt_bind_param($stmt, "i", $employee_id);
        mysqli_stmt_execute($stmt);
        $result_employee = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result_employee)) {
            // Fetch department and position
            $official_employee_id = $row['employee_id'];
            $department = $row['department'];
            $position = $row['position'];
        }
        mysqli_stmt_close($stmt);
    }

    // Loop through all days of the month
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    // Fetch attendance for the specified month-year
    $attendance_data = [];
    $sql_attendance = "
    SELECT 
        a.employee_id, 
        a.date, 
        a.clock_in_time, 
        a.clock_out_time, 
        a.total_hours, 
        a.status,
        COALESCE(la.leave_type, '') as leave_type,
        la.start_date,
        la.end_date,
        la.status as leave_status
    FROM attendance a
    LEFT JOIN leave_applications la 
        ON la.employee_id = a.employee_id 
        AND a.date BETWEEN la.start_date AND la.end_date
        AND la.status = 'Approved'
    WHERE a.employee_id = ? 
    AND YEAR(a.date) = ? 
    AND MONTH(a.date) = ?";

    if ($stmt_attendance = mysqli_prepare($conn, $sql_attendance)) {
        // Bind parameters for the SQL statement
        mysqli_stmt_bind_param($stmt_attendance, "iii", $official_employee_id, $year, $month);
        mysqli_stmt_execute($stmt_attendance);
        $result_attendance = mysqli_stmt_get_result($stmt_attendance);

        // Store the attendance data in an array
        while ($attendance_row = mysqli_fetch_assoc($result_attendance)) {
            $attendance_date = $attendance_row['date'];
            $day = date("d", strtotime($attendance_date)); // Get the day from the date
            $attendance_data[$day] = $attendance_row;  // Store the attendance record for that day
        }
        mysqli_stmt_close($stmt_attendance);
    }
}

function convertTo12HourFormat($time) {
    return date("g:i A", strtotime($time));  // Adds AM/PM
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <style>
        .attendance-report-content {
            padding: 25px 50px;
        }

        table thead th,
        table tbody td {
            border: 1px solid #000 !important;
        }

        .employees-and-date {
            display: flex;
            gap: 5px;
            flex-direction: column;
        }

        .department,
        .position,
        .button,
        .employees,
        .month-and-year {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .department label,
        .position label,
        .employees label,
        .month-and-year label {
            width: 250px;
        }

        .button label {
            width: 212px;
        }

        button[type="button"] {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        button[type="button"]:hover {
            background-color: #0056b3;
        }

        input {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        
        @media print {
            body * {
                font-size: 10px;
            }

            table thead th,
            table tbody td {
                padding: 6px !important;
            }

            button,
            header {
                display: none;
            }

            .attendance-report-content {
                padding: 10px;
            }

            input,
            select {
                padding: 0px;
                border: none;
            }

            .department label,
            .position label,
            .employees label,
            .button label {
                width: fit-content;
                font-weight: 600;
            }

            .month-and-year label {
                width: 109px;
                font-weight: 600;
            }

            select {
                -webkit-appearance: none; /* For Safari */
                -moz-appearance: none; /* For Firefox */
                appearance: none; /* Standard syntax */
                padding-right: 20px; /* Give space for custom arrow */
            }

            input {
                color: #000;
            }

            /* Hide elements not needed in print */
            .searchBtn,
            .printBtn,
            header,
            footer {
                display: none !important;
            }

            /* Ensure table fits on page */
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }

            /* Add page break settings */
            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            /* Enhance table visibility for print */
            table thead th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            /* Add title for print */
            .attendance-report-content::before {
                content: 'Attendance Report';
                display: block;
                text-align: center;
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 20px;
            }

            /* Format employee info for print */
            .employees-and-date {
                margin-bottom: 20px;
            }

            /* Remove input styling in print */
            input, select {
                border: none;
                background: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
            }

            /* Ensure text is black in print */
            * {
                color: black !important;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="attendance-report-content">
        <form method="POST">
            <div class="employees-and-date">
                <div class="employees">
                    <label for="">Name: </label>
                    <select name="employee_name">
                        <?php 
                            $sql_employees = "
                            SELECT 
                                id,
                                first_name,
                                middle_name,
                                last_name
                            FROM employees";
                        
                            $result_employees = mysqli_query($conn, $sql_employees);

                            if ($result_employees): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_employees)): ?>
                                <?php
                                // Constructing the full name
                                $fullname = htmlspecialchars($row['middle_name']) == '' 
                                    ? htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) 
                                    : htmlspecialchars($row['first_name']) . ' ' .  htmlspecialchars($row['middle_name']) . ' ' . htmlspecialchars($row['last_name']);
                                $value = $row['id'] . ',' . $fullname;
                                ?>
                                <option value="<?php echo $value; ?>" <?php echo isset($selectedValue) && $selectedValue == $value ? 'selected' : ''; ?>>
                                    <?php echo $fullname; ?>
                                </option>
                            <?php endwhile; ?>
                            <?php endif; ?>
                    </select>
                </div>

                <div class="department">
                    <label for="">Department:</label>
                    <input type="text" value="<?php echo !empty($department) ? $department : ''; ?>" disabled>
                </div>
                <div class="position">
                    <label for="">Position:</label>
                    <input type="text" value="<?php echo !empty($position) ? $position : ''; ?>" disabled>
                </div>

                <div class="month-and-year">
                    <label for="month-year">Month and Year:</label>
                    <select name="month-year" id="month-year">
                        <?php
                        // Get current year and month
                        $currentYear = date("Y");
                        $currentMonth = date("m");

                        // Start year from 2025
                        $startYear = 2025;

                        // Loop through years from 2025 to current year
                        for ($y = $startYear; $y <= $currentYear; $y++) {
                            // Loop through months
                            for ($m = 1; $m <= 12; $m++) {
                                // Skip future dates
                                if (($y == $currentYear && $m > $currentMonth) || $y > $currentYear) {
                                    continue;
                                }

                                // Format the month to two digits
                                $monthNumber = str_pad($m, 2, "0", STR_PAD_LEFT);
                                
                                // Format the month-year value
                                $monthYear = $monthNumber . "-" . $y;
                                
                                // Get month name for display
                                $monthName = date("F", mktime(0, 0, 0, $m, 1, $y));
                                
                                // Check if this month-year should be selected
                                $selected = '';
                                if (isset($_POST['month-year']) && $_POST['month-year'] == $monthYear) {
                                    $selected = 'selected';
                                }

                                echo "<option value='$monthYear' $selected>$monthName $y</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="button">
                    <label for=""></label>
                    <button class="searchBtn" name="search" type="submit">Search</button>
                    <button class="printBtn" type="button" onclick="printReport()">Print</button>
                </div>
            </div>
        </form>

<!-- Attendance Table -->
<table>
    <thead>
        <tr>
            <th>Day</th>
            <th>Arrival</th>
            <th>Departure</th>
            <th>Hours</th>
            <th>Total Regular Hours</th>
            <th>Total Hours</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
<?php
if (!empty($month) && !empty($year)) {
    for ($day = 1; $day <= $days_in_month; $day++) {
        // Generate the correct date for the current month and year
        $date_string = sprintf("%04d-%02d-%02d", intval($year), intval($month), $day);
        $current_date = new DateTime($date_string); // Fixed: Using DateTime for accurate comparison
        $today_date = new DateTime('today'); // Fixed: Ensures we compare only the date

        // Get the day name
        $day_name = $current_date->format('l');
        $is_future_date = $current_date > $today_date; // Fixed: Now future dates will be true
        $is_weekend = ($day_name === 'Saturday' || $day_name === 'Sunday');

        echo "<tr><td>$day</td>"; // Display the day

        if ($is_future_date) {
            // Future dates (weekdays & weekends) should be empty
            echo "<td></td><td></td><td></td><td></td><td></td><td></td>";
        } else {
            if ($is_weekend) {
                // If it's a past weekend, mark as "Day Off"
                echo "<td></td><td></td><td></td><td></td><td></td><td>Day Off</td>";
            } else {
                // Process attendance for past weekdays
                $day_key = sprintf("%02d", $day);
                $attendance_for_day = $attendance_data[$day_key] ?? null;

                if ($attendance_for_day) {
                    // Display attendance
                    $clock_in_time = $attendance_for_day['clock_in_time'];
                    $clock_out_time = $attendance_for_day['clock_out_time'];
                    $total_hours = round($attendance_for_day['total_hours']);
                    $regular_hours = 8;
                    $overtime_hours = max(0, $total_hours - $regular_hours);
                    $status = $attendance_for_day['status'];

                    // Convert times to 12-hour format
                    $clock_in_time_12hr = convertTo12HourFormat($clock_in_time);
                    $clock_out_time_12hr = convertTo12HourFormat($clock_out_time);

                    echo "<td>$clock_in_time_12hr</td>";
                    echo "<td>$clock_out_time_12hr</td>";
                    echo "<td>$total_hours</td>";
                    echo "<td>$regular_hours</td>";
                    echo "<td>$overtime_hours</td>"; 
                    echo "<td>$status</td>";
                } else {
                    // Check for approved leave
                    $leave_sql = "SELECT leave_type FROM leave_applications 
                                 WHERE employee_id = ? 
                                 AND ? BETWEEN start_date AND end_date 
                                 AND status = 'Approved'
                                 LIMIT 1";

                    if ($stmt_leave = mysqli_prepare($conn, $leave_sql)) {
                        mysqli_stmt_bind_param($stmt_leave, "is", $official_employee_id, $date_string);
                        mysqli_stmt_execute($stmt_leave);
                        $result_leave = mysqli_stmt_get_result($stmt_leave);

                        if ($leave_row = mysqli_fetch_assoc($result_leave)) {
                            echo "<td></td><td></td><td></td><td></td><td></td><td>On Leave</td>";
                        } else {
                            // Mark as absent if it's a past weekday with no attendance
                            echo "<td></td><td></td><td></td><td></td><td></td><td>Absent</td>";
                        }
                        mysqli_stmt_close($stmt_leave);
                    }
                }
            }
        }
        echo "</tr>";
    }
}
?>
</tbody>



</table>

<?php
if (!empty($month) && !empty($year)) {
    // Initialize counters
    $total_overall_hours = 0;
    $total_regular_hours = 0;
    $total_overtime_hours = 0;
    $days_present = 0;
    $days_absent = 0;
    $days_late = 0;
    $days_on_leave = 0;  // Add this counter

    // Calculate totals
    for ($day = 1; $day <= $days_in_month; $day++) {
        $current_date = date_create(sprintf("%04d-%02d-%02d", intval($year), intval($month), $day));
        $day_name = date_format($current_date, 'l');
        $is_weekend = ($day_name === 'Saturday' || $day_name === 'Sunday');
        $is_future_date = $current_date > date_create('today');

        if (!$is_weekend) {
            $attendance_for_day = isset($attendance_data[$day]) ? $attendance_data[$day] : null;
            
            // First check for approved leaves
            $current_date_str = sprintf("%04d-%02d-%02d", intval($year), intval($month), $day);
            $leave_sql = "SELECT 1 FROM leave_applications 
                         WHERE employee_id = ? 
                         AND ? BETWEEN start_date AND end_date 
                         AND status = 'Approved'
                         LIMIT 1";
            
            $is_on_leave = false;
            if ($stmt_leave = mysqli_prepare($conn, $leave_sql)) {
                mysqli_stmt_bind_param($stmt_leave, "is", $official_employee_id, $current_date_str);
                mysqli_stmt_execute($stmt_leave);
                $result_leave = mysqli_stmt_get_result($stmt_leave);
                if (mysqli_fetch_row($result_leave)) {
                    $days_on_leave++;
                    $is_on_leave = true;
                }
                mysqli_stmt_close($stmt_leave);
            }

            if ($attendance_for_day && !$is_future_date) {
                $days_present++;
                $total_overall_hours += round($attendance_for_day['total_hours']);
                $regular_hours = 8;
                $total_regular_hours += $regular_hours;
                $total_overtime_hours += max(0, round($attendance_for_day['total_hours']) - $regular_hours);
                
                if ($attendance_for_day['status'] === 'Late') {
                    $days_late++;
                }
            } elseif (!$is_on_leave && !$is_future_date) {
                // Only count as absent if not on leave and not a future date
                $days_absent++;
            }
        } else {
            
        }
    }
?>

<div style="margin-top: 20px;">
    <table style="width: 50%; border-collapse: collapse; float: left;">
        <tr>
            <td style="width: 33%; text-align: center; padding: 5px; font-size: 11px;">
                Total of Overall Hours
            </td>
            <td style="width: 33%; text-align: center; padding: 5px; font-size: 11px;">
                Regular Hours 
            </td>
            <td style="width: 33%; text-align: center; padding: 5px; font-size: 11px;">
                Total Hours of Over Time
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid black; text-align: center; padding: 5px;">
                <?php echo $total_overall_hours; ?>
            </td>
            <td style="border: 1px solid black; text-align: center; padding: 5px;">
                <?php echo $total_regular_hours; ?>
            </td>
            <td style="border: 1px solid black; text-align: center; padding: 5px;">
                <?php echo $total_overtime_hours; ?>
            </td>
        </tr>
    </table>

    <div style="float: right;">
        <table style="border-collapse: collapse;">
            <tr>
                <td style="padding: 5px; text-align: right;">Number of Days Present:</td>
                <td style="border: 1px solid black; width: 40px; text-align: center; padding: 5px;">
                    <?php echo $days_present; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: right;">Number of Days Absent:</td>
                <td style="border: 1px solid black; width: 40px; text-align: center; padding: 5px;">
                    <?php echo $days_absent; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: right;">Number of Days on Leave:</td>
                <td style="border: 1px solid black; width: 40px; text-align: center; padding: 5px;">
                    <?php echo $days_on_leave; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: right;">Number of Late:</td>
                <td style="border: 1px solid black; width: 40px; text-align: center; padding: 5px;">
                    <?php echo $days_late; ?>
                </td>
            </tr>
        </table>
    </div>
    <!-- Clear the floats -->
    <div style="clear: both;"></div>
</div>

<?php } ?>

    </div>

    <?php include('footer.php'); ?>

    <script>
    function printReport() {
        // Hide the buttons before printing
        const buttons = document.querySelectorAll('.button');
        buttons.forEach(button => button.style.display = 'none');

        // Remove the header and footer for printing
        const header = document.querySelector('header');
        const footer = document.querySelector('footer');
        if (header) header.style.display = 'none';
        if (footer) footer.style.display = 'none';

        // Print the document
        window.print();

        // Restore the elements after printing
        buttons.forEach(button => button.style.display = 'block');
        if (header) header.style.display = 'block';
        if (footer) footer.style.display = 'block';
    }

    // Add event listener for when user cancels print
    window.onafterprint = function() {
        // Restore the elements if print is cancelled
        const buttons = document.querySelectorAll('.button');
        const header = document.querySelector('header');
        const footer = document.querySelector('footer');
        
        buttons.forEach(button => button.style.display = 'block');
        if (header) header.style.display = 'block';
        if (footer) footer.style.display = 'block';
    };
    </script>
</body>
</html>

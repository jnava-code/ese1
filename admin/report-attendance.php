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
    $sql_attendance = "SELECT * FROM attendance WHERE employee_id = ? AND YEAR(date) = ? AND MONTH(date) = ?";
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

// Function to convert 24-hour time to 12-hour format with AM/PM
function convertTo12HourFormat($time) {
    $formatted_time = date("g:i:s a", strtotime($time));
    return $formatted_time;
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
                font-size: 12px;
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

                        // Start year from 2024
                        $startYear = 2024;

                        // Loop through years from 2024 to current year
                        for ($year = $startYear; $year <= $currentYear; $year++) {
                            // Loop through months
                            for ($month = 1; $month <= 12; $month++) {
                                // Skip months that are ahead of the current month if it's the current year
                                if ($year == $currentYear && $month > $currentMonth) {
                                    break;
                                }

                                // Get month name (e.g., January, February)
                                $monthName = date("F", strtotime("$year-$month-01"));

                                // Format the month to two digits
                                $monthNumber = str_pad($month, 2, "0", STR_PAD_LEFT);

                                // Format the month-year as "Month YYYY"
                                $monthYear = $monthNumber . "-" . $year;

                                // Check if this month-year should be selected
                                $selectedMonthYear = (isset($_POST['month-year']) && $_POST['month-year'] == $monthYear) ? 'selected' : '';

                                echo "<option value='$monthYear' $selectedMonthYear>$monthName $year</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="button">
                    <label for=""></label>
                    <button class="searchBtn" name="search" type="submit">Search</button>
                    <button class="printBtn" type="submit">Print</button>
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
                // Loop through all days in the month
                for ($day = 1; $day <= $days_in_month; $day++) {
                    // Check if there's attendance for the current day
                    $attendance_for_day = isset($attendance_data[$day]) ? $attendance_data[$day] : null;

                    if ($attendance_for_day) {
                        // If attendance exists for the day, display the attendance data
                        $attendance_date = $attendance_for_day['date'];
                        $clock_in_time = $attendance_for_day['clock_in_time'];
                        $clock_out_time = $attendance_for_day['clock_out_time'];
                        $total_hours = $attendance_for_day['total_hours'];
                        $status = $attendance_for_day['status'];

                        // Convert times to 12-hour format with AM/PM
                        $clock_in_time_12hr = convertTo12HourFormat($clock_in_time);
                        $clock_out_time_12hr = convertTo12HourFormat($clock_out_time);

                        // Calculate overtime hours (if any)
                        $regular_hours = 8; // Regular working hours
                        $overtime_hours = 0;

                        if ($total_hours > $regular_hours) {
                            $overtime_hours = $total_hours - $regular_hours;
                            $overtime_hours = floor($overtime_hours); // No decimals
                        }
                    } else {
                        // If no attendance, set values to empty
                        $attendance_date = '';
                        $clock_in_time_12hr = '';
                        $clock_out_time_12hr = '';
                        $total_hours = '';
                        $status = 'Absent';
                        $overtime_hours = '';
                        $regular_hours = ''; // Empty for no attendance
                    }

                    // Display the row for the current day
                    echo "<tr>";
                    echo "<td>$day</td>";
                    echo "<td>$clock_in_time_12hr</td>";
                    echo "<td>$clock_out_time_12hr</td>";
                    echo "<td>$total_hours</td>";
                    echo "<td>" . ($regular_hours ? $regular_hours : '') . "</td>";
                    echo "<td>$overtime_hours</td>"; 
                    echo "<td>$status</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>

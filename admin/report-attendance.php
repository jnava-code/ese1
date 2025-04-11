<?php include('header.php'); ?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>

<?php


$conn = mysqli_connect('localhost', 'root', '', 'esetech');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$days_in_month = 0;
// Handle the form submission
if (isset($_POST['search'])) {
    // Get the selected employee ID
    $search_name = $_POST['search_name'];
    $employee_id = intval($_POST['employee_id']);
    
    // Ensure it's valid before proceeding
    if ($employee_id <= 0) {
        die("Invalid employee ID.");
    }

    // Get month and year
    $monthYear = $_POST['month-year'];
    list($month, $year) = explode('-', $monthYear); 

    // Fetch employee details
    $sql_employee = "SELECT employee_id, department, position FROM employees WHERE id = ? LIMIT 1";
    if ($stmt = mysqli_prepare($conn, $sql_employee)) {
        mysqli_stmt_bind_param($stmt, "i", $employee_id);
        mysqli_stmt_execute($stmt);
        $result_employee = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result_employee)) {
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
    // Fetch attendance
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
        mysqli_stmt_bind_param($stmt_attendance, "iii", $official_employee_id, $year, $month);
        mysqli_stmt_execute($stmt_attendance);
        $result_attendance = mysqli_stmt_get_result($stmt_attendance);

        while ($attendance_row = mysqli_fetch_assoc($result_attendance)) {
            $attendance_date = $attendance_row['date'];
            $day = date("d", strtotime($attendance_date));
            $attendance_data[$day] = $attendance_row;
        }
        mysqli_stmt_close($stmt_attendance);
    }
}


// function convertTo12HourFormat($time) {
//     return date("h:i A", strtotime($time));
// }

$sql_hired_date = "SELECT MIN(YEAR(hire_date)) AS min_year FROM employees";
$result_hired_date = mysqli_query($conn, $sql_hired_date);

if ($result_hired_date) {
    $row = mysqli_fetch_assoc($result_hired_date);
    $existing_year = $row['min_year'];
} else {
    echo "Error: " . mysqli_error($conn);
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

        .attendance-report-content form {
            display: flex;
            gap: 5px;
            flex-direction: column;
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
        
        .employees {
            position: relative;
        }

        .search-results {
            position: absolute;
            top: 32px;
            left: 15%;
            width: 100%;
            max-width: 550px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            max-height: 50px;
            overflow-y: auto;
            display: none; /* Hidden by default */
        }

        .search-results .search-item {
            padding: 10px;
            cursor: pointer;
            transition: background 0.3s ease-in-out;
        }

        .search-results .search-item:hover {
            background: #f1f1f1;
        }

        .search-results.active {
            display: block;
        }

        button[type="submit"] {
            padding: 12px 20px;
            font-size: 14px;
            margin-top: 10px;
        }
        @media print {
            body * {
                font-size: 8px;
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
                width: 108px;
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
                <label for="search_name">Employee Name: </label>
                <input type="search" id="search_name" name="search_name" autocomplete="off" placeholder="Type employee name..." 
                    value="<?php echo !empty($search_name) ? htmlspecialchars($search_name) : ''; ?>">
                
                <input type="hidden" id="employee_id" name="employee_id" 
                    value="<?php echo !empty($employee_id) ? htmlspecialchars($employee_id) : ''; ?>">

                <div id="searchResults" class="search-results"></div>
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

                        $startYear = $existing_year;

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
            <th>Total Overtime Hours</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
<?php
if (!empty($month) && !empty($year)) {
    for ($day = 1; $day <= $days_in_month; $day++) {
        // Generate the correct date for the current month and year
        $date_string = sprintf("%04d-%02d-%02d", intval($year), intval($month), $day);
        $current_date = new DateTime($date_string);
        $today_date = new DateTime('today'); // Compare only date

        // Get the day name
        $day_name = $current_date->format('l');
        $is_future_date = $current_date > $today_date;
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
                    $clock_in_time = $attendance_for_day['clock_in_time'] ? date('h:i:s A', strtotime($attendance_for_day['clock_in_time'])) : '-';
                    $clock_out_time = $attendance_for_day['clock_out_time'] ? date('h:i:s A', strtotime($attendance_for_day['clock_out_time'])) : '-';

                    $total_hours = floatval($attendance_for_day['total_hours']); // Convert to float
                    $regular_hours = 8;

                    // Convert total hours into hours and minutes
                    $total_minutes = round($total_hours * 60); // Convert to minutes
                    $total_worked_hours = intdiv($total_minutes, 60);
                    $total_worked_minutes = $total_minutes % 60;

                    // Calculate overtime
                    $overtime_minutes = max(0, $total_minutes - ($regular_hours * 60));
                    $overtime_hours = intdiv($overtime_minutes, 60);
                    $overtime_remaining_minutes = $overtime_minutes % 60;
                    
                    // Format overtime hours as "H:MM"
                    $formatted_overtime = $overtime_hours . ":" . str_pad($overtime_remaining_minutes, 2, "0", STR_PAD_LEFT);

                    $status = $attendance_for_day['status'];

                    echo "<td>$clock_in_time</td>";
                    echo "<td>$clock_out_time</td>";
                    echo "<td>$total_worked_hours:$total_worked_minutes</td>"; // Show Hours:Minutes
                    echo "<td>$regular_hours</td>";
                    
                    if ($overtime_minutes > 0) {
                        echo "<td>$formatted_overtime</td>"; // Show overtime in H:MM format
                    } else {
                        echo "<td>0:00</td>"; // No overtime
                    }

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
    $days_on_leave = 0;
    $working_days = 0; // Count of weekdays (excluding weekends & future dates)

    // Start outputting table rows
    for ($day = 1; $day <= $days_in_month; $day++) {
        // Build date objects
        $date_string = sprintf("%04d-%02d-%02d", intval($year), intval($month), $day);
        $current_date = new DateTime($date_string);
        $today_date = new DateTime('today');
        $day_name = $current_date->format('l');
        $is_future_date = $current_date > $today_date;
        $is_weekend = ($day_name === 'Saturday' || $day_name === 'Sunday');


        // If the day is in the future, output empty cells
        if(!$is_weekend && !$is_future_date) {
            // This is a working day: increase working day count
            $working_days++;
            // Try to retrieve attendance data using a day key (zero-padded if necessary)
            $day_key = sprintf("%02d", $day);
            $attendance_for_day = $attendance_data[$day_key] ?? null;

            if ($attendance_for_day) {
                // There is attendance – output details and count as present
                $clock_in_time = $attendance_for_day['clock_in_time'];
                $clock_out_time = $attendance_for_day['clock_out_time'];
                $total_hours = round($attendance_for_day['total_hours']);
                $regular_hours = 8;
                $overtime_hours = max(0, $total_hours - $regular_hours);
                $status = $attendance_for_day['status'];

                // Count as present if status is either "Present", "Overtime", or "Late"
                if (in_array($status, ["Present", "Overtime", "Late"])) {
                    $days_present++;
                }
                if ($status === "Late") {
                    $days_late++;
                }
                $total_overall_hours += $total_hours;
                $total_regular_hours += $regular_hours;
                $total_overtime_hours += $overtime_hours;
            } else {
                // No attendance record found; check if the employee was on approved leave
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
                        $days_on_leave++;
                    } else {
                        $days_absent++;
                    }
                    mysqli_stmt_close($stmt_leave);
                }
            }
        }
    }
    ?>
    </tbody>
    </table>

    <table style="width: 50%; border-collapse: collapse; float: left;">
        <tr>
            <td style="width: 33%; text-align: center; padding: 5px; font-size: 11px;">Total of Overall Hours</td>
            <td style="width: 33%; text-align: center; padding: 5px; font-size: 11px;">Regular Hours</td>
            <td style="width: 33%; text-align: center; padding: 5px; font-size: 11px;">Total Hours of Over Time</td>
        </tr>
        <tr>
            <td style="border: 1px solid black; text-align: center; padding: 5px;"><?php echo $total_overall_hours; ?></td>
            <td style="border: 1px solid black; text-align: center; padding: 5px;"><?php echo $total_regular_hours; ?></td>
            <td style="border: 1px solid black; text-align: center; padding: 5px;"><?php echo $total_overtime_hours; ?></td>
        </tr>
    </table>

    <div style="float: right;">
        <table style="border-collapse: collapse;">
            <tr>
                <td style="padding: 5px; text-align: right;">Number of Days Present:</td>
                <td style="border: 1px solid black; width: 40px; text-align: center; padding: 5px;"><?php echo $days_present; ?></td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: right;">Number of Days Absent:</td>
                <td style="border: 1px solid black; width: 40px; text-align: center; padding: 5px;"><?php echo $days_absent; ?></td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: right;">Number of Days on Leave:</td>
                <td style="border: 1px solid black; width: 40px; text-align: center; padding: 5px;"><?php echo $days_on_leave; ?></td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: right;">Number of Late:</td>
                <td style="border: 1px solid black; width: 40px; text-align: center; padding: 5px;"><?php echo $days_late; ?></td>
            </tr>
        </table>
    </div>
<?php
}
?>
    </div>

    <?php include('footer.php'); ?>

    <script>
    function printReport() {
        window.print();
    }

        let searchInput = document.getElementById('search_name');
        let resultsContainer = document.getElementById('searchResults');
        let employeeIdInput = document.getElementById('employee_id');

        searchInput.addEventListener('input', function () {
            let query = searchInput.value.trim();

            if (query.length < 2) {
                resultsContainer.innerHTML = '';
                return;
            }

            fetch(`search_employee?query=${query}`)
            .then(response => response.text())
            .then(data => {
                if (data.trim() !== '') {
                    resultsContainer.innerHTML = data;
                    resultsContainer.classList.add('active');
                } else {
                    resultsContainer.innerHTML = '';
                    resultsContainer.classList.remove('active');
                }
            });
        });

        window.selectEmployee = function (id, name) {
            searchInput.value = name; // Set name in input field
            employeeIdInput.value = id; // Store ID in hidden input
            resultsContainer.innerHTML = ''; // Clear results
            resultsContainer.classList.remove('active');
        };
    
        // document.addEventListener('click', function (event) {
        //     if (event.target.classList.contains('search-item')) {
        //         searchInput.value = event.target.textContent;
        //         resultsContainer.innerHTML = '';
        //     }
        // });

        // // Hide results when clicking outside
        // document.addEventListener('click', function (event) {
        //     if (!searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
        //         resultsContainer.innerHTML = '';
        //     }
        // });

    </script>
</body>
</html>

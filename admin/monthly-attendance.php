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

@media print {
        header,
        .main-content .monthly-h2,
        .report_btn,
        .action-buttons,
        .form-row {
            display: none !important;
        }

        table th,
        table tr {
            font-size: 12px;
        }

        .main-content {
            padding: 15px;
        }

        .count-totals-container {
            margin-bottom: 350px;
        }
    }

</style>

<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2 class="monthly-h2">MONTHLY ATTENDANCE MONITORING</h2>
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
                            <?php
                                $currentMonth = date('n') - 1; // Get the current month (0-11)
                            ?>
                            <option value="0" <?php echo ($currentMonth == 0) ? 'selected' : ''; ?>>January</option>
                            <option value="1" <?php echo ($currentMonth == 1) ? 'selected' : ''; ?>>February</option>
                            <option value="2" <?php echo ($currentMonth == 2) ? 'selected' : ''; ?>>March</option>
                            <option value="3" <?php echo ($currentMonth == 3) ? 'selected' : ''; ?>>April</option>
                            <option value="4" <?php echo ($currentMonth == 4) ? 'selected' : ''; ?>>May</option>
                            <option value="5" <?php echo ($currentMonth == 5) ? 'selected' : ''; ?>>June</option>
                            <option value="6" <?php echo ($currentMonth == 6) ? 'selected' : ''; ?>>July</option>
                            <option value="7" <?php echo ($currentMonth == 7) ? 'selected' : ''; ?>>August</option>
                            <option value="8" <?php echo ($currentMonth == 8) ? 'selected' : ''; ?>>September</option>
                            <option value="9" <?php echo ($currentMonth == 9) ? 'selected' : ''; ?>>October</option>
                            <option value="10" <?php echo ($currentMonth == 10) ? 'selected' : ''; ?>>November</option>
                            <option value="11" <?php echo ($currentMonth == 11) ? 'selected' : ''; ?>>December</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="year">Year</label>
                        <select name="year" required>
                            <?php
                                $currentYear = date('Y');
                                for ($i = 2020; $i <= $currentYear + 10; $i++) {
                                    // Check if the current year is equal to $i
                                    $selected = ($i == $currentYear) ? 'selected' : '';
                                    echo "<option value='$i' $selected>$i</option>";
                                }
                            ?>
                        </select>

                    </div>
                    <input type="submit" name="search" class="btn" value="Search">
                </form>
            </div>
    <div id="monthly-attendance" style="overflow-x:auto;">
        <?php
        // Determine the number of days in the selected month and year
        $selectedYear = isset($_POST['year']) ? $_POST['year'] : date('Y');
        $selectedMonth = isset($_POST['month']) ? $_POST['month'] + 1 : date('m'); // Adjust for PHP's 0-based months

        // Get the number of days in the selected month and year
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

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
        <?php if (empty($attendanceData)): ?>
            <tr>
                <td style="text-align: center;">No attendance records found for the selected criteria.</td>
            </tr>
        <?php else: ?>
            <?php
foreach ($attendanceData as $employee_id => $attendance): 

    // Initialize counters for statuses
    $absent_count = 0;
    $present_count = 0;
    $late_count = 0;
    $leave_count = 0;

    // Get the hire_date for the current employee
    $hire_date = $attendance[0]['hire_date']; 
    $hire_date_timestamp = strtotime($hire_date);

    // Get selected month and year from POST, default to current if not set
    $selectedMonth = isset($_POST['month']) ? $_POST['month'] + 1 : date('m');
    $selectedYear = isset($_POST['year']) ? $_POST['year'] : date('Y');
    
    // Get today's date
    $today_date = date('Y-m-d');

    // Get the employee's leave data for the selected month
    $leave_query = "SELECT leave_type, reason, start_date, end_date 
                    FROM leave_applications 
                    WHERE employee_id = ? 
                    AND status = 'Approved'
                    AND (
                        (YEAR(start_date) = ? AND MONTH(start_date) = ?) 
                        OR (YEAR(end_date) = ? AND MONTH(end_date) = ?)
                    )";
    $stmt = $conn->prepare($leave_query);
    $stmt->bind_param("siiii", $employee_id, $selectedYear, $selectedMonth, $selectedYear, $selectedMonth);
    $stmt->execute();
    $leave_result = $stmt->get_result();
    $leave_data = [];
    while ($leave = $leave_result->fetch_assoc()) {
        $leave_data[] = $leave;
    }

    // Format month and year for display
    $monthName = date('F', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
    
    // Get first day of the selected month and total days
    $first_day_timestamp = strtotime("$selectedYear-$selectedMonth-01");
    $days_in_month = date('t', $first_day_timestamp);
    $first_day_of_week = date('w', $first_day_timestamp); // 0 = Sunday, 6 = Saturday

    echo "<table class='table_content' border='1'>";
    echo "<tr><th colspan='7' class='employee-header'>" . htmlspecialchars($attendance[0]['full_name'], ENT_QUOTES, 'UTF-8') . " 
            ( <span class='employee_id_display'>" . htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') . "</span> ) - $monthName $selectedYear</th></tr>";
    echo "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";
    echo "<tr>";

    // Fill empty cells before the first day of the month
    for ($i = 0; $i < $first_day_of_week; $i++) {
        echo "<td class='prev-month'></td>";
    }

    // Loop through the days of the selected month
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf("%04d-%02d-%02d", $selectedYear, $selectedMonth, $day);
        $date_timestamp = strtotime($date);
        $dayOfWeek = date('w', $date_timestamp); // 0 = Sunday, 6 = Saturday

        // Default display
        $status_display = "";
        $status_color = "#f0f0f0"; // Default background

        // Before hire date
        if ($date_timestamp < $hire_date_timestamp) {
            echo "<td class='before-hire-date'><strong>$day</strong></td>";
        }
        // Weekends (Saturday and Sunday) should only display the day number
        elseif ($dayOfWeek == 0 || $dayOfWeek == 6) {
            echo "<td><strong>$day</strong></td>";
        }
        // Future dates (after today) should only display the day number
        elseif ($date > $today_date) {
            echo "<td><strong>$day</strong></td>";
        }
        else {
            // Check attendance
            $status = 'A'; // Default to Absent
            $clock_in_time = '-';
            $clock_out_time = '-';
            $total_hours = '-';

            foreach ($attendance as $record) {
                if ($record['date'] == $date) {
                    $status = htmlspecialchars(substr($record['attendance_status'], 0, 1), ENT_QUOTES, 'UTF-8');
                    $clock_in_time = $record['clock_in_time'] ? htmlspecialchars($record['clock_in_time'], ENT_QUOTES, 'UTF-8') : '-';
                    $clock_out_time = $record['clock_out_time'] ? htmlspecialchars($record['clock_out_time'], ENT_QUOTES, 'UTF-8') : '-';
                    $total_hours = $record['total_hours'] ? htmlspecialchars($record['total_hours'], ENT_QUOTES, 'UTF-8') : '-';
                    break;
                }
            }

            // Check leave status
            $leave_status = '';

                foreach ($leave_data as $leave) {
                    if ($date >= $leave['start_date'] && $date <= $leave['end_date']) {
                        $leave_status = "On Leave <br> <strong>Type:</strong> " . htmlspecialchars($leave['leave_type'], ENT_QUOTES, 'UTF-8') . "<br> 
                                    <strong>Reason:</strong> " . htmlspecialchars($leave['reason'], ENT_QUOTES, 'UTF-8');
                        $status_color = "#74c0fc"; // Blue for leave
                        $leave_count++;
                        break;
                    } if ($status == "A") {
                        $status_display = "Absent";
                        $status_color = "#ff8787"; // Red for absent
                        $absent_count++;
                    } elseif ($status == "P") {
                        $status_display = "Present";
                        $status_color = "#69db7c"; // Green for present
                        $present_count++;
                    } elseif ($status == "L") {
                        $status_display = "Late";
                        $status_color = "#ffa94d"; // Orange for late
                        $late_count++;
                    } else {
                        $status_display = "N/A";
                    }
                }
            

            // Display attendance details
            echo "<td><strong style='color: $status_color'>$day</strong><br>";
            echo $status_display ? "<strong>Status:</strong> $status_display <br>" : "";
            if ($clock_in_time != '-') {
                echo "<strong>In:</strong> $clock_in_time<br>";
                echo "<strong>Out:</strong> $clock_out_time<br>";
                echo "<strong>Total hours:</strong> $total_hours<br>";
            }
            echo "</td>";
        }

        // Start a new row after Saturday
        if ($dayOfWeek == 6) {
            echo "</tr><tr>";
        }
    }

    // Fill remaining cells in the last row
    $remaining_cells = (7 - (($days_in_month + $first_day_of_week) % 7)) % 7;
    if ($remaining_cells > 0) {
        for ($i = 0; $i < $remaining_cells; $i++) {
            echo "<td class='next-month'></td>";
        }
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
    </div>
</div>
            </div>

            <script>
document.addEventListener("DOMContentLoaded", function () {
    const reportBtn = document.querySelector(".report_btn");
    
    if (reportBtn) {
        reportBtn.addEventListener("click", (e) => {
            const clicked = e.target.closest("a"); // Detect clicks on <a> elements inside dropdown-content
            
            if (!clicked) return; // Prevent errors if clicked outside the expected buttons

            if (clicked.classList.contains("print_btn")) {
                window.print();
            } else if (clicked.classList.contains("pdf_btn")) {
                generatePDF();
            } else if (clicked.classList.contains("excel_btn")) {
                generateExcel();
            } else if (clicked.classList.contains("word_btn")) {
                generateWord();
            }
        });
    }

    function generatePDF() {
        const element = document.getElementById("monthly-attendance");

        const style = document.createElement("style");
        style.innerHTML = `
            header, .main-content .monthly-h2, .report_btn, .action-buttons, .form-row {
                display: none !important;
            }
            table th, table tr {
                font-size: 12px;
            }
            .main-content {
                padding: 15px;
            }
            .count-totals-container {
                margin-bottom: 380px;
            }
        `;
        document.head.appendChild(style);
        
        const clonedElement = element.cloneNode(true);

        html2pdf()
            .set({
                margin: 1,
                filename: "monthly_attendance.pdf",
                image: { type: "jpeg", quality: 0.98 },
                html2canvas: { dpi: 192, scale: 2, letterRendering: true, useCORS: true },
                jsPDF: { unit: "mm", format: "a4", orientation: "landscape" }
            })
            .from(clonedElement)
            .toPdf()
            .save()
            .then(() => {
                document.head.removeChild(style);
            });
    }

    function generateExcel() {
        const table = document.getElementById("monthly-attendance");
        const rows = [];

        const employeeName = document.querySelector('.employee_id_display').textContent;
        rows.push([employeeName]);

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
        XLSX.utils.book_append_sheet(wb, ws, "Monthly Attendance");
        XLSX.writeFile(wb, "monthly_attendance.xlsx");
    }

    function generateWord() {
        const table = document.getElementById("monthly-attendance").cloneNode(true);
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
            <body>${table.outerHTML}</body>
            </html>`;

        const blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "monthly_attendance.doc";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});
                

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
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/docx/7.1.0/docx.min.js"></script>
</main>
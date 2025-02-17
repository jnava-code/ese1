<?php
// Connect to the database
include('header.php');
include('includes/sideBar.php');

// Get departments to populate the dropdown
$deptSelect = "SELECT * FROM departments WHERE is_archived = 0 ORDER BY dept_name ASC";
$deptResult = mysqli_query($conn, $deptSelect);

if ($deptResult) {
    $deptOptions = '';
    while ($row = mysqli_fetch_assoc($deptResult)) {
        $deptOptions .= '<option value="' . htmlspecialchars($row['dept_name'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['dept_name'], ENT_QUOTES, 'UTF-8') . '</option>';
    }
}

// Get current year and month
$currentYear = date('Y');
$currentMonth = date('m');

// Initialize default values for the year and month
$selectedYear = isset($_POST['year']) ? $_POST['year'] : $currentYear;  // Default to current year
$selectedMonth = isset($_POST['month']) ? $_POST['month']: $currentMonth;  // Default to current month

// Get the selected week from POST
$selectedWeek = isset($_POST['week']) ? $_POST['week']: 1;  // Default to Week 1 if not set

// Check if the search button was clicked
if (isset($_POST['search_week'])) {
    // Get the start of the selected month
    $firstDayOfMonth = strtotime("{$selectedYear}-{$selectedMonth}-01");

    // Find the first Sunday of the month
    $firstSunday = strtotime("next Sunday", $firstDayOfMonth);
    if (date('m', $firstSunday) != $selectedMonth) {
        // If the first Sunday goes into the next month, get the previous Sunday
        $firstSunday = strtotime("last Sunday", $firstDayOfMonth);
    }

    // Calculate the start date for the selected week
    if ($selectedWeek == 1) {
        // For the first week, no need to add any days, just use the first Sunday
        $startDate = date('Y-m-d', strtotime($firstSunday));
    } else {
        // For subsequent weeks, add (selectedWeek - 1) * 7 days
        $startDate = date('Y-m-d', strtotime("+".($selectedWeek - 2) * 7 . " days", $firstSunday));
    }
    $endDate = date('Y-m-d', strtotime("$startDate + 6 days"));

    // Fetch attendance data for the selected week and year
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
    LEFT JOIN leave_applications la ON e.employee_id = la.employee_id 
        AND a.date BETWEEN la.start_date AND la.end_date
    WHERE YEAR(a.date) = $selectedYear 
    AND MONTH(a.date) = $selectedMonth 
    AND a.date BETWEEN '$startDate' AND '$endDate'
    ORDER BY a.date, e.employee_id
    ";

    $attendanceResult = mysqli_query($conn, $sql);
    $attendanceData = [];
    while ($row = mysqli_fetch_assoc($attendanceResult)) {
        $attendanceData[$row['employee_id']][] = $row;
    }
}
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
/* Additional CSS to style the attendance table and form */
.month-and-week {
    display: flex;
    gap: 5px;
}

.attendance-table {
    padding: 0px 2rem ;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background-color: #fff;
}

table th, table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

table th {
    background-color: #f8f9fa;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #f1f1f1;
}

@media (max-width: 768px) {
    table th, table td {
        padding: 8px;
        font-size: 12px;
    }
}

@media print {
        header,
        .main-content h2,
        .report_btn,
        .month-and-week {
            display: none !important;
        }

        .main-content,
        .attendance-table {
            padding: 0px;
        }

        table th,
        table tr {
            font-size: 12px;
        }
    }
</style>

<main class="main-content">
    <section id="dashboard">
        <h2>WEEKLY ATTENDANCE MONITORING</h2>
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

        <form action="" method="POST" class="month-and-week">
            <div class="col-md-6">
                <label for="month">Month</label>
                <select name="month" id="month" required onchange="updateWeeks()">
                    <option value="1" <?php echo ($selectedMonth == 1) ? 'selected' : ''; ?>>January</option>
                    <option value="2" <?php echo ($selectedMonth == 2) ? 'selected' : ''; ?>>February</option>
                    <option value="3" <?php echo ($selectedMonth == 3) ? 'selected' : ''; ?>>March</option>
                    <option value="4" <?php echo ($selectedMonth == 4) ? 'selected' : ''; ?>>April</option>
                    <option value="5" <?php echo ($selectedMonth == 5) ? 'selected' : ''; ?>>May</option>
                    <option value="6" <?php echo ($selectedMonth == 6) ? 'selected' : ''; ?>>June</option>
                    <option value="7" <?php echo ($selectedMonth == 7) ? 'selected' : ''; ?>>July</option>
                    <option value="8" <?php echo ($selectedMonth == 8) ? 'selected' : ''; ?>>August</option>
                    <option value="9" <?php echo ($selectedMonth == 9) ? 'selected' : ''; ?>>September</option>
                    <option value="10" <?php echo ($selectedMonth == 10) ? 'selected' : ''; ?>>October</option>
                    <option value="11" <?php echo ($selectedMonth == 11) ? 'selected' : ''; ?>>November</option>
                    <option value="12" <?php echo ($selectedMonth == 12) ? 'selected' : ''; ?>>December</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="year">Year</label>
                <select name="year" id="year" required>
                    <?php
                    // Dynamically generate years (e.g., from current year - 5 to current year + 5)
                    $currentYear = date('Y');
                    for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
                        echo "<option value=\"$i\"" . ($i == $selectedYear ? ' selected' : '') . ">$i</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="week">Week</label>
                <select name="week" id="week" required>
                    <!-- Week options will be populated based on the selected month -->
                </select>
            </div>
            <input type="submit" name="search_week" value="Search" class="btn btn-primary">
        </form>

        <div class="attendance-table">
            <table id="attendance-table" class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Sunday</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($attendanceData) && !empty($attendanceData)): ?>
                        <?php foreach ($attendanceData as $employee_id => $attendance): ?>
                            <tr>
                                <?php 
                                // Get the employee's full name and hire date
                                $full_name = '';
                                $hire_date = '';
                                if (!empty($attendance)) {
                                    $full_name = htmlspecialchars($attendance[0]['full_name'], ENT_QUOTES, 'UTF-8');
                                    $hire_date = $attendance[0]['hire_date'];
                                }

                                // Convert hire_date to a comparable format
                                $hire_date = date('Y-m-d', strtotime($hire_date));
                                ?>
                                <td><?php echo $full_name; ?></td>

                                <?php 
                                // Loop through the days of the week (Sunday to Saturday)
                                for ($day = 0; $day < 7; $day++) {
                                    $currentDate = date('Y-m-d', strtotime($startDate . ' + ' . $day . ' days'));
                                    $today = date('Y-m-d'); // Get today's date
                                
                                    // If the date is in the future, leave the cell empty
                                    if ($currentDate > $today) {
                                        echo "<td></td>";
                                        continue;
                                    }
                                
                                    $status = 'A'; // Default status is Absent
                                    $clock_in_time = '-';
                                    $clock_out_time = '-';
                                    $total_hours = '-';
                                
                                    $status_display = '';
                                    $status_color = '#f8f9fa'; // Default status color (light gray)
                                
                                    // Skip Saturday and Sunday
                                    if ($day == 0 || $day == 6) { // 0 for Sunday, 6 for Saturday
                                        echo "<td></td>"; // Empty cell for Saturday and Sunday
                                        continue;
                                    }
                                
                                    // Skip dates before the hire date for that employee
                                    if ($currentDate < $hire_date) {
                                        // Leave the cell empty if the date is before the hire date
                                        echo "<td></td>";
                                        continue;
                                    }
                                
                                    // Check if attendance data exists for this day
                                    foreach ($attendance as $record) {
                                        if (isset($record['date']) && $record['date'] == $currentDate) {
                                            $status = htmlspecialchars(substr($record['attendance_status'], 0, 1), ENT_QUOTES, 'UTF-8');
                                            $clock_in_time = $record['clock_in_time'] ? htmlspecialchars($record['clock_in_time'], ENT_QUOTES, 'UTF-8') : '-';
                                            $clock_out_time = $record['clock_out_time'] ? htmlspecialchars($record['clock_out_time'], ENT_QUOTES, 'UTF-8') : '-';
                                            $total_hours = $record['total_hours'] ? htmlspecialchars($record['total_hours'], ENT_QUOTES, 'UTF-8') : '-';
                                            break; // Exit the inner loop once the record is found
                                        }
                                    }
                                
                                    // Check if employee is on approved leave
                                    $leave_query = "SELECT leave_type, reason FROM leave_applications WHERE employee_id = ? AND status = 'Approved' AND ? BETWEEN start_date AND end_date";
                                    $stmt = $conn->prepare($leave_query);
                                    $stmt->bind_param("ss", $employee_id, $currentDate);
                                    $stmt->execute();
                                    $leave_result = $stmt->get_result();
                                
                                    if ($leave = $leave_result->fetch_assoc()) {
                                        $status_display = "On Leave <br> <strong>Type:</strong> " . htmlspecialchars($leave['leave_type'], ENT_QUOTES, 'UTF-8') . "<br> <strong>Reason:</strong> " . htmlspecialchars($leave['reason'], ENT_QUOTES, 'UTF-8');
                                        $status_color = "#74c0fc";  // Blue for leave
                                    } elseif ($status == "A") {
                                        $status_display = "Absent";
                                        $status_color = "#ff8787";  // Red for absence
                                    } elseif ($status == "P") {
                                        $status_display = "Present";
                                        $status_color = "#69db7c";  // Green for present
                                    } elseif ($status == "L") {
                                        $status_display = "Late";
                                        $status_color = "#f7b731";  // Yellow for late
                                    } else {
                                        $status_display = "N/A";
                                    }
                                
                                    // Output the table cell for the current day with color-coded status
                                    echo "<td>";
                                    echo $status_display == "" ? "" : "<strong>Status:</strong> $status_display <br>";
                                    if ($clock_in_time != '-') {
                                        echo "<strong>In:</strong> $clock_in_time<br>";
                                        echo "<strong>Out:</strong> $clock_out_time<br>";
                                        echo "<strong>Total hours:</strong> $total_hours<br>";
                                    }
                                    echo "</td>";
}

                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8">No attendance records found for the selected week.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>

const reportBtn = document.querySelector(".report_btn"); 

if(reportBtn) {
    reportBtn.addEventListener("click", (e) => {
        e.preventDefault();
        let clicked = e.target.closest(".btn, .dropdown-content a"); // Include dropdown links

        if(!clicked) return;

        if(clicked.classList.contains("print_btn")) {
            window.print();
        } else if(clicked.classList.contains("pdf_btn")) {
            const element = document.getElementById("attendance-table");

            // Create a temporary style element to ensure proper styling
            const style = document.createElement("style");
            style.innerHTML = `
                header, .main-content h2, .report_btn, .month-and-week {
                    display: none !important;
                }
                .main-content, .attendance-table {
                    padding: 0px;
                }
                table th, table tr {
                    font-size: 12px;
                }
            `;

            document.head.appendChild(style);
            const clonedElement = element.cloneNode(true);

            html2pdf()
                .set({
                    margin: 0,
                    filename: "weekly_attendance.pdf",
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

        } else if(clicked.classList.contains("excel_btn")) {
            const table = document.getElementById("attendance-table");
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
            XLSX.utils.book_append_sheet(wb, ws, "Weekly Attendance");
            XLSX.writeFile(wb, "weekly_attendance.xlsx");

        } else if(clicked.classList.contains("word_btn")) {
            const table = document.getElementById("attendance-table").cloneNode(true);
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
                        table th, table tr {
                            font-size: 12px;
                        }
                    </style>
                </head>
                <body>
                    ${table.outerHTML}
                </body>
                </html>`;

            const blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "weekly_attendance.doc";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });
}
// Function to update the week dropdown dynamically based on the selected month
function updateWeeks() {
    var month = document.getElementById('month').value;
    var weeksInMonth = getWeeksInMonth(month);
    var weekSelect = document.getElementById('week');

    // Clear existing week options
    weekSelect.innerHTML = '';

    // Add week options dynamically
    for (var i = 1; i <= weeksInMonth; i++) {
        var option = document.createElement('option');
        option.value = i;
        option.textContent = 'Week ' + i;
        weekSelect.appendChild(option);
    }

    // Set the default week to Week 1
    weekSelect.value = 1;
}

// Function to calculate the number of weeks in a given month
function getWeeksInMonth(month) {
    var date = new Date();
    var year = date.getFullYear();
    var firstDay = new Date(year, month, 1); // First day of the month
    var lastDay = new Date(year, month + 1, 0); // Last day of the month
    var daysInMonth = lastDay.getDate(); // Total days in the month

    return Math.ceil(daysInMonth / 7); // Return the number of weeks
}

updateWeeks(); // Update weeks when page loads
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/docx/7.1.0/docx.min.js" defer></script>
<?php include('header.php'); ?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>

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

$sql = "
    SELECT 
        CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name,
        e.employee_id,
        a.date,
        IFNULL(la.status, 'Present') AS attendance_status
    FROM employees e
    LEFT JOIN attendance a ON e.employee_id = a.employee_id
    LEFT JOIN leave_applications la ON e.employee_id = la.employee_id AND a.date BETWEEN la.start_date AND la.end_date
    WHERE a.date LIKE '$month%'
    ORDER BY e.employee_id, a.date";
$attendanceResult = mysqli_query($conn, $sql);  // Use $sql here, not $attendanceQuery
$attendanceData = [];
while ($row = mysqli_fetch_assoc($attendanceResult)) {
    $attendanceData[$row['employee_id']][] = $row;
}
?>


<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>ATTENDANCE MONITORING</h2>
    <div style="overflow-x:auto;">
        <table id="attendance-table" class="table table-striped">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <?php
                    $days_in_month = date('t'); // Get the number of days in the current month
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        echo "<th>" . $day . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through the employee records and display attendance
                $result = mysqli_query($conn, $sql);
                $employees = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $employees[$row['employee_id']]['name'] = $row['full_name'];
                    // Convert the attendance status to the first letter
                    $attendanceStatus = $row['attendance_status'];
                    if ($attendanceStatus == 'Present') {
                        $employees[$row['employee_id']]['attendance'][$row['date']] = 'P'; // P for Present
                    } elseif ($attendanceStatus == 'Absent') {
                        $employees[$row['employee_id']]['attendance'][$row['date']] = 'A'; // A for Absent
                    } elseif ($attendanceStatus == 'On Leave') {
                        $employees[$row['employee_id']]['attendance'][$row['date']] = '0'; // L for On Leave
                    } else {
                        $employees[$row['employee_id']]['attendance'][$row['date']] = 'O'; // Default to Present
                    }
                }

                // Output employee data
                foreach ($employees as $employee_id => $employee) {
                    echo "<tr>";
                    echo "<td>" . $employee['name'] . "</td>";
                    echo "<td>" . $employee_id . "</td>";

                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $date = date('Y-m-', strtotime('first day of this month')) . str_pad($day, 2, '0', STR_PAD_LEFT);
                        echo "<td>" . (isset($employee['attendance'][$date]) ? $employee['attendance'][$date] : 'A') . "</td>"; // Default to A if no status is found
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
            </div>
<style>
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

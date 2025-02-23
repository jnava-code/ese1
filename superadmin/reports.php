<?php include('header.php'); ?>
<?php include('includes/sideBar.php'); ?>

<?php 
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Function to get counts from the database
    function getCount($conn, $sql) {    
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? $row[array_key_first($row)] : 0;
        } else {
            echo "Error: " . mysqli_error($conn);
            return 0;
        }
    }

    // Function to count weekdays (excluding Saturdays and Sundays)
    function countWeekdays($start_date, $end_date) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day'));

        $weekdays = 0;
        foreach ($dateRange as $date) {
            if ($date->format('N') < 6) {
                $weekdays++;
            }
        }
        return $weekdays;
    }

    // Function to get the total absent weekdays for all employees
    function getAbsentCount($conn, $sql) {    
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $total_days = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $employee_id = $row['employee_id']; 
                $hire_date = $row['hire_date'];
                $total_days += countWeekdays($hire_date, date('Y-m-d')); 
            }
            return $total_days;
        } else {
            echo "Error: " . mysqli_error($conn);
            return 0;
        }
    }

    // Get the present date
    $presentDate = date('Y-m-d');

    // Get the total absent weekdays for all employees
    $absent_days = getAbsentCount($conn, "SELECT e.employee_id, e.hire_date FROM employees e WHERE e.hire_date <= '$presentDate'");
    $late_count = getCount($conn, "SELECT count(*) as late_count FROM attendance WHERE status = 'Late'");
    $ontime_count = getCount($conn, "SELECT count(*) as ontime_count FROM attendance WHERE status = 'On Time'");
    $leave_count = getCount($conn, "SELECT count(*) as leave_count FROM leave_applications WHERE status = 'Approved'");

    // $week_start = date('Y-m-d', strtotime('monday this week'));

    // Daily Counts
    $daily_absent = getAbsentCount($conn, "SELECT employee_id, hire_date FROM employees WHERE hire_date = '$presentDate'");
    $daily_late = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'Late' AND date = '$presentDate'");
    $daily_ontime = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'On Time' AND date = '$presentDate'");
    $daily_leave = getCount($conn, "SELECT COUNT(*) as count FROM leave_applications WHERE status = 'Approved' AND file_date = '$presentDate'");

    // Weekly Counts
    // $weekly_late = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'Late' AND date >= '$week_start'");
    // $weekly_ontime = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'On Time' AND date >= '$week_start'");
    // $weekly_leave = getCount($conn, "SELECT COUNT(*) as count FROM leave_applications WHERE status = 'Approved' AND file_date >= '$week_start'");

    $notAbsent = $late_count + $ontime_count + $leave_count;
    $total_absent_days = $absent_days - $notAbsent;

    // Fetch department names and colors from the departments table
    $sqlDepartments = "SELECT dept_name, colors FROM departments";
    $resultDepartments = mysqli_query($conn, $sqlDepartments);

    $departments = [];
    $colors = [];
    $employeeCounts = [];

    if ($resultDepartments) {
        while ($row = mysqli_fetch_assoc($resultDepartments)) {
            $departments[] = $row['dept_name'];
            $colors[] = $row['colors'];
            $deptName = $row['dept_name'];
            $sqlEmployeeCount = "SELECT COUNT(*) AS count FROM employees WHERE department = '$deptName'";
            $resultEmployeeCount = mysqli_query($conn, $sqlEmployeeCount);
            if ($resultEmployeeCount) {
                $employeeRow = mysqli_fetch_assoc($resultEmployeeCount);
                $employeeCounts[] = $employeeRow['count'];
            } else {
                $employeeCounts[] = 0;
            }
        }
    }

    // Convert PHP arrays to JavaScript arrays
    $departmentsJson = json_encode($departments);
    $colorsJson = json_encode($colors);
    $employeeCountsJson = json_encode($employeeCounts);

    // Get the count of male and female employees in each age range
    $ageRanges = ['20-29', '30-39', '40-49', '50-59', '60+'];
    $maleCounts = [];
    $femaleCounts = [];

    foreach ($ageRanges as $ageRange) {
        if ($ageRange == '60+') {
            $sqlMale = "SELECT COUNT(*) as count FROM employees WHERE gender = 'Male' AND age >= 60";
            $sqlFemale = "SELECT COUNT(*) as count FROM employees WHERE gender = 'Female' AND age >= 60";
        } else {
            $startAge = intval(substr($ageRange, 0, 2));
            $endAge = intval(substr($ageRange, 3, 2)) + 10;
            $sqlMale = "SELECT COUNT(*) as count FROM employees WHERE gender = 'Male' AND age >= $startAge AND age < $endAge";
            $sqlFemale = "SELECT COUNT(*) as count FROM employees WHERE gender = 'Female' AND age >= $startAge AND age < $endAge";
        }
        $maleCounts[] = getCount($conn, $sqlMale);
        $femaleCounts[] = getCount($conn, $sqlFemale);
    }

    // Convert PHP arrays to JavaScript arrays
    $maleCountsJson = json_encode($maleCounts);
    $femaleCountsJson = json_encode($femaleCounts);
    $ageRangesJson = json_encode($ageRanges);

    // Fetch job satisfaction and performance evaluation data
    $jobSatisfactionSummary = getCount($conn, "SELECT AVG(overall_rating) as avg_rating FROM job_satisfaction_surveys");
    $performanceEvaluationSummary = getCount($conn, "SELECT AVG(overall_score) as avg_score FROM performance_evaluations");

    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<style>
    .reports {
        display: flex;
        gap: 100px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .summary-section {
        width: 100%;
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 10px;
        background-color: #f9f9f9;
    }

    .summary-section h1 {
        text-align: center;
        margin-bottom: 20px;
    }

    .summary-section table {
        width: 100%;
        border-collapse: collapse;
    }

    .summary-section th, .summary-section td {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: center;
    }

    .summary-section th {
        background-color: #4dabf7;
        color: white;
    }

    @media print {
        header {
            display: none;
        }
    }
</style>
<body>   
    <!-- Main Content Area -->
    <main class="main-content">
        <section id="dashboard">
            <!-- Summary Section -->
            <div class="summary-section">
                <h1>Summary of Reports</h1>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Attendance Summary -->
                        <tr>
                            <td rowspan="4">Attendance</td>
                            <td>On Time (Present)</td>
                            <td><?php echo $ontime_count; ?></td>
                        </tr>
                        <tr>
                            <td>Late (Present)</td>
                            <td><?php echo $late_count; ?></td>
                        </tr>
                        <tr>
                            <td>Absent</td>
                            <td><?php echo $total_absent_days; ?></td>
                        </tr>
                        <tr>
                            <td>On Leave</td>
                            <td><?php echo $leave_count; ?></td>
                        </tr>

                        <!-- Job Satisfaction Summary -->
                        <tr>
                            <td>Job Satisfaction</td>
                            <td>Average Rating</td>
                            <td><?php echo number_format($jobSatisfactionSummary, 2); ?></td>
                        </tr>

                        <!-- Performance Evaluation Summary -->
                        <tr>
                            <td>Performance Evaluation</td>
                            <td>Average Score</td>
                            <td><?php echo number_format($performanceEvaluationSummary, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Charts Section -->
            <div class="reports">
                <!-- Attendance Report -->
                <div style="width: 100%; max-width: 0px;">
                    <h1>Daily Attendance Report</h1>
                    <canvas id="dailyAttendancePieChart"></canvas>
                    <h2>Total: <?php echo (int)$daily_late + (int)$daily_ontime + (int)$daily_leave; ?></h2>
                </div>

                <div style="width: 100%; max-width: 0px;">
                    <h1>Monthly Attendance Report</h1>
                    <canvas id="attendancePieChart"></canvas>
                    <h2>Total: <?php echo (int)$late_count + (int)$ontime_count + (int)$leave_count; ?></h2>
                </div>

                <!-- Employees Report -->
                <div style="width: 100%; max-width: 500px;">
                    <h1>Employees Report</h1>
                    <canvas id="departmentChart" width="400" height="400"></canvas>
                    <?php $totalEmployees = array_sum($employeeCounts); ?>
                    <h2>Total: <?php echo $totalEmployees; ?></h2>
                </div>

                <!-- Employees by Age and Gender Report -->
                <div style="width: 100%; max-width: 500px;">
                    <h1>Employees by Age and Gender Report</h1>
                    <canvas id="ageGenderChart" width="600" height="400"></canvas>
                </div>
            </div>
        </section>
    </main>

    <script>
        // ATTENDANCE REPORT
        var daily_late = <?php echo $daily_late; ?>;
        var daily_ontime = <?php echo $daily_ontime; ?>;
        var daily_leave = <?php echo $daily_leave; ?>;
        var daily_absent = <?php echo $daily_absent ?? 0; ?>;

        var dailyctx = document.getElementById('dailyAttendancePieChart').getContext('2d');
        var dailyAttendancePieChart = new Chart(dailyctx, {
            type: 'pie',
            data: {
                labels: ['On Time (Present)', 'Late (Present)', 'Absent', 'On Leave'],
                datasets: [{
                    data: [daily_ontime, daily_late, daily_absent, daily_leave], // Fixed
                    backgroundColor: ['#69db7c', '#ff8787', '#fa5252', '#4dabf7'],
                    borderWidth: 1
                }]
            },
        });
        
        var lateCount = <?php echo $late_count; ?>; 
        var onTimeCount = <?php echo $ontime_count; ?>; 
        var absentCount = <?php echo $total_absent_days; ?>; 
        var onLeaveCount = <?php echo $leave_count; ?>;  

        var ctx = document.getElementById('attendancePieChart').getContext('2d');
        var attendancePieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['On Time (Present)', 'Late (Present)', 'Absent', 'On Leave'],
                datasets: [{
                    data: [onTimeCount, lateCount, absentCount, onLeaveCount],
                    backgroundColor: ['#69db7c', '#ff8787', '#fa5252', '#4dabf7'],
                    borderWidth: 1
                }]
            },
        });

        // DEPARTMENT REPORT
        var departments = <?php echo $departmentsJson; ?>;
        var colors = <?php echo $colorsJson; ?>;
        var employees = <?php echo $employeeCountsJson; ?>;

        var departmentCtx = document.getElementById('departmentChart').getContext('2d');
        var departmentChart = new Chart(departmentCtx, {
            type: 'doughnut',
            data: {
                labels: departments,
                datasets: [{
                    data: employees,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
        });

        // AGE AND GENDER REPORT
        var ageRanges = <?php echo $ageRangesJson; ?>;
        var maleCounts = <?php echo $maleCountsJson; ?>;
        var femaleCounts = <?php echo $femaleCountsJson; ?>;

        var ctx = document.getElementById('ageGenderChart').getContext('2d');
        var ageGenderChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ageRanges,
                datasets: [{
                    label: 'Male Employees',
                    data: maleCounts,
                    borderColor: '#4b8bf5',
                    fill: false,
                    tension: 0.1,
                    borderWidth: 2
                }, {
                    label: 'Female Employees',
                    data: femaleCounts,
                    borderColor: '#f55d7a',
                    fill: false,
                    tension: 0.1,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Age Range'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Employees'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php include('footer.php'); ?>
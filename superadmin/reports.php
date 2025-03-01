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
    $undertime_count = getCount($conn, "SELECT count(*) as undertime_count FROM attendance WHERE status = 'Under Time'");
    $overtime_count = getCount($conn, "SELECT count(*) as overtime_count FROM attendance WHERE status = 'Over Time'");
    $leave_count = getCount($conn, "SELECT count(*) as leave_count FROM leave_applications WHERE status = 'Approved'");

    // $week_start = date('Y-m-d', strtotime('monday this week'));

    // Daily Counts
    $daily_absent = getAbsentCount($conn, "SELECT employee_id, hire_date FROM employees WHERE hire_date = '$presentDate'");
    $daily_late = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'Late' AND date = '$presentDate'");
    $daily_ontime = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'On Time' AND date = '$presentDate'");
    $daily_undertime = getCount($conn, "SELECT count(*) as undertime_count FROM attendance WHERE status = 'Under Time' AND date = '$presentDate'");
    $daily_overtime = getCount($conn, "SELECT count(*) as overtime_count FROM attendance WHERE status = 'Over Time' AND date = '$presentDate'");
    $daily_leave = getCount($conn, "SELECT COUNT(*) as count FROM leave_applications WHERE status = 'Approved' AND file_date = '$presentDate'");

    // Weekly Counts
    $currentWeekCondition = "WEEK(date, 1) = WEEK(NOW(), 1) AND YEAR(date) = YEAR(NOW())";
    $weekly_absent = getAbsentCount($conn, "SELECT employee_id, hire_date FROM employees WHERE WEEK(hire_date, 1) = WEEK(NOW(), 1) AND YEAR(hire_date) = YEAR(NOW())");
    $weekly_late = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'Late' AND $currentWeekCondition");
    $weekly_ontime = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'On Time' AND $currentWeekCondition");
    $weekly_undertime = getCount($conn, "SELECT COUNT(*) as undertime_count FROM attendance WHERE status = 'Under Time' AND $currentWeekCondition");
    $weekly_overtime = getCount($conn, "SELECT COUNT(*) as overtime_count FROM attendance WHERE status = 'Over Time' AND $currentWeekCondition");
    $weekly_leave = getCount($conn, "SELECT COUNT(*) as count FROM leave_applications WHERE status = 'Approved' AND WEEK(file_date, 1) = WEEK(NOW(), 1) AND YEAR(file_date) = YEAR(NOW())");

    // Monthly Counts
    $currentMonthCondition = "MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())";
    $monthly_absent = getAbsentCount($conn, "SELECT employee_id, hire_date FROM employees WHERE MONTH(hire_date) = MONTH(NOW()) AND YEAR(hire_date) = YEAR(NOW())");
    $monthly_late = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'Late' AND $currentMonthCondition");
    $monthly_ontime = getCount($conn, "SELECT COUNT(*) as count FROM attendance WHERE status = 'On Time' AND $currentMonthCondition");
    $monthly_undertime = getCount($conn, "SELECT COUNT(*) as undertime_count FROM attendance WHERE status = 'Under Time' AND $currentMonthCondition");
    $monthly_overtime = getCount($conn, "SELECT COUNT(*) as overtime_count FROM attendance WHERE status = 'Over Time' AND $currentMonthCondition");
    $monthly_leave = getCount($conn, "SELECT COUNT(*) as count FROM leave_applications WHERE status = 'Approved' AND MONTH(file_date) = MONTH(NOW()) AND YEAR(file_date) = YEAR(NOW())");

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

    // Add this query near your other database queries at the top
    $attrition_query = "SELECT 
        e.employee_id,
        COALESCE(AVG(CASE 
            WHEN a.status = 'On Time' THEN 1
            WHEN a.status = 'Late' THEN 0.5
            ELSE 0 
        END), 0) as attendance_score,
        COALESCE(AVG(js.overall_rating) / 5, 0.5) as satisfaction_score,
        COALESCE(AVG(pe.overall_score) / 100, 0) as performance_score,
        DATEDIFF(CURRENT_DATE, e.hire_date) / 365 as years_of_service
    FROM employees e
    LEFT JOIN attendance a ON e.employee_id = a.employee_id
    LEFT JOIN job_satisfaction_surveys js ON e.employee_id = js.employee_id
    LEFT JOIN performance_evaluations pe ON e.employee_id = pe.employee_id
    WHERE e.is_archived = 0
    GROUP BY e.employee_id";

    $result = mysqli_query($conn, $attrition_query);

    $low_risk = 0;
    $medium_risk = 0;
    $high_risk = 0;

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Calculate risk score using the same formula from predict.php
            $attendance_score = floatval($row['attendance_score']);
            $satisfaction_score = floatval($row['satisfaction_score']);
            $performance_score = floatval($row['performance_score']);
            $years_of_service = floatval($row['years_of_service']);

            // Weights for each factor
            $attendance_weight = 0.25;
            $satisfaction_weight = 0.25;
            $performance_weight = 0.25;
            $years_weight = 0.25;

            // Normalize years of service
            $normalized_years = min($years_of_service / 30, 1);
            
            // Calculate risk score
            $risk_score = 1 - (
                ($attendance_score * $attendance_weight) +
                ($satisfaction_score * $satisfaction_weight) +
                ($performance_score * $performance_weight) +
                ($normalized_years * $years_weight)
            );

            $risk_score = max(0, min(1, $risk_score));

            // Categorize risk
            if ($risk_score <= 0.3) {
                $low_risk++;
            } elseif ($risk_score <= 0.6) {
                $medium_risk++;
            } else {
                $high_risk++;
            }
        }
    }

    // Convert PHP variables to JavaScript
    echo "<script>
        const attritionData = {
            labels: ['Low Risk', 'Medium Risk', 'High Risk'],
            counts: [$low_risk, $medium_risk, $high_risk],
            colors: ['#28a745', '#ffc107', '#dc3545']
        };
    </script>";

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
        gap: 50px;
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

    .reports h1 {
        text-align: center;
    }
    @media print {
        header {
            display: none;
        }
    }

    .filter-section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .filter-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }

    .form-select, .form-control {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-width: 150px;
    }

    .filter-option {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .search-btn {
        background-color: #4dabf7;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .search-btn:hover {
        background-color: #3793dd;
    }

    .attendance-chart-container {
        margin-top: 20px;
        height: 400px;
        position: relative;
    }

    #chartTitle {
        color: #333;
        font-size: 1.5em;
        margin-bottom: 20px;
    }

    /* Match your existing color scheme */
    .chart-colors {
        --on-time: #69db7c;
        --late: #ff8787;
        --absent: #fa5252;
        --under-time: #ffcc00;
        --over-time: #8e44ad;
        --on-leave: #4dabf7;
    }
</style>
<body>   
    <!-- Main Content Area -->
    <main class="main-content">
        <section id="dashboard">
        <div class="filter-controls">
                    <select id="reportType" class="form-select">
                        <option value="">Select Report Type</option>
                        <option value="daily">Daily Report</option>
                        <option value="weekly">Weekly Report</option>
                        <option value="monthly">Monthly Report</option>
                    </select>

                    <!-- Daily Filter -->
                    <div id="dailyFilter" class="filter-option" style="display: none;">
                        <input type="date" id="dailyDate" class="form-control">
                        <button class="btn btn-primary search-btn" onclick="fetchDailyReport()">Search</button>
                    </div>

                    <!-- Weekly Filter -->
                    <div id="weeklyFilter" class="filter-option" style="display: none;">
                        <select id="weeklyMonth" class="form-select">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                        <select id="weeklyYear" class="form-select">
                            <?php 
                            $currentYear = date('Y');
                            for($year = $currentYear; $year >= $currentYear - 5; $year--) {
                                echo "<option value='$year'>$year</option>";
                            }
                            ?>
                        </select>
                        <select id="weekNumber" class="form-select">
                            <!-- Will be populated dynamically -->
                        </select>
                        <button class="btn btn-primary search-btn" onclick="fetchWeeklyReport()">Search</button>
                    </div>

                    <!-- Monthly Filter -->
                    <div id="monthlyFilter" class="filter-option" style="display: none;">
                        <select id="monthlyMonth" class="form-select">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                        <select id="monthlyYear" class="form-select">
                            <?php 
                            $currentYear = date('Y');
                            for($year = $currentYear; $year >= $currentYear - 5; $year--) {
                                echo "<option value='$year'>$year</option>";
                            }
                            ?>
                        </select>
                        <button class="btn btn-primary search-btn" onclick="fetchMonthlyReport()">Search</button>
                    </div>
                </div>
            <!-- Add this before your charts section -->
            <div class="filter-section">


                <!-- Add a title for the chart -->
                <h2 id="chartTitle" class="text-center" style="margin-top: 20px; display: none;">Attendance Report</h2>
                
                <!-- Chart container -->
                <div class="attendance-chart-container" style="display: none;">
                    <canvas id="filteredAttendanceChart"></canvas>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="reports">
                <!-- Attendance Report -->
                <div style="width: 100%; max-width: 350px;">
                    <h1>Daily Attendance Report</h1>
                    <canvas id="dailyAttendancePieChart"></canvas>
                </div>

                <div style="width: 100%; max-width: 350px;">
                    <h1>Weekly Attendance Report</h1>
                    <canvas id="weeklyAttendancePieChart"></canvas>
                </div>

                <div style="width: 100%; max-width: 350px;">
                    <h1>Monthly Attendance Report</h1>
                    <canvas id="monthlyAttendancePieChart"></canvas>
                </div>

                <!-- Employees Report -->
                <div style="width: 100%; max-width: 350px;">
                    <h1>Employees Report</h1>
                    <canvas id="departmentChart" width="400" height="400"></canvas>
                    <?php $totalEmployees = array_sum($employeeCounts); ?>
                </div>

                <div style="width: 100%; max-width: 350px;">
                    <h1>Attrition Risk Report</h1>
                    <canvas id="attritionChart" width="400" height="400"></canvas>
                </div>

                <!-- Employees by Age and Gender Report -->
                <div style="width: 100%; max-width: 500px;">
                    <h1>Employees by Age and Gender Report</h1>
                    <canvas id="ageGenderChart" width="600" height="400"></canvas>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <script>
       // DAILY ATTENDANCE REPORT
        var daily_late = <?php echo $daily_late; ?>;
        var daily_ontime = <?php echo $daily_ontime; ?>;
        var daily_undertime = <?php echo $daily_undertime; ?>;
        var daily_overtime = <?php echo $daily_overtime; ?>;
        var daily_leave = <?php echo $daily_leave; ?>;
        var daily_absent = <?php echo $daily_absent ?? 0; ?>;

        var dailyctx = document.getElementById('dailyAttendancePieChart').getContext('2d');
        var dailyAttendancePieChart = new Chart(dailyctx, {
            type: 'pie',
            data: {
                labels: ['On Time', 'Late', 'Absent', 'Under Time', 'Over Time', 'On Leave'],
                datasets: [{
                    data: [daily_ontime, daily_late, daily_absent, daily_undertime, daily_overtime, daily_leave],
                    backgroundColor: ['#69db7c', '#ff8787', '#fa5252', '#ffcc00', '#8e44ad', '#4dabf7'],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    datalabels: {
                        formatter: (value, ctx) => {
                            if (value === 0) return ""; // Hide labels when value is 0
                            let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = ((value / sum) * 100).toFixed(1) + "%";
                            return percentage;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 14
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // WEEKLY ATTENDANCE REPORT
        var weekly_late = <?php echo $weekly_late; ?>;
        var weekly_ontime = <?php echo $weekly_ontime; ?>;
        var weekly_undertime = <?php echo $weekly_undertime; ?>;
        var weekly_overtime = <?php echo $weekly_overtime; ?>;
        var weekly_leave = <?php echo $weekly_leave; ?>;
        var weekly_absent = <?php echo $weekly_absent ?? 0; ?>;

        var weeklyctx = document.getElementById('weeklyAttendancePieChart').getContext('2d');
        var weeklyAttendancePieChart = new Chart(weeklyctx, {
            type: 'pie',
            data: {
                labels: ['On Time', 'Late', 'Absent', 'Under Time', 'Over Time', 'On Leave'],
                datasets: [{
                    data: [weekly_ontime, weekly_late, weekly_absent, weekly_undertime, weekly_overtime, weekly_leave],
                    backgroundColor: ['#69db7c', '#ff8787', '#fa5252', '#ffcc00', '#8e44ad', '#4dabf7'],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    datalabels: {
                        formatter: (value, ctx) => {
                            if (value === 0) return ""; // Hide labels when value is 0
                            let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = ((value / sum) * 100).toFixed(1) + "%";
                            return percentage;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 14
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
       
        // Monthly ATTENDANCE REPORT
        var monthly_late = <?php echo $monthly_late; ?>;
        var monthly_ontime = <?php echo $monthly_ontime; ?>;
        var monthly_undertime = <?php echo $monthly_undertime; ?>;
        var monthly_overtime = <?php echo $monthly_overtime; ?>;
        var monthly_leave = <?php echo $monthly_leave; ?>;
        var monthly_absent = <?php echo $monthly_absent ?? 0; ?>;

        var monthlyctx = document.getElementById('monthlyAttendancePieChart').getContext('2d');
        var monthlyAttendancePieChart = new Chart(monthlyctx, {
            type: 'pie',
            data: {
                labels: ['On Time', 'Late', 'Absent', 'Under Time', 'Over Time', 'On Leave'],
                datasets: [{
                    data: [monthly_ontime, monthly_late, monthly_absent, monthly_undertime, monthly_overtime, monthly_leave],
                    backgroundColor: ['#69db7c', '#ff8787', '#fa5252', '#ffcc00', '#8e44ad', '#4dabf7'],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    datalabels: {
                        formatter: (value, ctx) => {
                            if (value === 0) return ""; // Hide labels when value is 0
                            let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = ((value / sum) * 100).toFixed(1) + "%";
                            return percentage;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 14
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });


// var ctx = document.getElementById('attendancePieChart').getContext('2d');
// var attendancePieChart = new Chart(ctx, {
//     type: 'pie',
//     data: {
//         labels: ['On Time (Present)', 'Late (Present)', 'Absent', 'On Leave'],
//         datasets: [{
//             data: [onTimeCount, lateCount, absentCount, onLeaveCount],
//             backgroundColor: ['#69db7c', '#ff8787', '#fa5252', '#4dabf7'],
//             borderWidth: 1
//         }]
//     },
//     options: {
//         plugins: {
//             datalabels: {
//                 formatter: (value, ctx) => {
//                     let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
//                     let percentage = ((value / sum) * 100).toFixed(1) + "%";
//                     return percentage;
//                 },
//                 color: '#fff',
//                 font: {
//                     weight: 'bold',
//                     size: 14
//                 }
//             }
//         }
//     },
//     plugins: [ChartDataLabels]
// });


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
            options: {
                plugins: {
                    datalabels: {
                        anchor: 'center', // Center the label inside the doughnut
                        align: 'center',            
                        color: '#fff', // White text for visibility
                        font: {
                            weight: 'bold',
                            size: 14
                        }
                    }
                },
            },
            plugins: [ChartDataLabels] // Make sure ChartDataLabels is included
        });

        var attritionCtx = document.getElementById('attritionChart').getContext('2d');
        var attritionChart = new Chart(attritionCtx, {
            type: 'bar',
            data: {
                labels: attritionData.labels,
                datasets: [{
                    data: attritionData.counts,
                    backgroundColor: attritionData.colors,
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: (value) => {
                            if (value === 0) return "";
                            return value;
                        },
                        color: '#000',
                        font: {
                            weight: 'bold',
                            size: 14
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Employees'
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
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

        document.getElementById('reportType').addEventListener('change', function() {
            // Hide all filters and chart
            document.querySelectorAll('.filter-option').forEach(el => el.style.display = 'none');
            document.getElementById('chartTitle').style.display = 'none';
            document.querySelector('.attendance-chart-container').style.display = 'none';
            
            // Show selected filter
            const selectedFilter = document.getElementById(this.value + 'Filter');
            if (selectedFilter) {
                selectedFilter.style.display = 'flex';
            }

            // Update weeks if weekly is selected
            if (this.value === 'weekly') {
                updateWeeks();
            }
        });

        // Update weeks based on selected month
        function updateWeeks() {
            const month = document.getElementById('weeklyMonth').value;
            const year = document.getElementById('weeklyYear').value;
            const weekSelect = document.getElementById('weekNumber');
            
            // Calculate number of weeks in the month
            const date = new Date(year, month - 1, 1);
            const lastDay = new Date(year, month, 0).getDate();
            const numWeeks = Math.ceil((lastDay + date.getDay()) / 7);
            
            // Update week options
            weekSelect.innerHTML = '';
            for (let i = 1; i <= numWeeks; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `Week ${i}`;
                weekSelect.appendChild(option);
            }
        }

        // Add event listeners for month/year changes in weekly filter
        document.getElementById('weeklyMonth').addEventListener('change', updateWeeks);
        document.getElementById('weeklyYear').addEventListener('change', updateWeeks);

        let attendanceChart = null;

        function createOrUpdateChart(data) {
            const ctx = document.getElementById('filteredAttendanceChart').getContext('2d');
            
            if (attendanceChart) {
                attendanceChart.destroy();
            }

            // Calculate total for percentage
            const total = Object.values(data).reduce((a, b) => Number(a) + Number(b), 0);

            // Filter out zero values and prepare data
            const labels = ['On Time', 'Late', 'Absent', 'Under Time', 'Over Time', 'On Leave'];
            const colors = ['#69db7c', '#ff8787', '#fa5252', '#ffcc00', '#8e44ad', '#4dabf7'];
            const values = [
                data.ontime || 0,
                data.late || 0,
                data.absent || 0,
                data.undertime || 0,
                data.overtime || 0,
                data.leave || 0
            ];

            // Filter out items with zero values
            const filteredData = labels.map((label, i) => ({
                label,
                value: values[i],
                color: colors[i]
            })).filter(item => item.value > 0);

            attendanceChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: filteredData.map(item => item.label),
                    datasets: [{
                        data: filteredData.map(item => item.value),
                        backgroundColor: filteredData.map(item => item.color)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map(function(label, i) {
                                            const value = data.datasets[0].data[i];
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return {
                                                text: `${label}: ${value} (${percentage}%)`,
                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        datalabels: {
                            formatter: (value) => {
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${value}\n(${percentage}%)`;
                            },
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 14
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function fetchDailyReport() {
            const date = document.getElementById('dailyDate').value;
            if (!date) {
                alert('Please select a date');
                return;
            }
            document.getElementById('chartTitle').textContent = `Attendance Report for ${new Date(date).toLocaleDateString()}`;
            document.getElementById('chartTitle').style.display = 'block';
            document.querySelector('.attendance-chart-container').style.display = 'block';
            fetchAttendanceData('daily', { date });
        }

        function fetchWeeklyReport() {
            const month = document.getElementById('weeklyMonth').value;
            const year = document.getElementById('weeklyYear').value;
            const week = document.getElementById('weekNumber').value;
            if (!month || !year || !week) {
                alert('Please select all fields');
                return;
            }
            const monthName = new Date(year, month - 1).toLocaleString('default', { month: 'long' });
            document.getElementById('chartTitle').textContent = `Attendance Report for Week ${week} of ${monthName} ${year}`;
            document.getElementById('chartTitle').style.display = 'block';
            document.querySelector('.attendance-chart-container').style.display = 'block';
            fetchAttendanceData('weekly', { month, year, week });
        }

        function fetchMonthlyReport() {
            const month = document.getElementById('monthlyMonth').value;
            const year = document.getElementById('monthlyYear').value;
            if (!month || !year) {
                alert('Please select month and year');
                return;
            }
            const monthName = new Date(year, month - 1).toLocaleString('default', { month: 'long' });
            document.getElementById('chartTitle').textContent = `Attendance Report for ${monthName} ${year}`;
            document.getElementById('chartTitle').style.display = 'block';
            document.querySelector('.attendance-chart-container').style.display = 'block';
            fetchAttendanceData('monthly', { month, year });
        }

        function fetchAttendanceData(type, params) {
            // Create URL with parameters
            const queryString = new URLSearchParams(params).toString();
            console.log(params);
            
            fetch(`get_attendance_data?type=${type}&${queryString}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        alert('Error fetching data');
                        return;
                    }
                    createOrUpdateChart(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching data');
                });
        }
    </script>
</body>
</html>

<?php include('footer.php'); ?>
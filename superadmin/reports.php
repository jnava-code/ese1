<?php include('header.php'); ?>
<?php include('includes/sideBar.php'); ?>

<?php 
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
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
        
        // Add one day to include the end date in the range
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day'));

        $weekdays = 0;
        foreach ($dateRange as $date) {
            // Check if the day is not Saturday (6) or Sunday (7)
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
                // Count weekdays from hire_date to presentDate for each employee
                $total_days += countWeekdays($hire_date, date('Y-m-d')); 
            }
            return $total_days;  // Return the sum of all days
        } else {
            echo "Error: " . mysqli_error($conn);
            return 0;
        }
    }

    // Get the present date
    $presentDate = date('Y-m-d');

    // SQL query to get the hire_date for each employee
    $sql = "
    SELECT e.employee_id, e.hire_date
    FROM employees e
    WHERE e.hire_date <= '$presentDate'";

    // Get the total absent weekdays for all employees
    $absent_days = getAbsentCount($conn, $sql);

    $late_count = getCount($conn, "SELECT count(*) as late_count FROM attendance WHERE status = 'Late'");
    $ontime_count = getCount($conn, "SELECT count(*) as ontime_count FROM attendance WHERE status = 'On Time'");
    $leave_count = getCount($conn, "SELECT count(*) as leave_count FROM leave_applications WHERE status = 'Approved'");

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
            // Add department name and color to arrays
            $departments[] = $row['dept_name'];
            $colors[] = $row['colors'];
            
            // Count the number of employees in each department dynamically
            $deptName = $row['dept_name'];
            
            // Query to count employees in the current department
            $sqlEmployeeCount = "SELECT COUNT(*) AS count FROM employees WHERE department = '$deptName'";
            $resultEmployeeCount = mysqli_query($conn, $sqlEmployeeCount);
            
            if ($resultEmployeeCount) {
                $employeeRow = mysqli_fetch_assoc($resultEmployeeCount);
                $employeeCounts[] = $employeeRow['count']; // Store the count for this department
            } else {
                $employeeCounts[] = 0; // If there's an issue with the query, default to 0 employees
            }
        }
    }

    // Convert PHP arrays to JavaScript arrays
    $departmentsJson = json_encode($departments);
    $colorsJson = json_encode($colors);
    $employeeCountsJson = json_encode($employeeCounts);

    // Get the count of male employees in each age range
    $ageRanges = ['20-29', '30-39', '40-49', '50-59', '60+'];
    $maleCounts = [];
    $femaleCounts = [];

    // Loop through the age ranges and get the counts for each gender
foreach ($ageRanges as $ageRange) {
    // For Male Employees
    if ($ageRange == '60+') {
        $sqlMale = "SELECT COUNT(*) as count FROM employees WHERE gender = 'Male' AND age >= 60";  // For the 60+ range
    } else {
        $startAge = intval(substr($ageRange, 0, 2)); // Convert to integer
        $endAge = intval(substr($ageRange, 3, 2)) + 10; // Convert to integer and add 10
        $sqlMale = "SELECT COUNT(*) as count FROM employees WHERE gender = 'Male' AND age >= $startAge AND age < $endAge";
    }
    $maleCounts[] = getCount($conn, $sqlMale);
    
    // For Female Employees
    if ($ageRange == '60+') {
        $sqlFemale = "SELECT COUNT(*) as count FROM employees WHERE gender = 'Female' AND age >= 60";  // For the 60+ range
    } else {
        $startAge = intval(substr($ageRange, 0, 2)); // Convert to integer
        $endAge = intval(substr($ageRange, 3, 2)) + 10; // Convert to integer and add 10
        $sqlFemale = "SELECT COUNT(*) as count FROM employees WHERE gender = 'Female' AND age >= $startAge AND age < $endAge";
    }
    $femaleCounts[] = getCount($conn, $sqlFemale);
}


    // Convert PHP arrays to JavaScript arrays
    $maleCountsJson = json_encode($maleCounts);
    $femaleCountsJson = json_encode($femaleCounts);
    $ageRangesJson = json_encode($ageRanges);

    
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
            <div class="reports">
                <div style="width: 100%; max-width: 500px;"> <!-- This container helps control the chart's maximum size -->
                    <h1>Attendance Report</h1>
                    <canvas id="attendancePieChart"></canvas>
                </div>

                <div style="width: 100%; max-width: 500px;"> <!-- This container helps control the chart's maximum size -->
                    <h1>Employees Report</h1>
                    <canvas id="departmentChart" width="400" height="400"></canvas>
                </div>

                <div style="width: 100%; max-width: 500px;"> <!-- This container helps control the chart's maximum size -->
                    <h1>Employees by Age and Gender Report</h1>
                    <canvas id="ageGenderChart" width="600" height="400"></canvas>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
      // ATTENDANCE REPORT
        var lateCount = <?php echo $late_count; ?>; 
        var onTimeCount = <?php echo $ontime_count; ?>; 
        var absentCount = <?php echo $total_absent_days; ?>; 
        var onLeaveCount = <?php echo $leave_count; ?>;  

        // Calculate total
        var total = lateCount + onTimeCount + absentCount + onLeaveCount;

        // Calculate percentages
        var latePercentage = ((lateCount / total) * 100).toFixed(1);
        var onTimePercentage = ((onTimeCount / total) * 100).toFixed(1);
        var absentPercentage = ((absentCount / total) * 100).toFixed(1);
        var onLeavePercentage = ((onLeaveCount / total) * 100).toFixed(1);

        var ctx = document.getElementById('attendancePieChart').getContext('2d');
        var attendancePieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [
                    `On Time (Present) ${onTimePercentage}%`, 
                    `Late (Present) ${latePercentage}%`, 
                    `Absent ${absentPercentage}%`, 
                    `On Leave ${onLeavePercentage}%`
                ],
                datasets: [{
                    data: [onTimeCount, lateCount, absentCount, onLeaveCount],
                    backgroundColor: ['#69db7c', '#ff8787', '#fa5252', '#4dabf7'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                let dataset = tooltipItem.dataset.data;
                                let value = dataset[tooltipItem.dataIndex];
                                let percentage = ((value / total) * 100).toFixed(1);
                                return `${percentage}%`;
                            }
                        }
                    },
                    legend: {
                        display: true, 
                        position: 'bottom'
                    },
                    datalabels: {
                        display: true,
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        formatter: function(value, context) {
                            let percentage = ((value / total) * 100).toFixed(1);
                            return `${percentage}%`; // Display percentage in the center of each slice
                        }
                    }
                }
            }
        });

        var departments = <?php echo $departmentsJson; ?>;
        var colors = <?php echo $colorsJson; ?>;
        var employees = <?php echo $employeeCountsJson; ?>

        var departmentCtx = document.getElementById('departmentChart').getContext('2d');
        var departmentChart = new Chart(departmentCtx, {
            type: 'doughnut',
            data: {
                labels: departments, // Labels for departments
                datasets: [{
                    data: employees, // Department data
                    backgroundColor: colors, // Colors for the departments
                    borderWidth: 1
                }]
            },
        });

        // Data from PHP (Dynamic data passed from PHP to JavaScript)
        var ageRanges = <?php echo $ageRangesJson; ?>;
        var maleCounts = <?php echo $maleCountsJson; ?>;
        var femaleCounts = <?php echo $femaleCountsJson; ?>;

        // Set up the Line Chart
        var ctx = document.getElementById('ageGenderChart').getContext('2d');
        var ageGenderChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ageRanges, // Age ranges on X-axis
                datasets: [{
                    label: 'Male Employees',
                    data: maleCounts, // Male employee count data
                    borderColor: '#4b8bf5', // Blue color for Male line
                    fill: false,
                    tension: 0.1,
                    borderWidth: 2
                }, {
                    label: 'Female Employees',
                    data: femaleCounts, // Female employee count data
                    borderColor: '#f55d7a', // Red color for Female line
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

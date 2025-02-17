<?php
include('header.php');
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Query for total employees
$totalEmployeesQuery = "SELECT COUNT(*) AS total_employees FROM employees WHERE e_status = 1";
$totalEmployeesResult = $conn->query($totalEmployeesQuery);
$totalEmployees = $totalEmployeesResult->fetch_assoc()['total_employees'];

// Query for new hires (employees hired in the current year)
$currentYear = date('Y');
$newHiresQuery = "SELECT COUNT(*) AS new_hires FROM employees WHERE YEAR(hire_date) = $currentYear";
$newHiresResult = $conn->query($newHiresQuery);
$newHires = $newHiresResult->fetch_assoc()['new_hires'];

// Query for pending leave requests
$pendingLeaveQuery = "SELECT COUNT(*) AS pending_leaves FROM leave_applications WHERE status = 'Pending'";
$pendingLeaveResult = $conn->query($pendingLeaveQuery);
$pendingLeaves = $pendingLeaveResult->fetch_assoc()['pending_leaves'];

// Query for department distribution
$departmentDistributionQuery = "SELECT d.dept_name, COUNT(e.id) AS employee_count 
                                FROM departments d 
                                LEFT JOIN employees e ON d.dept_name = e.department 
                                GROUP BY d.dept_name";
$departmentDistributionResult = $conn->query($departmentDistributionQuery);
$departmentData = [];
while ($row = $departmentDistributionResult->fetch_assoc()) {
    $departmentData[] = $row;
}

// Query for male employees
$maleEmployeesQuery = "SELECT COUNT(*) AS male_employees FROM employees WHERE e_status = 1 AND gender = 'Male'";
$maleEmployeesResult = $conn->query($maleEmployeesQuery);
$maleEmployees = $maleEmployeesResult->fetch_assoc()['male_employees'];

// Query for female employees
$femaleEmployeesQuery = "SELECT COUNT(*) AS female_employees FROM employees WHERE e_status = 1 AND gender = 'Female'";
$femaleEmployeesResult = $conn->query($femaleEmployeesQuery);
$femaleEmployees = $femaleEmployeesResult->fetch_assoc()['female_employees'];

// Query for attendance data
$attendanceQuery = "SELECT date, COUNT(*) AS present_count 
                    FROM attendance 
                    WHERE status = 'Present' 
                    GROUP BY date 
                    ORDER BY date DESC 
                    LIMIT 7";
$attendanceResult = $conn->query($attendanceQuery);
$attendanceData = [];
while ($row = $attendanceResult->fetch_assoc()) {
    $attendanceData[] = $row;
}

// Query for attrition forecasting data
$attritionQuery = "SELECT employee_id, attrition_probability 
                   FROM attrition_forecasting 
                   ORDER BY prediction_date DESC 
                   LIMIT 5";
$attritionResult = $conn->query($attritionQuery);
$attritionData = [];
while ($row = $attritionResult->fetch_assoc()) {
    $attritionData[] = $row;
}
?>

<!-- Include Sidebar -->
<?php include('includes/sideBar.php'); ?>

<style>
    body, html {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden; /* Prevent scrolling */
        font-family: Arial, sans-serif;
        background-color: #f0f0f0;
    }
    .main-content {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    #dashboard {
        flex: 1;
        height: 100%; /* Use full height */
        width: 100%; /* Use full width */
        overflow: hidden; /* Prevent internal scrolling */
        padding: 5px; /* Reduced padding */
        box-sizing: border-box;
        display: grid;
        grid-template-rows: auto 1fr;
        gap: 5px; /* Reduced gap */
    }
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); /* Adjusted min-width */
        gap: 5px; /* Reduced gap */
    }
    .dashboard-card {
        background: #fff;
        padding: 5px; /* Reduced padding */
        border-radius: 8px;
        border: 1px solid #ddd; /* Add border */
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        text-align: center;
        font-size: 10px; /* Smaller font size */
    }
    .dashboard-card .icon {
        font-size: 18px; /* Adjusted icon size */
        margin-bottom: 5px;
    }
    .dashboard-card .title {
        font-size: 12px; /* Custom font size for title */
        font-weight: bold;
        margin: 5px 0;
    }
    .dashboard-card .value {
        font-size: 14px; /* Custom font size for value */
    }
    .chart-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 5px; /* Reduced gap */
        height: calc(100% - 150px); /* Adjust height to fit within the page */
    }
    .chart {
        background: #fff;
        padding: 10px; /* Reduced padding */
        border-radius: 8px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        font-size: 10px; /* Smaller font size */
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px; /* Reduced margin */
    }
    table, th, td {
        border: 1px solid #ddd;
    }
    th, td {
        padding: 5px;
        text-align: left;
    }
    th {
        background-color: #f4f4f4;
    }
</style>

<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>DASHBOARD</h2>
        <div class="dashboard-cards">
            <!-- Total Employees -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="title">Total Employees</div>
                <div class="value"><?php echo $totalEmployees; ?></div>
            </div>
            <!-- New Hires -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-user-plus"></i></div>
                <div class="title">New Hires</div>
                <div class="value"><?php echo $newHires; ?></div>
            </div>
            <!-- Department Distribution -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-building"></i></div>
                <div class="title">Departments</div>
                <div class="value"><?php echo count($departmentData); ?></div>
            </div>
            <!-- Male Employees -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-male"></i></div>
                <div class="title">Male Employees</div>
                <div class="value"><?php echo $maleEmployees; ?></div>
            </div>
            <!-- Female Employees -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-female"></i></div>
                <div class="title">Female Employees</div>
                <div class="value"><?php echo $femaleEmployees; ?></div>
            </div>
            <!-- Pending Leave Requests -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-calendar-times"></i></div>
                <div class="title">Leave Requests</div>
                <div class="value"><?php echo $pendingLeaves; ?> Pending</div>
            </div>
        </div>

        <!-- Charts and Tables Section -->
        <div class="chart-container">
            <!-- Department Distribution Chart -->
            <div class="chart">
                <h3>Department Distribution</h3>
                <canvas id="departmentChart"></canvas>
            </div>

            <!-- Attendance Chart -->
            <div class="chart">
                <h3>Attendance (Last 7 Days)</h3>
                <canvas id="attendanceChart"></canvas>
            </div>

            <!-- Attrition Forecasting Table -->
            <div class="chart">
                <h3>Attrition Forecasting (Top 5 Employees)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Attrition Probability</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attritionData as $row): ?>
                            <tr>
                                <td><?php echo $row['employee_id']; ?></td>
                                <td><?php echo $row['attrition_probability']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Gender Distribution Chart -->
            <div class="chart">
                <h3>Gender Distribution</h3>
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </section>
</main>

<!-- Include Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gender Distribution Chart
    const genderData = {
        labels: ['Male', 'Female'],
        datasets: [{
            label: 'Gender Distribution',
            data: [<?php echo $maleEmployees; ?>, <?php echo $femaleEmployees; ?>],
            backgroundColor: [
                '#36a2eb', // Blue for Male
                '#ff6384'  // Pink for Female
            ],
            borderWidth: 1
        }]
    };

    const ctx3 = document.getElementById('genderChart').getContext('2d');
    const genderChart = new Chart(ctx3, {
        type: 'pie',
        data: genderData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Department Distribution Chart
    const departmentData = <?php echo json_encode($departmentData); ?>;
    const departmentLabels = departmentData.map(dept => dept.dept_name);
    const departmentCounts = departmentData.map(dept => dept.employee_count);

    const ctx = document.getElementById('departmentChart').getContext('2d');
    const departmentChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: departmentLabels,
            datasets: [{
                label: 'Employees per Department',
                data: departmentCounts,
                backgroundColor: [
                    '#ff8787', '#f783ac', '#da77f2', '#9775fa', '#748ffc', '#4dabf7', '#3bc9db'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Attendance Chart
    const attendanceData = <?php echo json_encode($attendanceData); ?>;
    const attendanceLabels = attendanceData.map(entry => entry.date);
    const attendanceCounts = attendanceData.map(entry => entry.present_count);

    const ctx2 = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: attendanceLabels,
            datasets: [{
                label: 'Present Employees',
                data: attendanceCounts,
                borderColor: '#4dabf7',
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Number of Employees'
                    }
                }
            }
        }
    });
</script>

<?php include('footer.php'); ?>
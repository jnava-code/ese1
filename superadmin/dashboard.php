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
    .chart-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
    }
    .chart {
        flex: 1 1 calc(50% - 20px); /* Two items per row */
        min-width: 200px; /* Adjusted to be a bit smaller */
        background: #fff;
        padding: 20px; /* Adjusted to be a bit smaller */
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        box-sizing: border-box; /* Ensure padding and border are included in the element's total width and height */
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    table, th, td {
        border: 1px solid #ddd;
    }
    th, td {
        padding: 10px;
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
                <h3>Total Employees</h3>
                <p><?php echo $totalEmployees; ?></p>
            </div>
            <!-- New Hires -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-user-plus"></i></div>
                <h3>New Hires</h3>
                <p><?php echo $newHires; ?></p>
            </div>
            <!-- Department Distribution -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-building"></i></div>
                <h3>Departments</h3>
                <p><?php echo count($departmentData); ?></p>
            </div>
                        <!-- Male Employees -->
                        <div class="dashboard-card">
                <div class="icon"><i class="fas fa-male"></i></div>
                <h3>Male Employees</h3>
                <p><?php echo $maleEmployees; ?></p>
            </div>
            <!-- Female Employees -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-female"></i></div>
                <h3>Female Employees</h3>
                <p><?php echo $femaleEmployees; ?></p>
            </div>
            <!-- Pending Leave Requests -->
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-calendar-times"></i></div>
                <h3>Leave Requests</h3>
                <p><?php echo $pendingLeaves; ?> Pending</p>
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
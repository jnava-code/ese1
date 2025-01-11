<?php
include('header.php');
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Query for total employees
$totalEmployeesQuery = "SELECT COUNT(*) AS total_employees FROM employees WHERE e_status = 1";
$totalEmployeesResult = $conn->query($totalEmployeesQuery);
$totalEmployees = $totalEmployeesResult->fetch_assoc()['total_employees'];

// Query for pending leave requests
$pendingLeaveQuery = "SELECT COUNT(*) AS pending_leaves FROM leave_applications WHERE status = 'Pending'";
$pendingLeaveResult = $conn->query($pendingLeaveQuery);
$pendingLeaves = $pendingLeaveResult->fetch_assoc()['pending_leaves'];


?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>


<style>
    /* nilipat ko sa "css/style.css" yung code dito */
</style>



<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>DASHBOARD</h2>
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <h3>Total Employees</h3>
                <p><?php echo $totalEmployees; ?></p>
            </div>
            <div class="dashboard-card">
                <div class="icon"><i class="fas fa-calendar-times"></i></div>
                <h3>Leave Requests</h3>
                <p><?php echo $pendingLeaves; ?> Pending</p>
            </div>
        </div>
    </section>
</main>
<script>
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function (event) {
        const parent = this.parentElement;

        // Prevent the link's default behavior
        event.preventDefault();

        // Toggle the active class
        parent.classList.toggle('active');
    });
});

</script>

<?php include('footer.php'); ?>

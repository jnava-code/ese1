<?php
include('header.php'); // Admin header file

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the filter from the URL or default to 'all'
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the SQL query with JOIN
$sql = "
    SELECT 
        leave_applications.*, 
        CONCAT(employees.first_name, ' ', employees.last_name) AS employee_name 
    FROM leave_applications 
    JOIN employees 
    ON leave_applications.employee_id = employees.employee_id";
if ($status_filter != 'all') {
    $sql .= " WHERE leave_applications.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

$result = mysqli_query($conn, $sql);
?>


<nav class="sidebar">
    <ul>
        <li><a href="./dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="./employees"><i class="fas fa-user-friends"></i> Employees Profile</a></li>
        <li class="dropdown">
            <a href="#attendance-dropdown" class="dropdown-toggle"><i class="fas fa-calendar-check"></i> Attendance Management</a>
            <ul class="dropdown-menu" id="attendance-dropdown">
                <li><a href="./daily-attendance">Daily Attendance</a></li>
                <li><a href="./monthly-attendance">Monthly Attendance</a></li>
            </ul>
        </li>
        <li><a href="./leave"><i class="fas fa-paper-plane"></i> Request Leave</a></li>
        <li><a href="./predict"><i class="fas fa-chart-line"></i> Prediction</a></li>
        <li><a href="./recommendation"><i class="fas fa-lightbulb"></i> Recommendation</a></li>
        <li><a href="./reports"><i class="fas fa-file-alt"></i> Reports</a></li>
        <li><a href="./performance-evaluation"><i class="fas fa-trophy"></i> Performance</a></li>
        <li><a href="./satisfaction"><i class="fas fa-smile"></i> Satisfaction</a></li>
    </ul>
</nav>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>LEAVE APPLICATION REQUESTS</h2>

        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <a href="?status=all" class="<?php echo $status_filter == 'all' ? 'active' : ''; ?>">All</a>
            <a href="?status=Approved" class="<?php echo $status_filter == 'Approved' ? 'active' : ''; ?>">Accepted</a>
            <a href="?status=Pending" class="<?php echo $status_filter == 'Pending' ? 'active' : ''; ?>">Pending</a>
            <a href="?status=Rejected" class="<?php echo $status_filter == 'Rejected' ? 'active' : ''; ?>">Rejected</a>
        </div>

        <!-- Styled Leave Requests Table -->
        <table id="myTable" class="styled-table">
    <thead>
        <tr>
            <th>Employee Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <form action="./process_leave" method="POST">
                        <input type="hidden" name="leave_id" value="<?php echo $row['leave_id']; ?>">
                        <button type="submit" name="action" value="approve" 
                            <?php echo ($row['status'] != 'Pending') ? 'disabled' : ''; ?>>
                            Approve
                        </button>
                        <button type="submit" name="action" value="reject" 
                            <?php echo ($row['status'] != 'Pending') ? 'disabled' : ''; ?>>
                            Reject
                        </button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
    </section>
</main>

<?php
mysqli_close($conn); // Close database connection
include('footer.php'); // Admin footer file
?>

<!-- CSS -->
<style>
/* Reuse the same styles from the Attendance table for consistency */

/* Style for Table */
#attendance-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

#attendance-table th, #attendance-table td {
    padding: 12px;
    text-align: left;
}

#attendance-table th {
    background-color: #4CAF50;
    color: white;
}

#attendance-table tr:nth-child(even) {
    background-color: #f2f2f2;
}

#attendance-table tr:hover {
    background-color: #ddd;
}

/* Buttons inside the table */
button {
    padding: 8px 12px;
    font-size: 14px;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button[name="action"][value="approve"] {
    background-color: #4CAF50;
}

button[name="action"][value="reject"] {
    background-color: #e74c3c;
}

button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

/* Filter Buttons */
.filter-buttons {
    margin-bottom: 20px;
}

.filter-buttons a {
    padding: 8px 16px;
    text-decoration: none;
    color: white;
    background-color: #b01515;
    border-radius: 4px;
    margin-right: 10px;
}

.filter-buttons a:hover {
    background-color: #bd2828;
}

.filter-buttons a.active {
    background-color: #de0d0d;
}
    /* Sidebar Dropdown */
    .sidebar ul .dropdown {
    position: relative;
}

.sidebar ul .dropdown .dropdown-toggle {
    cursor: pointer;
}

.sidebar ul .dropdown .dropdown-menu {
    display: none; /* Hide by default */
    list-style: none;
    padding: 0;
    margin: 0;
    background-color: #a83a3a;
}

.sidebar ul .dropdown .dropdown-menu li a {
    padding-left: 2rem; /* Indent for dropdown items */
    display: block;
    color: #fff;
}

.sidebar ul .dropdown .dropdown-menu li a:hover {
    background-color: #c45b5b;
}

/* Show dropdown menu when the parent is active */
.sidebar ul .dropdown.active .dropdown-menu {
    display: block; /* Show the dropdown */
}

/* Optional styling for active links */
.sidebar ul li a.active {
    background-color: #c45b5b;
}
</style>
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

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready( function () {
    $('#myTable').DataTable();
  });
</script>
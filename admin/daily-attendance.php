<?php include('header.php'); ?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>


<?php
// Database connection
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "esetech";  

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$employee_id = $_GET['employee_id'] ?? ''; 
$start_date = $_GET['start_date'] ?? ''; 
$end_date = $_GET['end_date'] ?? ''; 

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; 
$offset = ($page - 1) * $limit;

$conditions = [];
if ($employee_id) {
    $conditions[] = "attendance.employee_id = '" . mysqli_real_escape_string($conn, $employee_id) . "'"; 
}
if ($start_date) {
    $conditions[] = "attendance.date >= '" . mysqli_real_escape_string($conn, $start_date) . "'"; 
}
if ($end_date) {
    $conditions[] = "attendance.date <= '" . mysqli_real_escape_string($conn, $end_date) . "'"; 
}
$where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$today = date("Y-m-d");
$sql = "
    SELECT     
        e.employee_id,
        CONCAT(e.first_name, ' ', e.middle_name, ' ', e.last_name) AS full_name,
        a.date, 
        a.clock_in_time, 
        a.clock_out_time,
        IFNULL(la.status, 'Absent') AS leave_status
    FROM attendance a
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    LEFT JOIN leave_applications la ON a.employee_id = la.employee_id
    WHERE a.date = '$today'
    $where 
    ORDER BY a.date DESC 
    LIMIT $limit OFFSET $offset";

// $sql = "
//     SELECT 
//         CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name,
//         a.employee_id,
//         a.date, 
//         a.clock_in_time, 
//         a.clock_out_time,
//         IFNULL(la.status, 'Absent') AS leave_status
//     FROM attendance a
//     LEFT JOIN employees e ON a.employee_id = e.employee_id
//     LEFT JOIN attendance la ON a.employee_id = la.employee_id
//     $where 
//     ORDER BY a.date DESC 
//     LIMIT $limit OFFSET $offset";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$countQuery = "SELECT COUNT(*) as total FROM attendance a $where";
$countResult = mysqli_query($conn, $countQuery);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $limit);

// Handle the display of attendance records here

?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>ATTENDANCE MONITORING</h2>

        <form method="POST" class="evaluation-form">
            <div class="form-group">
                <table id="myTable" class="table table-striped table-bordered">
                <thead>
    <tr>
        <th>Employee ID</th> <!-- New Column for Employee ID -->
        <th>Employee Name</th>
        <th>Date</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Status</th>
    </tr>
</thead>
<tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['employee_id']); ?></td> <!-- Display Employee ID -->
            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
            <td><?php echo $row['date']; ?></td>
            <td><?php echo $row['clock_in_time'] ? $row['clock_in_time'] : '-'; ?></td>
            <td><?php echo $row['clock_out_time'] ? $row['clock_out_time'] : '-'; ?></td>
            <td><?php echo $row['leave_status']; ?></td> <!-- Use leave_status directly here -->
        </tr>
    <?php endwhile; ?>
</tbody>
                </table>
            </div>
        </form>
    </section>
</main>

<script>
document.querySelector('form').addEventListener('submit', function (e) {
    e.preventDefault();
    const params = new URLSearchParams(new FormData(e.target));
    const query = params.toString();
    fetch(`fetch_attendance.php?${query}`)
        .then(response => response.text())
        .then(data => {
            document.querySelector("#attendance-table tbody").innerHTML = data;
        })
        .catch(error => console.error('Error fetching attendance:', error));
});
</script>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!-- CSS -->
<style>
/* Style for Table */
.evaluation-form {
        max-width: 1500px;
        margin: 0 auto;
        padding: 20px;
        border-radius: 8px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .evaluation-form .form-group {
        margin-bottom: 15px;
    }

    .evaluation-form label {
        font-weight: bold;
        font-size: 14px;
        color: #333;
    }

    .evaluation-form input,
    .evaluation-form select,
    .evaluation-form textarea {
        width: 100%;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .evaluation-form input[type="number"] {
        width: 60px;
    }

    .evaluation-form button {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }

    .evaluation-form button:hover {
        background-color: #0056b3;
    }
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
<?php 
include('user_header.php'); 

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ensure employee is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php"); 
    exit();
}

$employee_id = $_SESSION['employee_id']; // Replace with the logged-in employee's ID

// Fetch satisfaction surveys for the employee
$sql = "SELECT survey_date, overall_rating, rating_description
        FROM job_satisfaction_surveys 
        WHERE employee_id = ?
        ORDER BY survey_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id); // Bind the employee ID to the query
$stmt->execute();
$result = $stmt->get_result();

?>

<?php include('includes/sideBar.php'); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>Satisfaction Survey History</h2>

        <style>
            table {
                border: 1px solid #ddd;
                border-collapse: collapse;
                width: 100%;
                margin: 20px 0;
            }
            th, td {
                text-align: left;
                padding: 8px;
                border: 1px solid #ddd;
            }
            th {
                background-color: #f4f4f4;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            @media (max-width: 768px) {
                table {
                    display: block;
                    overflow-x: auto;
                    white-space: nowrap;
                }
                table thead, table tbody {
                    font-size: 12px;
                }
            }
        </style>

        <?php if ($result->num_rows > 0): ?>
            <table id="satisfactionTable">
                <thead>
                    <tr>
                        <th>Survey Date</th>
                        <th>Overall Rating</th>
                        <th>Rating Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['survey_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['overall_rating']); ?></td>
                            <td><?php echo htmlspecialchars($row['rating_description']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No satisfaction surveys found.</p>
        <?php endif; ?>

    </section>
</main>

<script>
  $(document).ready( function () {
    $('#satisfactionTable').DataTable();
  });
</script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<?php
$stmt->close();
mysqli_close($conn);  
include('user_footer.php');
?>
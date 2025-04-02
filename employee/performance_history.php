<?php 
include('user_header.php'); 

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ./");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// Fetch performance evaluations for the logged-in employee
$query = "
    SELECT 
        evaluation_date, 
        overall_score, 
        comments, 
        remarks 
    FROM performance_evaluations 
    WHERE employee_id = $employee_id 
    ORDER BY evaluation_date DESC
";
$result = mysqli_query($conn, $query);

?>

<?php include('includes/sideBar.php'); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>Performance Evaluation History</h2>

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

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <table id="performanceTable">
                <thead>
                    <tr>
                        <th>Evaluation Date</th>
                        <th>Overall Score</th>
                        <th>Comments</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['evaluation_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['overall_score']); ?></td>
                            <td><?php echo htmlspecialchars($row['comments']); ?></td>
                            <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No performance evaluations found.</p>
        <?php endif; ?>

    </section>
</main>

<script>
  $(document).ready( function () {
    $('#performanceTable').DataTable();
  });
</script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<?php
mysqli_close($conn);  
include('user_footer.php');
?>
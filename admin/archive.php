<?php
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    function executeQuery($conn, $sql, $types = null, $params = []) {
        $stmt = mysqli_prepare($conn, $sql);
        if ($types && $params) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        if (!mysqli_stmt_execute($stmt)) {
            echo "Error: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }

    if (isset($_GET['restore_id'])) {
        $id = $_GET['restore_id'];
        $sql = "UPDATE employees SET is_archived = 0 WHERE id=?";
        executeQuery($conn, $sql, 'i', [$id]);
    }

    $sql_archived = "SELECT * FROM employees WHERE is_archived = 1 OR employment_status = 'resigned' ORDER BY id ASC";
    $result_archived = mysqli_query($conn, $sql_archived);

    include('header.php');
?>

<?php include('includes/sideBar.php'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<main class="main-content">
    <section id="dashboard">
        <h2 class="text-2xl font-bold mb-6">ARCHIVED EMPLOYEES</h2>
        
        <div class="card">
            <div class="card-header">
                <h3>Archived Employee List</h3>
            </div>
            <div class="card-body">
                <table id="myTable" class="employee-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $counter = 1;
                            while ($employee = mysqli_fetch_assoc($result_archived)): 
                        ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo $employee['employee_id']; ?></td>
                                <td>
                                    <?php
                                        $full_name = trim($employee['first_name'] . ' ' . $employee['middle_name'] . ' ' . $employee['last_name']);
                                        if (!empty($employee['suffix'])) {
                                            $full_name .= ', ' . $employee['suffix'];
                                        }
                                        echo $full_name;
                                    ?>
                                </td>
                                <td><?php echo $employee['position']; ?></td>
                                <td><?php echo $employee['department']; ?></td>
                                <td><?php echo $employee['email']; ?></td>
                                <td><?php echo $employee['employment_status']; ?></td>
                                <td class="action-buttons">
                                    <a href="?restore_id=<?php echo $employee['id']; ?>" class="btn btn-success" onclick="return confirm('Are you sure you want to restore this employee?');">Restore</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#myTable').DataTable();
});
</script>

<?php include('footer.php'); ?> 
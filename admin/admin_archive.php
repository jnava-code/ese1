<?php
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    include('header.php');
    if (!isset($_SESSION['superadmin'])) {
        // Redirect to the dashboard if the user is not a superadmin
        header("Location: ./dashboard");
        exit();
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
        $sql = "UPDATE admin SET is_archived = 0 WHERE id=?";
        executeQuery($conn, $sql, 'i', [$id]);
    }

    $sql_archived = "SELECT * FROM admin WHERE is_archived = 1  ORDER BY id ASC";
    $result_archived = mysqli_query($conn, $sql_archived);
?>

<?php include('includes/sideBar.php'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<main class="main-content">
    <section id="dashboard">
        <h2 class="text-2xl font-bold mb-6">ARCHIVED ADMINS</h2>
        
        <div class="card">
            <div class="card-header">
                <h3>Archived Admin List </h3>
            </div>
            <div class="card-body">
                <table id="myTable" class="employee-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Contact Number</th>
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
                                <td>
                                    <?php
                                        $full_name = trim($employee['first_name'] . ' ' . $employee['last_name']);
                                        echo $full_name;
                                    ?>
                                </td>
                                <td><?php echo $employee['username']; ?></td>
                                <td><?php echo $employee['email']; ?></td>
                                <td><?php echo $employee['contact_number']; ?></td>
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

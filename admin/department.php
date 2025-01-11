<?php
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get unique departments for dropdown
    $dept_sql = "SELECT DISTINCT department FROM (
        SELECT department FROM employees WHERE is_archived = 0
        UNION
        SELECT 'Admin' as department
        UNION
        SELECT 'Chemical' as department
        UNION
        SELECT 'Procurement' as department
        UNION
        SELECT 'Sales' as department
        UNION
        SELECT 'Sales & Marketing' as department
        UNION
        SELECT 'Technical' as department
        UNION
        SELECT 'Technical Sales' as department
        UNION
        SELECT 'Work Order' as department
    ) as departments 
    ORDER BY department ASC";
    $dept_result = mysqli_query($conn, $dept_sql);

    // Get employees with optional department filter
    $selected_dept = isset($_GET['department']) ? $_GET['department'] : '';
    
    $sql = "SELECT * FROM employees WHERE is_archived = 0";
    if (!empty($selected_dept)) {
        $sql .= " AND department = '$selected_dept'";
    }
    $sql .= " ORDER BY last_name ASC";
    
    $result = mysqli_query($conn, $sql);

    include('header.php');
?>

<?php include('includes/sideBar.php'); ?>

<main class="main-content">
    <section id="dashboard">
        <h2 class="text-2xl font-bold mb-6">DEPARTMENT EMPLOYEES</h2>
        
        <!-- Department Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="form-inline justify-content-between align-items-center">
                    <div class="form-group" style="display: flex; gap: 10px;">
                        <label for="department">Filter by Department:</label>
                        <select name="department" class="form-control" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            <?php while ($dept = mysqli_fetch_assoc($dept_result)): ?>
                                <option value="<?php echo $dept['department']; ?>" 
                                    <?php echo ($selected_dept == $dept['department']) ? 'selected' : ''; ?>>
                                    <?php echo $dept['department']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Employee List -->
        <div class="card">
            <div class="card-header">
                <h3><?php echo empty($selected_dept) ? 'All Employees' : $selected_dept . ' Department Employees'; ?></h3>
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
                            while ($employee = mysqli_fetch_assoc($result)): 
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
                                    <a href="./edit_employee?id=<?php echo $employee['id']; ?>" class="btn btn-warning">Edit</a>
                                    <a href="?delete_id=<?php echo $employee['id']; ?>" class="btn btn-danger" 
                                       onclick="return confirm('Are you sure you want to Archive this employee?');">Archive</a>
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
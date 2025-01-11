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


<?php
if (isset($_POST['add_dept'])) {
    $dept_name = $_POST['dept_name'];
    $is_archived = 0;

    // Use prepared statement to avoid SQL injection
    $selectDeptSql = "SELECT * FROM departments WHERE dept_name = ?";
    $stmt = $conn->prepare($selectDeptSql);
    $stmt->bind_param('s', $dept_name);
    $stmt->execute();
    $deptSelectResult = $stmt->get_result();

    if ($deptSelectResult->num_rows > 0) {
        // Department already exists
        $message = '<p style="color: red;">The department already exists.</p>';
    } else {
        // Insert new department
        $sql = "INSERT INTO departments (dept_name, is_archived) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $dept_name, $is_archived);  // 's' for string, 'i' for integer
        $deptSelectResult = $stmt->execute();

        if ($deptSelectResult) {
            // Department successfully added
            $message = '<p style="color: green;">The department has been successfully added.</p>';
        } else {
            // Error in insertion
            $message = '<p style="color: red;">Error adding department.</p>';
        }
    }
}


if (isset($_POST['delete_dept'])) {
    // Get the department ID from the hidden input field
    $dept_id = $_POST['dept_id'];

    // Delete query
    $deleteSql = "DELETE FROM departments WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param('i', $dept_id);

    if ($stmt->execute()) {
        // If deletion is successful, show a success message
        $message = '<p style="color: green;">Department successfully deleted.</p>';
    } else {
        // If there's an error in deletion, show an error message
        $message = '<p style="color: red;">Error deleting department.</p>';
    }
}

if (isset($_POST['archive_name'])) {
    // Get the department ID from the hidden input field
    $dept_id = $_POST['dept_id'];

    // Update query to archive the department
    $archiveSql = "UPDATE departments SET is_archived = 1 WHERE id = ?";
    $stmt = $conn->prepare($archiveSql);
    $stmt->bind_param('i', $dept_id);

    if ($stmt->execute()) {
        // If archiving is successful, show a success message
        $message = '<p style="color: green;">Department has been archived successfully.</p>';
    } else {
        // If there's an error in archiving, show an error message
        $message = '<p style="color: red;">Error archiving department.</p>';
    }
}

// Check if the restore action is triggered
if (isset($_POST['restore_name'])) {
    // Get the department ID from the hidden input field
    $dept_id = $_POST['dept_id'];

    // Update query to restore the department
    $restoreSql = "UPDATE departments SET is_archived = 0 WHERE id = ?";
    $stmt = $conn->prepare($restoreSql);
    $stmt->bind_param('i', $dept_id);

    // Execute the query
    if ($stmt->execute()) {
        // If restoring is successful, show a success message
        $message = '<p style="color: green;">Department has been restored successfully.</p>';
    } else {
        // If there's an error in restoring, show an error message
        $message = '<p style="color: red;">Error restoring department.</p>';
    }
}
?>

<main class="main-content">
    <section id="dashboard">
        <div class="department-and-button">
            <h2 class="text-2xl font-bold mb-6">DEPARTMENTS</h2> 
            <a href="#" id="add-dept-btn" class="btn btn-danger">ADD DEPARTMENT</a>
        </div>

        <div class="add-dept-content">
            <h3>ADD DEPARTMENT</h3>
            <form method="POST" class="label-and-input">
                <label for="dept_name">Department Name: </label>
                <input id="dept_name" type="text" value="" required>

                <div class="action-buttons">
                    <input class="btn" type="submit" name="add_dept" value="Add">
                </div>         
            </form>
        </div>
        <div class="dept-background"></div>
        <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Departments</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $deptSql = "SELECT * FROM departments ORDER BY dept_name ASC";
                    $deptResult = mysqli_query($conn, $deptSql);
                    if ($deptResult) {
                        while ($row = mysqli_fetch_assoc($deptResult)) {
                ?>
                <tr>
                    <td><?php echo $row['dept_name']; ?></td>
                    <td class="action-buttons">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="dept_id" value="<?php echo $row['id']; ?>" />
                            <?php echo $row['is_archived'] == 0 
                            ? "<button name='edit_dept' class='btn btn-warning'>Edit</button>
                                <button name='delete_dept' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this department?\");'>Delete</button>
                                <button name='archive_name' class='btn btn-warning'>Archive</button>"
                            : "<button name='edit_dept' class='btn btn-warning'>Edit</button>
                                <button name='delete_dept' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this department?\");'>Delete</button>
                                <button name='restore_name' class='btn btn-restore'>Restore</button>";
                        ?>
                        </form>
                    </td>
                </tr>
                <?php 
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>
    
        <h2 class="text-2xl font-bold mb-6">EMPLOYEES BY DEPARTMENT</h2> 
        <!-- Employee List -->
        <div class="card">
            <div class="card-header">
                <h3><?php echo empty($selected_dept) ? 'All Employees' : $selected_dept . ' Department Employees'; ?></h3>

                <form method="GET" class="form-inline justify-content-between align-items-center">
                    <!-- <div class="form-group" style="display: flex; gap: 10px;"> -->
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
                    <!-- </div> -->
                </form>
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
                                <td class="employee_id_display"><?php echo $employee['employee_id']; ?></td>
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
    const employeeIdDisplay = document.querySelectorAll(".employee_id_display");
    const addDeptBtn = document.getElementById("add-dept-btn");
    const addDeptCont = document.querySelector(".add-dept-content");
    const deptBack =document.querySelector(".dept-background");

    if(employeeIdDisplay) {
        employeeIdDisplay.forEach(display => {
            let validDisplayValue = display.textContent.replace(/[^0-9]/g, '');
            // Apply format: 00-000
            if (display.textContent.length > 2) {
                display.textContent = validDisplayValue.slice(0, 2) + '-' + validDisplayValue.slice(2, 5);
            }
        })
    }
    function addOrRemove(decision) {
    if (decision === "add") {
        addDeptCont.classList.add("show");
        deptBack.classList.add("show");
    } else {
        addDeptCont.classList.remove("show");
        deptBack.classList.remove("show");
    }
}

    addDeptBtn.addEventListener("click", e => {
        e.preventDefault();
        addOrRemove("add"); 
    });

    deptBack.addEventListener("click", e => {
        e.preventDefault();
        addOrRemove("remove");
    })
$(document).ready(function() {
    $('#myTable').DataTable();
});
</script>

<?php include('footer.php'); ?> 
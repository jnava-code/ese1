<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech'); // Update with actual credentials

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form was submitted
if (isset($_POST['action']) && isset($_POST['leave_id'])) {
    $leave_id = $_POST['leave_id'];
    $employee_id = $_POST['employee_id'];
    $number_of_days = $_POST['number_of_days'];
    $leave_type = $_POST['leave_type'];
    $action = $_POST['action']; // This will be either 'approve' or 'reject'

    // Set the status based on the action
    if ($action == 'approve') {
        $status = 'Approved';

        // Update the leave request status to 'Approved'
        $sql = "UPDATE leave_applications SET status = '$status' WHERE leave_id = '$leave_id'";

        if (mysqli_query($conn, $sql)) {
            // Check if leave is approved, then update the employee's leave balance
            $empSql = "SELECT sick_leave, vacation_leave, maternity_leave, paternity_leave FROM employees WHERE employee_id='$employee_id'";
            $empResult = mysqli_query($conn, $empSql);

            if ($empResult) {
                $row = mysqli_fetch_assoc($empResult);
                
                // Adjust leave balances based on the leave type
                if ($leave_type == "Sick") {
                    $leave_sql = "UPDATE employees SET sick_leave = sick_leave - $number_of_days WHERE employee_id='$employee_id'";
                } elseif ($leave_type == "Vacation") {
                    $leave_sql = "UPDATE employees SET vacation_leave = vacation_leave - $number_of_days WHERE employee_id='$employee_id'";
                } elseif ($leave_type == "Paternity") {
                    $leave_sql = "UPDATE employees SET paternity_leave = paternity_leave - $number_of_days WHERE employee_id='$employee_id'";
                } else { // Default case: Maternity
                    $leave_sql = "UPDATE employees SET maternity_leave = maternity_leave - $number_of_days WHERE employee_id='$employee_id'";
                }

                // Execute the update to the employee's leave balance
                $leave_result = mysqli_query($conn, $leave_sql);

                if ($leave_result) {
                    // Redirect back to the leave requests page after processing
                    header("Location: ./leave");
                    exit();
                } else {
                    echo "Error updating leave balance: " . mysqli_error($conn);
                }
            } else {
                echo "Error fetching employee data: " . mysqli_error($conn);
            }
        } else {
            echo "Error updating leave status: " . mysqli_error($conn);
        }
    } elseif ($action == 'reject') {
        $status = 'Rejected';

        // Update the leave request status to 'Rejected'
        $sql = "UPDATE leave_applications SET status = '$status' WHERE leave_id = '$leave_id'";

        if (mysqli_query($conn, $sql)) {
            // Redirect back to the leave requests page after rejection
            header("Location: ./leave");
            exit();
        } else {
            echo "Error updating leave status: " . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);  // Close the database connection
?>

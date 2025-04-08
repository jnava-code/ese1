<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech'); // Update with actual credentials

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form was submitted
if (isset($_POST['action'], $_POST['leave_id'])) {
    $employee_id = $_POST['employee_id'];
    $leave_id = $_POST['leave_id'];
    $reason_of_rejection = $_POST['reason_of_rejection'];
    $leave_type = strtolower(trim($_POST['leave_type']));
    $number_of_days = (int) $_POST['number_of_days'];
   
    $action = $_POST['action']; // Either 'approve' or 'reject'

    // Set the status based on the action
    if ($action == 'approve') {
        $status = 'Approved';

        // Determine the column for leave availed
        switch ($leave_type) {
            case 'sick':
                $availed = 'sick_availed';
                break;
            case 'vacation':
                $availed = 'vacation_availed';
                break;
            case 'maternity':
                $availed = 'maternity_availed';
                break;
            case 'paternity':
                $availed = 'paternity_availed';
                break;
            default:
                die("Invalid leave type.");
        }

        if ($employee_id) {
            // Update the leave request status
            $stmt = mysqli_prepare($conn, "UPDATE leave_applications SET status = ? WHERE leave_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $status, $leave_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Update the leave availed in the employees table
            $stmt1 = mysqli_prepare($conn, "UPDATE employees SET $availed = $availed + ? WHERE employee_id = ?");
            mysqli_stmt_bind_param($stmt1, "ii", $number_of_days, $employee_id);
            mysqli_stmt_execute($stmt1);
            mysqli_stmt_close($stmt1);

            header("Location: ./leave");
            exit();
        } else {
            die("Error: Employee not found.");
        }
    } elseif ($action == 'reject') {
        $status = 'Rejected';

        // Update the leave request status
        $stmt = mysqli_prepare($conn, "UPDATE leave_applications SET status = ?, reason_of_rejection = ? WHERE leave_id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $status, $reason_of_rejection, $leave_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: ./leave");
        exit();
    } else {
        die("Invalid action.");
    }
}

mysqli_close($conn); // Close database connection
?>

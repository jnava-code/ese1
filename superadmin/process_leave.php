<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech'); // Update with actual credentials

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form was submitted
if (isset($_POST['action']) && isset($_POST['leave_id'])) {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action']; // This will be either 'approve' or 'reject'

    // Set the status based on the action
    if ($action == 'approve') {
        $status = 'Approved';
    } else if ($action == 'reject') {
        $status = 'Rejected';
    }

    // Update the leave request status in the database
    $sql = "UPDATE leave_applications SET status = '$status' WHERE leave_id = '$leave_id'";

    if (mysqli_query($conn, $sql)) {
        // Redirect back to the leave requests page after processing
        header("Location: ./leave");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

mysqli_close($conn);  // Close database conne

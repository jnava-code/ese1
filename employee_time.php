<?php
$conn = mysqli_connect('localhost', 'root', '', 'esetech');
date_default_timezone_set('Asia/Manila'); // Adjust as per your timezone
$success = "";
$error = "";

// Current date and time (defined globally)
$current_day = date('l'); // Day of the week (e.g., Sunday)
$cdate = date('F j, Y'); // Full date (e.g., November 24, 2024)
$current_date = date('Y-m-d'); // YYYY-MM-DD format


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $inorout = $_POST['in-or-out'];
    
    // Prevent SQL injection
    $employee_id = mysqli_real_escape_string($conn, $employee_id);

    // Define work hours
    $work_start = strtotime("08:00:00"); // 8:00 AM
    $work_end = strtotime("17:00:00"); // 5:00 PM

    // Current date and time
    $current_date = date('Y-m-d'); 
    $current_time = date('g:i:s A'); // 12-hour format with AM/PM
    $current_time_unix = strtotime($current_time); // Convert to UNIX timestamp

    // Initialize response array
    $response = [];

    // Check if employee exists
    $sql = "SELECT * FROM employees WHERE employee_id = '$employee_id' AND is_archived = 0";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        // Check attendance record for today
        $attendance_sql = "SELECT * FROM attendance WHERE employee_id = '$employee_id' AND date = '$current_date'";
        $attendance_result = mysqli_query($conn, $attendance_sql);

        if (mysqli_num_rows($attendance_result) == 0) {
            // No attendance record for today
            if ($inorout == "IN") {
                $status = $current_time_unix > $work_start ? 'Late' : 'On Time';
                $insert_sql = "INSERT INTO attendance (employee_id, date, clock_in_time, status) VALUES ('$employee_id', '$current_date', '$current_time', '$status')";
                if (mysqli_query($conn, $insert_sql)) {
                    $response['success'] = $status === "Late" ? "You are late today." : "Time In recorded successfully!";
                } else {
                    $response['error'] = "Error recording Time In. Please try again.";
                }
            } else {
                $response['error'] = "You must Time In first before clocking out.";
            }
        } else {
            // Attendance record exists
            $attendance_row = mysqli_fetch_assoc($attendance_result);

            if ($inorout == "OUT") {
                if (!is_null($attendance_row['clock_out_time'])) {
                    $response['error'] = "You have already timed out for today.";
                } else {
                    // Process clock-out
                    $clock_in_time = strtotime($attendance_row['clock_in_time']); 
                    $clock_out_time = strtotime($current_time); 

                    if ($clock_in_time !== false && $clock_out_time !== false) {
                        $total_seconds = $clock_out_time - $clock_in_time;

                        // Subtract 1 hour for lunch break (12 PM to 1 PM)
                        // Check if the clock-in or clock-out time falls in the lunch period
                        $lunch_start = strtotime('12:00:00');
                        $lunch_end = strtotime('13:00:00');
                        
                        if ($clock_in_time < $lunch_end && $clock_out_time > $lunch_start) {
                            $total_seconds -= 3600; // Subtract 1 hour (3600 seconds) for the lunch break
                        }

                        // Calculate total hours in decimal format
                        $total_hours = round($total_seconds / 3600, 2); // Convert seconds to hours

                        // Calculate status based on total hours
                        $status = ($total_hours < 7.99) ? "Under Time" : (($total_hours >= 8) ? "Over Time" : "Present");

                        // Update attendance record with clock-out time
                        $update_sql = "UPDATE attendance 
                                       SET clock_out_time = '$current_time', total_hours = '$total_hours', status = '$status'
                                       WHERE attendance_id = '{$attendance_row['attendance_id']}'";

                        if (mysqli_query($conn, $update_sql)) {
                            $response['success'] = "Time Out recorded successfully! Status: $status";
                        } else {
                            $response['error'] = "Error recording Time Out. Please try again.";
                        }
                    } else {
                        $response['error'] = "Invalid time format.";
                    }
                }
            }
        }
    } else {
        $response['error'] = "Invalid Employee ID.";
    }

    // Return response as JSON
    echo json_encode($response);
}
?>

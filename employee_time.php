<?php
$conn = mysqli_connect('localhost', 'root', '', 'esetech');
date_default_timezone_set('Asia/Manila'); // Adjust as per your timezone
$success = "";
$error = "";

// Current date and time (defined globally)
$current_day = date('l'); // Day of the week (e.g., Sunday)
$cdate = date('F j, Y'); // Full date (e.g., November 24, 2024)
$current_date = date('Y-m-d'); // YYYY-MM-DD format
$current_datetime = date('Y-m-d H:i:s'); // Full date-time format

// Convert current time to 12-hour format for consistency
$current_time = date('h:i:s A'); // 12-hour format (e.g., 03:00:00 PM)
$current_time_unix = strtotime($current_datetime); // Full timestamp

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $inorout = $_POST['in-or-out'];
    
    // Prevent SQL injection
    $employee_id = mysqli_real_escape_string($conn, $employee_id);

    // Define work hours
    $morning_start = strtotime("$current_date 08:00:00"); // 8:00 AM
    $morning_end = strtotime("$current_date 12:00:00"); // 12:00 PM
    $afternoon_start = strtotime("$current_date 13:00:00"); // 1:00 PM
    $afternoon_end = strtotime("$current_date 17:00:00"); // 5:00 PM
    $overtime_start = strtotime("$current_date 17:30:00"); // 5:30 PM

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
                $status = $current_time_unix > $morning_start ? 'Late' : 'On Time';
                $clock_in_time_12hr = date('h:i:s A', strtotime($current_datetime));

                $insert_sql = "INSERT INTO attendance (employee_id, date, clock_in_time, status) VALUES ('$employee_id', '$current_date', '$clock_in_time_12hr', '$status')";
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
                    $clock_in_datetime = strtotime($attendance_row['date'] . ' ' . $attendance_row['clock_in_time']);
                    $clock_out_datetime = strtotime($current_datetime);
            
                    if ($clock_in_datetime !== false && $clock_out_datetime !== false) {
                        // Ensure the time-out is after time-in
                        if ($clock_out_datetime <= $clock_in_datetime) {
                            $response['error'] = "Time Out must be after Time In.";
                            echo json_encode($response);
                            exit;
                        }
            
                        $morning_hours = 0;
                        $afternoon_hours = 0;
                        $overtime_hours = 0;
            
                        // Morning Hours (8:00 AM - 12:00 PM)
                        if ($clock_in_datetime < $morning_end && $clock_out_datetime > $morning_start) {
                            $morning_start_time = max($clock_in_datetime, $morning_start);
                            $morning_end_time = min($clock_out_datetime, $morning_end);
                            $morning_hours = max(($morning_end_time - $morning_start_time) / 3600, 0);
                        }
            
                        // Afternoon Hours (1:00 PM - 5:00 PM)
                        if ($clock_out_datetime > $afternoon_start && $clock_in_datetime < $afternoon_end) {
                            $afternoon_start_time = max($clock_in_datetime, $afternoon_start);
                            $afternoon_end_time = min($clock_out_datetime, $afternoon_end);
                            $afternoon_hours = max(($afternoon_end_time - $afternoon_start_time) / 3600, 0);
                        }
            
                        // Overtime Hours (5:30 PM onwards)
                        if ($clock_out_datetime >= $overtime_start) {
                            $overtime_hours = max(($clock_out_datetime - $overtime_start) / 3600, 0);
                        }
            
                        // Compute Total Hours
                        $total_hours = round($morning_hours + $afternoon_hours + $overtime_hours, 2);
            
                        // Determine status based on clock-out time
                        if ($clock_out_datetime >= $overtime_start) {
                            $status = "Overtime"; // If time-out is 5:30 PM or later
                        } elseif ($clock_out_datetime >= $afternoon_end && $clock_out_datetime < $overtime_start) {
                            $status = "Present"; // If time-out is between 5:00 PM and 5:29 PM
                        } elseif ($total_hours > 0 && $total_hours < 8) {
                            $status = "Under Time"; // If total hours worked is less than 8 hours
                        } else {
                            $status = "Invalid"; // Covers cases where no valid hours were recorded
                        }

        
                        // Convert clock-out time to 12-hour format
                        $clock_out_time_12hr = date('h:i:s A', strtotime($current_datetime));
            
                        // Update attendance record
                        $update_sql = "UPDATE attendance 
                                       SET clock_out_time = '$clock_out_time_12hr', total_hours = '$total_hours', status = '$status'
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

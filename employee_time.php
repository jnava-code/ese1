<?php
$conn = mysqli_connect('localhost', 'root', '', 'esetech');
date_default_timezone_set('Asia/Manila');

$success = "";
$error = "";

$current_day = date('l');
$cdate = date('F j, Y');
$current_date = date('Y-m-d');
$current_datetime = date('Y-m-d H:i:s');
$current_time_unix = strtotime($current_datetime);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $inorout = $_POST['in-or-out'];
    $response = [];

    $sql = "SELECT * FROM employees WHERE employee_id = '$employee_id' AND is_archived = 0";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        // Check leave status
        $leave_sql = "SELECT * FROM leave_applications 
                      WHERE employee_id = '$employee_id' 
                      AND status = 'Approved' 
                      AND start_date <= '$current_date' 
                      AND end_date >= '$current_date'";
        $leave_result = mysqli_query($conn, $leave_sql);

        if (mysqli_num_rows($leave_result) > 0) {
            $response['error'] = "You are currently on approved leave";
            echo json_encode($response);
            exit;
        }

        $attendance_sql = "SELECT * FROM attendance WHERE employee_id = '$employee_id' AND date = '$current_date'";
        $attendance_result = mysqli_query($conn, $attendance_sql);

        if (mysqli_num_rows($attendance_result) == 0) {
            if ($inorout == "IN") {
                // Set 8:00 AM as the start time for regular hours
                $morning_start = strtotime("$current_date 08:00:00");
                $status = $current_time_unix > $morning_start ? 'Late' : 'On Time';
                $clock_in_time = date('H:i:s', strtotime($current_datetime));

                $insert_sql = "INSERT INTO attendance (employee_id, date, clock_in_time, status) 
                               VALUES ('$employee_id', '$current_date', '$clock_in_time', '$status')";
                $response['success'] = mysqli_query($conn, $insert_sql) ? 
                    ($status === "Late" ? "You are late today." : "Time In recorded successfully!") 
                    : "Error recording Time In. Please try again.";
            } else {
                $response['error'] = "You must Time In first before clocking out.";
            }
        } else {
            $attendance_row = mysqli_fetch_assoc($attendance_result);

            if ($inorout == "OUT") {
                if (!is_null($attendance_row['clock_out_time'])) {
                    $response['error'] = "You have already timed out for today.";
                } else {
                    $clock_in_datetime = strtotime($attendance_row['date'] . ' ' . $attendance_row['clock_in_time']);
                    $clock_out_datetime = strtotime($current_datetime);

                    if ($clock_out_datetime <= $clock_in_datetime) {
                        $response['error'] = "Time Out must be after Time In.";
                        echo json_encode($response);
                        exit;
                    }

                    // Set 8:00 AM as the start time for regular hours
                    $morning_start = strtotime("$current_date 08:00:00");

                    // Ensure that the computation always starts at 8:00 AM if the time-in is before 8 AM
                    if ($clock_in_datetime < $morning_start) {
                        $clock_in_datetime = $morning_start;
                    }

                    // Calculate raw total hours
                    $total_seconds = $clock_out_datetime - $clock_in_datetime;
                    $total_hours = round($total_seconds / 3600, 2);

                    // Exclude break time (12:00 PM - 1:00 PM)
                    $break_start = strtotime("$current_date 12:00:00");
                    $break_end = strtotime("$current_date 13:00:00");

                    if ($clock_in_datetime < $break_end && $clock_out_datetime > $break_start) {
                        $break_duration = min($clock_out_datetime, $break_end) - max($clock_in_datetime, $break_start);
                        $break_duration = max($break_duration, 0);
                        $total_hours -= $break_duration / 3600; // Deduct break time in hours
                    }

                    // Determine status
                    if ($total_hours >= 8.5) {
                        $status = "Over Time";
                    } elseif ($total_hours >= 8) {
                        $status = "Present";
                    } elseif ($total_hours > 0) {
                        $status = "Under Time";
                    } else {
                        $status = "Invalid";
                    }

                    $clock_out_time = date('H:i:s', strtotime($current_datetime));

                    $update_sql = "UPDATE attendance 
                                   SET clock_out_time = '$clock_out_time', 
                                       total_hours = '$total_hours', 
                                       status = '$status' 
                                   WHERE attendance_id = '{$attendance_row['attendance_id']}'";

                    $response['success'] = mysqli_query($conn, $update_sql) ? 
                        "Time Out recorded successfully! Status: $status" 
                        : "Error recording Time Out. Please try again.";
                }
            }
        }
    } else {
        $response['error'] = "Invalid Employee ID.";
    }

    echo json_encode($response);
}
?>

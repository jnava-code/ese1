<?php
$conn = mysqli_connect('localhost', 'root', '', 'esetech');
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d');
$current_datetime = date('Y-m-d H:i:s');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $inorout = $_POST['in-or-out'];
    $response = [];

    $employee_check = mysqli_query($conn, "SELECT * FROM employees WHERE employee_id = '$employee_id' AND is_archived = 0");

    if (mysqli_num_rows($employee_check) == 1) {
        $on_leave = mysqli_query($conn, "SELECT * FROM leave_applications WHERE employee_id = '$employee_id' AND status = 'Approved' AND start_date <= '$current_date' AND end_date >= '$current_date'");
        if (mysqli_num_rows($on_leave) > 0) {
            $response['error'] = "You are currently on approved leave";
            echo json_encode($response);
            exit;
        }

        $attendance = mysqli_query($conn, "SELECT * FROM attendance WHERE employee_id = '$employee_id' AND date = '$current_date'");
        if (mysqli_num_rows($attendance) == 0) {
            if ($inorout == "IN") {
                $clock_in_time = $current_datetime;

                $clock_in_hour = date("H", strtotime($clock_in_time)); 
                $am_pm_in = ($clock_in_hour < 12) ? 0 : 1; // 0 for AM, 1 for PM
                $insert_time_in = date("h:i:s A", strtotime($clock_in_time));

                $morning_start = strtotime("$current_date 08:00:00");
                $status = strtotime($current_datetime) > $morning_start ? 'Late' : 'On Time';

                $insert = "INSERT INTO attendance (employee_id, date, clock_in_time, status, am)
                           VALUES ('$employee_id', '$current_date', '$insert_time_in', '$status', '$am_pm_in')";
                $response['success'] = mysqli_query($conn, $insert)
                    ? ($status === "Late" ? "You are late today." : "Time In recorded successfully!")
                    : "Error recording Time In. Please try again.";
            } else {
                $response['error'] = "You must Time In first before clocking out.";
            }
        } else {
            $att = mysqli_fetch_assoc($attendance);
            if ($inorout == "OUT") {
                if (!is_null($att['clock_out_time'])) {
                    $response['error'] = "You have already timed out for today.";
                    echo json_encode($response);
                    exit;
                }

                $clock_in_timestamp = strtotime($att['clock_in_time']);
                $clock_in = strtotime($att['clock_in_time']);
                $clock_out_timestamp = strtotime($current_datetime);

                if ($clock_out_timestamp <= $clock_in_timestamp) {
                    $response['error'] = "Time Out must be after Time In.";
                    echo json_encode($response);
                    exit;
                }

                $morning_start = strtotime("$current_date 08:00:00");
                $morning_end = strtotime("$current_date 12:00:00");
                $afternoon_start = strtotime("$current_date 13:00:00");
                $afternoon_end = strtotime("$current_date 17:00:00");
                $overtime_start = strtotime("$current_date 17:30:00");

                $morning_hours = 0;
                $afternoon_hours = 0;
                $overtime_hours = 0;

                if ($clock_in_timestamp < $morning_end) {
                    $morning_hours = min($clock_out_timestamp, $morning_end) - max($clock_in_timestamp, $morning_start);
                    $morning_hours = max($morning_hours / 3600, 0);
                }

                if ($clock_out_timestamp > $afternoon_start) {
                    $afternoon_hours = min($clock_out_timestamp, $afternoon_end) - max($clock_in_timestamp, $afternoon_start);
                    $afternoon_hours = max($afternoon_hours / 3600, 0);
                }

                if ($clock_out_timestamp > $overtime_start) {
                    $overtime_hours = ($clock_out_timestamp - $overtime_start) / 3600;
                    $overtime_hours = max($overtime_hours, 0);
                }

                $am_pm_in = $att['am']; 

                if ($am_pm_in == 0) {  // AM case
                    $clock_in_timestamp = strtotime(date("Y-m-d h:i:s A", strtotime($clock_in))); 
                    $clock_out_timestamp = strtotime(date("Y-m-d h:i:s A", strtotime($current_datetime)));
                } else {  // PM case
                    $clock_in_timestamp = strtotime(date("Y-m-d H:i:s", strtotime($clock_in)));
                    $clock_out_timestamp = strtotime(date("Y-m-d H:i:s", strtotime($current_datetime)));
                }

                $total_seconds = $clock_out_timestamp - $clock_in_timestamp;

                // Compute total hours correctly
                $total_hours = $total_seconds / 3600; // In hours
                $total_hours_formatted = round($total_hours, 2);  // rounding to 2 decimal places

                $status = "Invalid";
                if ($total_hours_formatted >= 8.5) {
                    $status = "Over Time";
                } elseif ($total_hours_formatted >= 8) {
                    $status = "Present";
                } elseif ($total_hours_formatted > 0) {
                    $status = "Under Time";
                }

                $insert_time_out = date("h:i:s A", strtotime($current_datetime));

                $update = "UPDATE attendance SET 
                    clock_out_time = '$insert_time_out', 
                    total_hours = '$total_hours_formatted', 
                    status = '$status'
                    WHERE attendance_id = '{$att['attendance_id']}'";

                $response['success'] = mysqli_query($conn, $update)
                    ? "Time Out recorded successfully! Status: $status | Total: $total_hours_formatted hr(s)"
                    : "Error recording Time Out. Please try again.";
            }
        }
    } else {
        $response['error'] = "Invalid Employee ID.";
    }
    $response['clock_out_timestamp'] = $clock_out_timestamp;
    $response['am'] = $am_pm_in;
    $response['total_hours_formatted'] = $total_seconds;
    echo json_encode($response);
}


?>


<?php
// $conn = mysqli_connect('localhost', 'root', '', 'esetech');
// date_default_timezone_set('Asia/Manila');
// $success = "";
// $error = "";

// $current_day = date('l');
// $cdate = date('F j, Y');
// $current_date = date('Y-m-d');
// $current_datetime = date('Y-m-d H:i:s');
// $current_time = date('h:i:s A');
// $current_time_unix = strtotime($current_datetime);

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
//     $inorout = $_POST['in-or-out'];
//     $response = [];

//     $sql = "SELECT * FROM employees WHERE employee_id = '$employee_id' AND is_archived = 0";
//     $result = mysqli_query($conn, $sql);

//     if (mysqli_num_rows($result) == 1) {
//         // Check if employee is on approved leave for today
//         $leave_sql = "SELECT * FROM leave_applications WHERE employee_id = '$employee_id' AND status = 'Approved' AND start_date <= '$current_date' AND end_date >= '$current_date'";
//         $leave_result = mysqli_query($conn, $leave_sql);

//         if (mysqli_num_rows($leave_result) > 0) {
//             $response['error'] = "You are currently on approved leave";
//             echo json_encode($response);
//             exit;
//         }

//         $attendance_sql = "SELECT * FROM attendance WHERE employee_id = '$employee_id' AND date = '$current_date'";
//         $attendance_result = mysqli_query($conn, $attendance_sql);

//         if (mysqli_num_rows($attendance_result) == 0) {
//             if ($inorout == "IN") {
//                 $morning_start = strtotime("$current_date 08:00:00");
//                 $status = $current_time_unix > $morning_start ? 'Late' : 'On Time';
//                 $clock_in_time_12hr = date('h:i:s A', strtotime($current_datetime));

//                 $insert_sql = "INSERT INTO attendance (employee_id, date, clock_in_time, status) VALUES ('$employee_id', '$current_date', '$clock_in_time_12hr', '$status')";
//                 $response['success'] = mysqli_query($conn, $insert_sql) ? ($status === "Late" ? "You are late today." : "Time In recorded successfully!") : "Error recording Time In. Please try again.";
//             } else {
//                 $response['error'] = "You must Time In first before clocking out.";
//             }
//         } else {
//             $attendance_row = mysqli_fetch_assoc($attendance_result);

//             if ($inorout == "OUT") {
//                 if (!is_null($attendance_row['clock_out_time'])) {
//                     $response['error'] = "You have already timed out for today.";
//                 } else {
//                     $clock_in_datetime = strtotime($attendance_row['date'] . ' ' . $attendance_row['clock_in_time']);
//                     $clock_out_datetime = strtotime($current_datetime);

//                     if ($clock_out_datetime <= $clock_in_datetime) {
//                         $response['error'] = "Time Out must be after Time In.";
//                         echo json_encode($response);
//                         exit;
//                     }

//                     $morning_end = strtotime("$current_date 12:00:00");
//                     $afternoon_start = strtotime("$current_date 13:00:00");
//                     $afternoon_end = strtotime("$current_date 17:00:00");
//                     $overtime_start = strtotime("$current_date 17:30:00");

//                     $morning_hours = max((min($clock_out_datetime, $morning_end) - max($clock_in_datetime, strtotime("$current_date 08:00:00"))) / 3600, 0);
//                     $afternoon_hours = max((min($clock_out_datetime, $afternoon_end) - max($clock_in_datetime, $afternoon_start)) / 3600, 0);
//                     $overtime_hours = $clock_out_datetime >= $overtime_start ? max(($clock_out_datetime - $overtime_start) / 3600, 0) : 0;

//                     $total_hours = round($morning_hours + $afternoon_hours + $overtime_hours, 2);
                    
//                     $status = ($clock_out_datetime >= $overtime_start) ? "Overtime" : (($clock_out_datetime >= $afternoon_end) ? "Present" : ($total_hours < 8 ? "Under Time" : "Invalid"));
//                     $clock_out_time_12hr = date('h:i:s A', strtotime($current_datetime));

//                     $update_sql = "UPDATE attendance SET clock_out_time = '$clock_out_time_12hr', total_hours = '$total_hours', status = '$status' WHERE attendance_id = '{$attendance_row['attendance_id']}'";
//                     $response['success'] = mysqli_query($conn, $update_sql) ? "Time Out recorded successfully! Status: $status" : "Error recording Time Out. Please try again.";
//                 }
//             }
//         }
//     } else {
//         $response['error'] = "Invalid Employee ID.";
//     }

//     echo json_encode($response);
// }
?>

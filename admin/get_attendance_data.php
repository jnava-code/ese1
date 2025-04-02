<?php
// Add header to specify JSON response
header('Content-Type: application/json');

$conn = mysqli_connect('localhost', 'root', '', 'esetech');
if (!$conn) {
    die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()])); 
}

$type = $_GET['type'];
$response = [];

switch($type) {
    case 'daily':
        $date = $_GET['date'];
        $today = date('Y-m-d');
        
        // Query for attendance data
        $sql = "SELECT 
            COUNT(CASE WHEN status = 'On Time' THEN 1 END) as ontime,
            COUNT(CASE WHEN status = 'Late' THEN 1 END) as late,
            COUNT(CASE WHEN status = 'Under Time' THEN 1 END) as undertime,
            COUNT(CASE WHEN status = 'Over Time' THEN 1 END) as overtime
            FROM attendance 
            WHERE date = '$date'";

        // Check if the date is a weekend (Saturday or Sunday) or future date
        $dayOfWeek = date('w', strtotime($date));
        if ($dayOfWeek == 0 || $dayOfWeek == 6 || strtotime($date) > strtotime($today)) {
            $absent = 0;
        } else {
            // Get total employees who should be present (excluding those on leave)
            $totalEmployeesQuery = "SELECT COUNT(*) as total FROM employees e 
                                  WHERE e.hire_date <= '$date' 
                                  AND NOT EXISTS (
                                      SELECT 1 FROM leave_applications la 
                                      WHERE la.employee_id = e.employee_id 
                                      AND la.status = 'Approved' 
                                      AND '$date' BETWEEN la.start_date AND la.end_date
                                  )";
            $totalResult = mysqli_query($conn, $totalEmployeesQuery);
            $totalEmployees = mysqli_fetch_assoc($totalResult)['total'];

            // Get number of employees who attended
            $attendedQuery = "SELECT COUNT(DISTINCT employee_id) as attended 
                            FROM attendance 
                            WHERE date = '$date'";
            $attendedResult = mysqli_query($conn, $attendedQuery);
            $attended = mysqli_fetch_assoc($attendedResult)['attended'];

            $absent = $totalEmployees - $attended;
        }
        break;

    case 'weekly':
        $month = $_GET['month'];
        $year = $_GET['year'];
        $week = $_GET['week'];
        
        // Calculate week dates
        $firstDay = date('Y-m-d', strtotime("$year-$month-01"));
        $weekStart = date('Y-m-d', strtotime($firstDay . " +".($week-1)." weeks"));
        $weekEnd = date('Y-m-d', strtotime($weekStart . " +6 days"));
        $today = date('Y-m-d');
        
        // Query for attendance data
        $sql = "SELECT 
            COUNT(CASE WHEN status = 'On Time' THEN 1 END) as ontime,
            COUNT(CASE WHEN status = 'Late' THEN 1 END) as late,
            COUNT(CASE WHEN status = 'Under Time' THEN 1 END) as undertime,
            COUNT(CASE WHEN status = 'Over Time' THEN 1 END) as overtime
            FROM attendance 
            WHERE date BETWEEN '$weekStart' AND '$weekEnd'";

        // Calculate total working days in the week (excluding weekends and future dates)
        $totalWorkingDays = 0;
        for ($date = $weekStart; $date <= $weekEnd; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
            $dayOfWeek = date('w', strtotime($date));
            if ($dayOfWeek != 0 && $dayOfWeek != 6 && strtotime($date) <= strtotime($today)) {
                $totalWorkingDays++;
            }
        }

        // Get total employees who should be present (excluding those on leave)
        $totalEmployeesQuery = "SELECT COUNT(*) as total FROM employees e 
                              WHERE e.hire_date <= '$weekEnd' 
                              AND NOT EXISTS (
                                  SELECT 1 FROM leave_applications la 
                                  WHERE la.employee_id = e.employee_id 
                                  AND la.status = 'Approved' 
                                  AND la.start_date <= '$weekEnd' 
                                  AND la.end_date >= '$weekStart'
                              )";
        $totalResult = mysqli_query($conn, $totalEmployeesQuery);
        $totalEmployees = mysqli_fetch_assoc($totalResult)['total'];

        // Get number of employees who attended
        $attendedQuery = "SELECT COUNT(DISTINCT employee_id) as attended 
                        FROM attendance 
                        WHERE date BETWEEN '$weekStart' AND '$weekEnd'";
        $attendedResult = mysqli_query($conn, $attendedQuery);
        $attended = mysqli_fetch_assoc($attendedResult)['attended'];

        $absent = ($totalEmployees * $totalWorkingDays) - $attended;
        break;

    case 'monthly':
        $month = $_GET['month'];
        $year = $_GET['year'];
        $today = date('Y-m-d');
        
        // Query for attendance data
        $sql = "SELECT 
            COUNT(CASE WHEN status = 'On Time' THEN 1 END) as ontime,
            COUNT(CASE WHEN status = 'Late' THEN 1 END) as late,
            COUNT(CASE WHEN status = 'Under Time' THEN 1 END) as undertime,
            COUNT(CASE WHEN status = 'Over Time' THEN 1 END) as overtime
            FROM attendance 
            WHERE MONTH(date) = $month AND YEAR(date) = $year";

        // Calculate total working days in the month (excluding weekends and future dates)
        $totalWorkingDays = 0;
        $lastDay = date('t', strtotime("$year-$month-01"));
        for ($day = 1; $day <= $lastDay; $day++) {
            $date = date('Y-m-d', strtotime("$year-$month-$day"));
            $dayOfWeek = date('w', strtotime($date));
            if ($dayOfWeek != 0 && $dayOfWeek != 6 && strtotime($date) <= strtotime($today)) {
                $totalWorkingDays++;
            }
        }

        // Get total employees who should be present (excluding those on leave)
        $totalEmployeesQuery = "SELECT COUNT(*) as total FROM employees e 
                              WHERE e.hire_date <= LAST_DAY('$year-$month-01')
                              AND NOT EXISTS (
                                  SELECT 1 FROM leave_applications la 
                                  WHERE la.employee_id = e.employee_id 
                                  AND la.status = 'Approved' 
                                  AND MONTH(la.start_date) = $month 
                                  AND YEAR(la.start_date) = $year
                              )";
        $totalResult = mysqli_query($conn, $totalEmployeesQuery);
        $totalEmployees = mysqli_fetch_assoc($totalResult)['total'];

        // Get number of employees who attended
        $attendedQuery = "SELECT COUNT(DISTINCT employee_id) as attended 
                        FROM attendance 
                        WHERE MONTH(date) = $month AND YEAR(date) = $year";
        $attendedResult = mysqli_query($conn, $attendedQuery);
        $attended = mysqli_fetch_assoc($attendedResult)['attended'];

        $absent = ($totalEmployees * $totalWorkingDays) - $attended;
        break;
}

$result = mysqli_query($conn, $sql);
if ($result) {
    $data = mysqli_fetch_assoc($result);
    
    // Get leave count
    $leaveSQL = "SELECT COUNT(*) as leave_count FROM leave_applications 
                 WHERE status = 'Approved'";

    // Add date conditions based on type
    if ($type == 'daily') {
        $leaveSQL .= " AND '$date' BETWEEN start_date AND end_date";
    } elseif ($type == 'weekly') {
        $leaveSQL .= " AND start_date <= '$weekEnd' AND end_date >= '$weekStart'";
    } elseif ($type == 'monthly') {
        $leaveSQL .= " AND MONTH(start_date) = $month AND YEAR(start_date) = $year";
    }
    
    $leaveResult = mysqli_query($conn, $leaveSQL);
    $leaveData = mysqli_fetch_assoc($leaveResult);
    
    $data['leave'] = $leaveData['leave_count'];
    $data['absent'] = $absent;
    
    echo json_encode($data);
} else {
    echo json_encode(['error' => mysqli_error($conn)]);
}

mysqli_close($conn);
?>

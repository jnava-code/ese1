<?php
// Remove the header include
// include('header.php');

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
        $sql = "SELECT 
            COUNT(CASE WHEN status = 'On Time' THEN 1 END) as ontime,
            COUNT(CASE WHEN status = 'Late' THEN 1 END) as late,
            COUNT(CASE WHEN status = 'Under Time' THEN 1 END) as undertime,
            COUNT(CASE WHEN status = 'Over Time' THEN 1 END) as overtime
            FROM attendance 
            WHERE date = '$date'";
        break;

    case 'weekly':
        $month = $_GET['month'];
        $year = $_GET['year'];
        $week = $_GET['week'];
        
        // Calculate week dates
        $firstDay = date('Y-m-d', strtotime("$year-$month-01"));
        $weekStart = date('Y-m-d', strtotime($firstDay . " +".($week-1)." weeks"));
        $weekEnd = date('Y-m-d', strtotime($weekStart . " +6 days"));
        
        $sql = "SELECT 
            COUNT(CASE WHEN status = 'On Time' THEN 1 END) as ontime,
            COUNT(CASE WHEN status = 'Late' THEN 1 END) as late,
            COUNT(CASE WHEN status = 'Under Time' THEN 1 END) as undertime,
            COUNT(CASE WHEN status = 'Over Time' THEN 1 END) as overtime
            FROM attendance 
            WHERE date BETWEEN '$weekStart' AND '$weekEnd'";
        break;

    case 'monthly':
        $month = $_GET['month'];
        $year = $_GET['year'];
        $sql = "SELECT 
            COUNT(CASE WHEN status = 'On Time' THEN 1 END) as ontime,
            COUNT(CASE WHEN status = 'Late' THEN 1 END) as late,
            COUNT(CASE WHEN status = 'Under Time' THEN 1 END) as undertime,
            COUNT(CASE WHEN status = 'Over Time' THEN 1 END) as overtime
            FROM attendance 
            WHERE MONTH(date) = $month AND YEAR(date) = $year";
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
    
    // Calculate absent
    $employeeSQL = "SELECT COUNT(*) as total FROM employees WHERE is_archived = 0";
    $employeeResult = mysqli_query($conn, $employeeSQL);
    $employeeData = mysqli_fetch_assoc($employeeResult);
    
    $data['absent'] = $employeeData['total'] - ($data['ontime'] + $data['late'] + $data['leave']);
    
    echo json_encode($data);
} else {
    echo json_encode(['error' => mysqli_error($conn)]);
}

mysqli_close($conn);
?> 
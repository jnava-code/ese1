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
$today = date('Y-m-d');

switch($type) {
    case 'daily':
        $date = $_GET['date'];
        
        // If date is in future, only show approved leaves
        if ($date > $today) {
            $sql = "SELECT 
                0 as ontime,
                0 as late,
                0 as undertime,
                0 as overtime,
                (SELECT COUNT(*) 
                 FROM leave_applications 
                 WHERE status = 'Approved' 
                 AND '$date' BETWEEN start_date AND end_date) as leave_count";
            
            $result = mysqli_query($conn, $sql);
            $data = mysqli_fetch_assoc($result);
            
            // Get total employees
            $empSQL = "SELECT COUNT(*) as total FROM employees WHERE is_archived = 0";
            $empResult = mysqli_query($conn, $empSQL);
            $empData = mysqli_fetch_assoc($empResult);
            
            echo json_encode([
                'ontime' => 0,
                'late' => 0,
                'undertime' => 0,
                'overtime' => 0,
                'leave' => $data['leave_count'],
                'absent' => $data['leave_count'] > 0 ? $empData['total'] - $data['leave_count'] : 0
            ]);
            exit;
        }
        
        // For past or current dates, use existing logic
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
        
        // If week is in future, only show approved leaves
        if ($weekStart > $today) {
            $sql = "SELECT 
                0 as ontime,
                0 as late,
                0 as undertime,
                0 as overtime,
                (SELECT COUNT(*) 
                 FROM leave_applications 
                 WHERE status = 'Approved' 
                 AND start_date <= '$weekEnd' 
                 AND end_date >= '$weekStart') as leave_count";
            
            $result = mysqli_query($conn, $sql);
            $data = mysqli_fetch_assoc($result);
            
            // Get total employees
            $empSQL = "SELECT COUNT(*) as total FROM employees WHERE is_archived = 0";
            $empResult = mysqli_query($conn, $empSQL);
            $empData = mysqli_fetch_assoc($empResult);
            
            echo json_encode([
                'ontime' => 0,
                'late' => 0,
                'undertime' => 0,
                'overtime' => 0,
                'leave' => $data['leave_count'],
                'absent' => $data['leave_count'] > 0 ? $empData['total'] - $data['leave_count'] : 0
            ]);
            exit;
        }
        
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
        
        // If month is in future, only show approved leaves
        if ("$year-$month-01" > $today) {
            $sql = "SELECT 
                0 as ontime,
                0 as late,
                0 as undertime,
                0 as overtime,
                (SELECT COUNT(*) 
                 FROM leave_applications 
                 WHERE status = 'Approved' 
                 AND MONTH(start_date) = $month 
                 AND YEAR(start_date) = $year) as leave_count";
            
            $result = mysqli_query($conn, $sql);
            $data = mysqli_fetch_assoc($result);
            
            // Get total employees
            $empSQL = "SELECT COUNT(*) as total FROM employees WHERE is_archived = 0";
            $empResult = mysqli_query($conn, $empSQL);
            $empData = mysqli_fetch_assoc($empResult);
            
            echo json_encode([
                'ontime' => 0,
                'late' => 0,
                'undertime' => 0,
                'overtime' => 0,
                'leave' => $data['leave_count'],
                'absent' => $data['leave_count'] > 0 ? $empData['total'] - $data['leave_count'] : 0
            ]);
            exit;
        }
        
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
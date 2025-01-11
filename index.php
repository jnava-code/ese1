<?php include('server.php'); ?>
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
    // $action = $_POST['action'];

    // Prevent SQL injection
    $employee_id = mysqli_real_escape_string($conn, $employee_id);

    // Define work hours
    $work_start = strtotime("08:00:00"); // 8:00 AM
    $work_end = strtotime("17:00:00"); // 5:00 PM

    // Current time
    $current_time = date('g:i:s A'); // Use 12-hour format with AM/PM
    $current_time_unix = strtotime($current_time); // Convert to UNIX timestamp

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
                    $success = $status === "Late" ? "You are late today." : "Time In recorded successfully!";
                } else {
                    $error = "Error recording Time In. Please try again.";
                }
            } else {
                $error = "You must Time In first before clocking out.";
            }
        } else {
            // Attendance record exists
            $attendance_row = mysqli_fetch_assoc($attendance_result);

            if ($inorout == "OUT") {
                if (!is_null($attendance_row['clock_out_time'])) {
                    $error = "You have already timed out for today.";
                } else {
                    // Calculate total hours worked
                    $clock_in_time = strtotime($attendance_row['clock_in_time']); // Convert clock-in time to UNIX timestamp
                    $clock_out_time = strtotime($current_time); // Convert clock-out time to UNIX timestamp
                    $total_hours = round(($clock_out_time - $clock_in_time) / 3600, 2); // Calculate hours and round to 2 decimal places
            
                    // Update attendance record with clock-out time and total hours
                    $update_sql = "UPDATE attendance 
                                   SET clock_out_time = '$current_time', total_hours = '$total_hours' 
                                   WHERE attendance_id = '{$attendance_row['attendance_id']}'";
            
                    if (mysqli_query($conn, $update_sql)) {
                        $success = "Time Out recorded successfully!";
                    } else {
                        $error = "Error recording Time Out. Please try again.";
                    }
                }
            }
        }
    } else {
        $error = "Invalid Employee ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="shortcut icon" type="x-icon" href="images/icon1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Time Tracker</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        .header {
            background-color: #cc0000;
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 25px;
            position: relative;
            font-weight: bold;
        }
        .clock {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 16px;
            color: white;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .container h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: calc(100% - 20px); /* Ensures input doesn't overflow */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box; /* Ensures padding is included in width */
        }
        .form-group-buttons {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .form-group-buttons button {
            flex: 1;
            padding: 10px;
            border: none;
            background-color: #d61e1e;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-group-buttons button:hover {
            background-color: #c73a3a;
        }
        .message {
            margin-top: 15px;
            text-align: center;
            color: green;
        }
        .error {
            color: red;
            text-align: center;
        }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .nav-buttons a {
            flex: 1;
            text-align: center;
            text-decoration: none;
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin: 0 5px;
        }
        .nav-buttons a:hover {
            background-color: #0056b3;
        }#clock-container {
    text-align: center; /* Center the clock */
    margin: 20px;       /* Add some spacing around the clock */
}

#clock {
    font-size: 48px;    /* Increase the font size */
    font-weight: bold;  /* Make it bold for better visibility */
    color: #333;        /* Set a nice color for the clock */
    padding: 10px;      /* Add padding for space around the clock */
    border: 2px solid #ddd; /* Optional: Add a border */
    border-radius: 10px;    /* Optional: Make the border rounded */
    background-color: #f9f9f9; /* Optional: Add a background color */
}

#in-or-out {
    background-color: none;
    border: none;
    font-weight: 600;
    font-size: 32px;
    width: 100%;
    text-align: center;
}
    </style>
     <script>
        function updateClock() {
    const now = new Date();
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const amPm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12; // Convert to 12-hour format
    const timeString = `${hours}:${minutes}:${seconds} ${amPm}`;
    document.getElementById('clock').innerText = timeString;
}
setInterval(updateClock, 1000); // Update clock every second
window.onload = updateClock; // Initialize clock on page load
    </script>
</head>
<body>
    
<div class="header">
    ESE-Tech Industrial Solutions Corporation - Time Tracker
    <div id="clock-container">
    <span id="clock"></span>
</div>
    <div class="date-info">
        Today is <strong><?php echo $current_day; ?></strong>, 
        <strong><?php echo $cdate; ?></strong> <br>
    </div>
</div>


<div class="container">
    <h1>Employee Time Tracker</h1>
    <form method="POST">
        <input id="in-or-out" name="in-or-out" value="IN" readonly>
        <div class="form-group">
            <!-- <label for="employee_id">Employee ID</label> -->
            <input type="text" id="employee_id" name="employee_id" placeholder="Enter your Employee ID" required>
        </div>
  
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <?php if (!empty($success)) echo "<div class='message'>$success</div>"; ?>
    </form>
    <br>
        <a href="./login">Go to Login's Page</a>
</div>

<script>
    const inOrOut = document.getElementById("in-or-out");
    const employeeId = document.getElementById("employee_id");
    
    employeeId.addEventListener("input", e => {
    const id = e.target.value;
    if (id === "+") {
        // Toggle between IN and OUT
        inOrOut.value = inOrOut.value === "IN" ? "OUT" : "IN";
        
        // Clear the employee ID input
        employeeId.value = "";
    }
});

// Trigger form submission when "Enter" is pressed
employeeId.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            // Get the current action (IN or OUT)
            const currentAction = inOrOut.value;
            
            // Submit the form programmatically
            const form = employeeId.closest("form");
            form.submit(); // Trigger the form submission
        }
    });
</script>
</body>
</html>
<?php
$conn = mysqli_connect('localhost', 'root', '', 'esetech');
// Function to check if reset has been done for current year
function checkResetStatus($conn) {
    $currentYear = date('Y');
    $sql = "SELECT * FROM leave_reset_logs WHERE reset_year = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $currentYear);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

// Function to reset leave credits
function resetLeaveCredits($conn) {
    $currentDate = new DateTime();
    $currentYear = $currentDate->format('Y');
    $isDecember31 = $currentDate->format('m-d') === '12-31';

    // Check if it's December 31st
    if (!$isDecember31) {
        return [
            'success' => false,
            'message' => "Leave credits can only be reset on December 31st."
        ];
    }

    // Check if already reset for this year
    if (checkResetStatus($conn)) {
        return [
            'success' => false,
            'message' => "Leave credits have already been reset for year {$currentYear}. Please wait until next year."
        ];
    }

    try {
        // Start transaction
        mysqli_begin_transaction($conn);

        // Reset leave credits for regular employees
        $sql = "UPDATE employees 
                SET vacation_availed = 0,
                    sick_availed = 0,
                    maternity_availed = 0,
                    paternity_availed = 0
                WHERE employment_status = 'Regular'";

        if (mysqli_query($conn, $sql)) {
            $affected_rows = mysqli_affected_rows($conn);

            // Log the reset operation
            $logSql = "INSERT INTO leave_reset_logs (reset_year, reset_date, affected_employees) VALUES (?, NOW(), ?)";
            $stmt = mysqli_prepare($conn, $logSql);
            mysqli_stmt_bind_param($stmt, "si", $currentYear, $affected_rows);
            mysqli_stmt_execute($stmt);

            // Commit transaction
            mysqli_commit($conn);

            return [
                'success' => true,
                'message' => "Successfully reset leave credits for {$affected_rows} regular employee(s) for year {$currentYear}."
            ];
        } else {
            // Rollback on error
            mysqli_rollback($conn);
            return [
                'success' => false,
                'message' => "Error resetting leave credits: " . mysqli_error($conn)
            ];
        }
    } catch (Exception $e) {
        // Rollback on exception
        mysqli_rollback($conn);
        return [
            'success' => false,
            'message' => "Error: " . $e->getMessage()
        ];
    }
}

// Create leave_reset_logs table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS leave_reset_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reset_year VARCHAR(4) NOT NULL,
    reset_date DATETIME NOT NULL,
    affected_employees INT NOT NULL,
    UNIQUE KEY unique_year (reset_year)
)";

if (!mysqli_query($conn, $sql)) {
    $error = "Error creating leave_reset_logs table: " . mysqli_error($conn);
}

// Handle form submission
if (isset($_POST['reset_leaves'])) {
    $result = resetLeaveCredits($conn);
}

// Get current reset status
$currentYear = date('Y');
$isDecember31 = date('m-d') === '12-31';
$hasBeenReset = checkResetStatus($conn);

include('header.php');
include('includes/sideBar.php');
?>


<!DOCTYPE html>
<html>
<head>
    <title>Reset Leave Credits</title>
    <style>
        .container {
            padding: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .btn-reset {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-reset:hover {
            background-color: #c82333;
        }
        .btn-reset:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .status-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="container">
            <h2>Reset Leave Credits</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($result)): ?>
                <div class="alert <?php echo $result['success'] ? 'alert-success' : 'alert-danger'; ?>">
                    <?php echo $result['message']; ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <h3>Important Information</h3>
                <p>This action will reset the following leave credits to 0 for all regular employees:</p>
                <ul>
                    <li>Vacation Leaves Availed</li>
                    <li>Sick Leaves Availed</li>
                    <li>Maternity Leaves Availed</li>
                    <li>Paternity Leaves Availed</li>
                </ul>
                <p><strong>Note:</strong></p>
                <ul>
                    <li>This action can only be performed on December 31st</li>
                    <li>This action can only be performed once per year</li>
                    <li>Only affects employees with 'Regular' employment status</li>
                    <li>This action cannot be undone</li>
                </ul>
            </div>

            <div class="status-box">
                <h4>Current Status:</h4>
                <p>Today's Date: <?php echo date('F d, Y'); ?></p>
                <p>Reset Status for <?php echo $currentYear; ?>: 
                    <?php if ($hasBeenReset): ?>
                        <span style="color: #dc3545;">Already Reset</span>
                    <?php else: ?>
                        <span style="color: #28a745;">Not Yet Reset</span>
                    <?php endif; ?>
                </p>
            </div>

            <form method="POST" onsubmit="return confirm('Are you sure you want to reset all leave credits for regular employees? This action cannot be undone.');">
                <button type="submit" name="reset_leaves" class="btn-reset" <?php echo (!$isDecember31 || $hasBeenReset) ? 'disabled' : ''; ?>>
                    Reset Leave Credits
                </button>
            </form>
        </div>
    </main>
</body>
</html> 
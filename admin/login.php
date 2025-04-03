<?php

// ... existing login code ...

if ($row['user_type'] === 'Super Admin') {
    $_SESSION['user_type'] = 'Super Admin';
    
    // Include and run leave reset check
    require_once('auto_reset_leave_credits.php');
    
    // Create the logs table if it doesn't exist
    createLeaveResetLogsTable($conn);
    
    // Check and reset leaves if needed
    resetLeaveCredits($conn);
    
    // Redirect to dashboard
    header("Location: dashboard.php");
    exit();
}

// ... rest of the login code ... 
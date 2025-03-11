<?php 
session_start();

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ./");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// First query to check if the job satisfaction form is 'Open'
$sql_job_satisfaction = "
    SELECT status 
    FROM job_satisfaction_form_status 
    WHERE status = 'Open'
";

// Query to fetch leave requests with status, ordered by file_date in descending order
$sql_leave_applications = "
    SELECT 
        a.status,
        a.file_date,
        CONCAT(e.first_name, ' ', e.last_name) AS fullname
    FROM leave_applications a
    LEFT JOIN employees e ON e.employee_id = a.employee_id
    WHERE a.employee_id = $employee_id AND (a.status = 'Approved' OR a.status = 'Rejected')
    ORDER BY a.file_date DESC
";

// Execute queries
$result_job_satisfaction = mysqli_query($conn, $sql_job_satisfaction);
$result_leave_applications = mysqli_query($conn, $sql_leave_applications);

// Create a list for notifications
$notifications = [];

// Add job satisfaction notification if it's 'Open'
if ($result_job_satisfaction && mysqli_num_rows($result_job_satisfaction) > 0) {
    $notifications[] = [
        'type' => 'job_satisfaction',
        'message' => 'Job Satisfaction Form is Open',
        'timestamp' => null
    ];
}

// Add leave application notifications
if ($result_leave_applications && mysqli_num_rows($result_leave_applications) > 0) {
    while ($row = mysqli_fetch_assoc($result_leave_applications)) {
        $notifications[] = [
            'type' => 'leave_application',
            'message' => 'Your leave request was ' . htmlspecialchars($row['status']),
            'timestamp' => $row['file_date']
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESE-Tech Industrial Solutions Corporation</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="shortcut icon" type="x-icon" href="images/logo2.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .bell-btn {
            position: relative;
            cursor: pointer;
        }

        .notification-count {
            position: absolute;
            top: 30px;
            right: 165px;
            background-color: red;
            color: white;
            font-size: 12px;
            font-weight: bold;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .notification {
            display: none;
            position: absolute;
            top: 180px;
            right: -10px;
            transform: translate(-50%, -50%);
            background-color: #fff;
            width: 350px;
            height: auto;
            max-height: 300px;
            padding: 15px;
            overflow-y: auto;
            box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
            z-index: 100;
            border-radius: 8px;
        }

        .notification h3 {
            margin: 0px;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .notifications-content {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .notification-item {
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #f1f1f1;
            border-radius: 5px;
            display: flex;
            align-items: center;
            background-color: #f9f9f9;
            transition: background-color 0.3s ease;
        }

        .notification-item:hover {
            background-color: #f0f0f0;
        }

        .notification-item i {
            font-size: 20px;
            color: #ff9800;
            margin-right: 10px;
        }

        .notification-item p {
            margin: 0;
            font-size: 14px;
            color: #333;
        }

        .notification-item .timestamp {
            margin-left: auto;
            font-size: 12px;
            color: #888;
        }

        .header-right {
            display: relative;
        }

        .logo i {
            display: none;
        }
        @media (max-width: 768px) {
            .logo i {
                display: block;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: .5rem;
            }

            .logo img {
                width: 60px;
            }

            .header-right {
                gap: .5rem;
            }

            .profile strong {
                font-size: 12px;
            }

            .header-right i {
                font-size: 1rem;
            }

            .notification-count {
                top: 12px;
                right: 115px;

                font-size: 8px;
                width: 15px;
                height: 15px;
            }

            .notification {
                top: 105px;
                right: -15px !important;
                
                width: 200px;
            }

            .notification h3 {
                font-size: 14px;
            }

            .notification-item p {
                font-size: 10px;
            }
            .notification-item {
                padding: 8px;
                margin: 5px 0
            }

            .sidebar {
                transform: translateX(-1050px) !important;
            }
            
            .sidebar.show {
                transform: translateX(0) !important;
                height: 100%;
            }

            .sidebar ul li {
                margin-bottom: 5px;
            }

            .logo h1 {
                display: none;
            }

            .main-content {
          
                padding: 15px
            }

            input[type="text"],
            input[type="date"] {
                width: 95%;
            }

            .hover-area {
                display: none;
            }

            /* .evaluation-form input {
                width: 95%;
            } */
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <img src="images/logo1.png" alt="ESE-Tech Logo">
        <h1>ESE-Tech Industrial Solutions Corporation</h1>
        <i id="barBtn" class="fa-solid fa-bars"></i>
    </div>
    
    <div class="header-right">
        <i class="fas fa-bell bell-btn"></i>
        <!-- Notification count -->
        <?php if (count($notifications) > 0): ?>
            <div class="notification-count"><?php echo count($notifications); ?></div>
        <?php endif; ?>
        <div class="profile">
            <strong>
            <?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Guest'; ?>
            </strong>
        </div>
        <a href="./user_logout" class="logout-button">
            <i class="fas fa-power-off"></i> 
        </a> <!-- Logout button -->
    </div>

    <!-- Notification Dropdown -->
    <div class="notification">
        <h3>Notifications</h3>
        <div class="notifications-content">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item">
                        <i class="fas fa-file-alt"></i> 
                        <div>
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            <?php if ($notification['timestamp']): ?>
                                <span class="timestamp"><?php echo date('F j, Y', strtotime($notification['timestamp'])); ?></span> 
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No notifications</p>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    // Toggle notification box visibility on bell icon click
    $(".bell-btn").click(function() {
        $(".notification").toggle();
    });

</script>

</body>
</html>

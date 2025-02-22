<?php 
session_start();

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ./");
    exit();
}

// SQL query to fetch leave requests and job satisfaction survey data
$sql = "
    (SELECT 
        e.first_name,
        e.middle_name,
        e.last_name,
        'Leave Request' AS type,
        a.file_date AS action_date,
        CONCAT(e.first_name, ' ', e.last_name) AS fullname,
        NULL AS survey_date
    FROM leave_applications a
    LEFT JOIN employees e ON e.employee_id = a.employee_id
    WHERE a.status = 'Pending')
    
    UNION
    
    (SELECT 
        e.first_name,
        e.middle_name,
        e.last_name,
        'Job Satisfaction Survey' AS type,
        NULL AS action_date,
        CONCAT(e.first_name, ' ', e.last_name) AS fullname,
        js.survey_date AS survey_date
    FROM job_satisfaction_surveys js
    LEFT JOIN employees e ON e.employee_id = js.employee_id)
    
    ORDER BY action_date DESC, survey_date DESC;
";

// Execute the query
$result = mysqli_query($conn, $sql);

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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
        }

        .bell-btn {
            position: relative;
            cursor: pointer;
        }

        .notification {
            display: none;
            position: absolute;
            top: 200px;
            right: -20px;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            width: 350px;
            height: auto;
            max-height: 400px;
            padding: 20px;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .notification h3 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .notification .notification-item {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .notification .notification-item:hover {
            background-color: #f1f1f1;
        }

        .notification .notification-item .icon {
            font-size: 20px;
            margin-right: 15px;
        }

        .notification .notification-item .content {
            display: flex;
            flex-direction: column;
        }

        .notification .notification-item .content .title {
            font-weight: bold;
            color: #333;
        }

        .notification .notification-item .content .timestamp {
            font-size: 12px;
            color: #777;
        }

        .leave-request {
            background-color: #f9f9f9;
            border-left: 5px solid #4CAF50;
        }

        .survey-submission {
            background-color: #f0f8ff;
            border-left: 5px solid #1E90FF;
        }

        .empty-message {
            font-style: italic;
            color: #999;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="images/logo1.png" alt="ESE-Tech Logo">
        <h1>ESE-Tech Industrial Solutions Corporation</h1>
    </div>

    <div class="header-right">
        <i class="fas fa-bell bell-btn"></i>
        <div class="profile">
            <strong>
            <?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Guest'; ?>
            </strong>
        </div>
        <a href="./logout" class="logout-button">
            <i class="fas fa-power-off"></i>
        </a>
    </div>

    <div class="notification">
        <h3>Notifications</h3>
        <div class="notifications-content">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php 
                    $fullname = htmlspecialchars($row['fullname']);
                    $type = $row['type'];
                    $action_date = isset($row['action_date']) ? htmlspecialchars($row['action_date']) : null;
                    $survey_date = isset($row['survey_date']) ? htmlspecialchars($row['survey_date']) : null;

                    if ($type == 'Leave Request') {
                        $message = "$fullname filed a leave request";
                        $class = "leave-request";
                        $icon = "fas fa-calendar-day";
                        $timestamp = $action_date;
                    } else if ($type == 'Job Satisfaction Survey') {
                        $message = "$fullname submitted a job satisfaction survey";
                        $class = "survey-submission";
                        $icon = "fas fa-poll";
                        $timestamp = $survey_date;
                    }
                    ?>
                    <div class="notification-item <?php echo $class; ?>">
                        <i class="icon <?php echo $icon; ?>"></i>
                        <div class="content">
                            <div class="title"><?php echo $message; ?></div>
                            <div class="timestamp"><?php echo $timestamp; ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-message">No notifications</p>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    $(".bell-btn").click(function() {
        $(".notification").toggle();
    });
</script>

</body>
</html>

<?php 
session_start();

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Check if the user is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: ../index");
    exit();
}

// Count notifications
$sql_count = "
    SELECT COUNT(*) AS notification_count FROM (
        (SELECT a.file_date AS action_date FROM leave_applications a WHERE a.status = 'Pending')
        UNION ALL
        (SELECT js.survey_date AS survey_date FROM job_satisfaction_surveys js)
    ) AS notifications;
";
$result_count = mysqli_query($conn, $sql_count);
$row = mysqli_fetch_assoc($result_count);
$notification_count = $row['notification_count'];

// Fetch notifications
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

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .bell-btn {
            position: relative;
            cursor: pointer;
            font-size: 24px;
        }

        .notification-container {
            position: relative;
        }
        .badge {
            position: absolute;
            top: -15px;
            right: -15px;
            background-color: red;
            color: white;
            font-size: 12px;
            font-weight: bold;
            padding: 5px;
            border-radius: 50%;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            top: 75px;
            right: 160px;
            background-color: #ffffff;
            width: 350px;
            max-height: 400px;
            padding: 15px;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .notification-dropdown h3 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .notification-item {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
            cursor: pointer;
            background-color: #f9f9f9;
            border-left: 5px solid #4CAF50;
        }

        .survey-submission {
            border-left: 5px solid #1E90FF;
        }

        .notification-item:hover {
            background-color: #f1f1f1;
        }

        .notification-item .icon {
            font-size: 18px;
            margin-right: 10px;
        }

        .notification-item .content {
            display: flex;
            flex-direction: column;
        }

        .notification-item .content .title {
            font-weight: bold;
            color: #333;
        }

        .notification-item .content .timestamp {
            font-size: 12px;
            color: #777;
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
        <div class="notification-container">
            <i class="fas fa-bell bell-btn"></i>
            <?php if ($notification_count > 0): ?>
                <span class="badge"><?php echo $notification_count; ?></span>
            <?php endif; ?>

            <div class="notification-dropdown">
                <h3>Notifications</h3>
                <div class="notifications-content">
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php 
                            $fullname = htmlspecialchars($row['fullname']);
                            $type = $row['type'];
                            $action_date = isset($row['action_date']) ? htmlspecialchars($row['action_date']) : null;
                            $survey_date = isset($row['survey_date']) ? htmlspecialchars($row['survey_date']) : null;

                            $message = ($type == 'Leave Request') 
                                ? "$fullname filed a leave request" 
                                : "$fullname submitted a job satisfaction survey";

                            $class = ($type == 'Leave Request') ? "leave-request" : "survey-submission";
                            $icon = ($type == 'Leave Request') ? "fas fa-calendar-day" : "fas fa-poll";
                            $timestamp = $action_date ?? $survey_date;
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
        </div>

        <div class="profile">
            <strong>
                <?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Guest'; ?>
            </strong>
        </div>
        <a href="./logout" class="logout-button">
            <i class="fas fa-power-off"></i>
        </a>
    </div>
</header>

<script>
    $(".bell-btn").click(function() {
        $(".notification-dropdown").toggle();
    });

    $(document).click(function(event) {
        if (!$(event.target).closest(".notification-container").length) {
            $(".notification-dropdown").hide();
        }
    });
</script>

</body>
</html>

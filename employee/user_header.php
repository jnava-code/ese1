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

// SQL query to fetch leave requests with status, ordered by file_date in descending order
$sql = "
    SELECT 
        a.status,
        a.file_date,
        CONCAT(e.first_name, ' ', e.last_name) AS fullname
    FROM leave_applications a
    LEFT JOIN employees e ON e.employee_id = a.employee_id
    WHERE a.employee_id = $employee_id AND (a.status = 'Approved' OR a.status = 'Rejected')
    ORDER BY a.file_date DESC
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
        .bell-btn {
            position: relative;
            cursor: pointer;
        }

        .notification {
            display: none;
            position: absolute;
            top: 150px;
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
    </style>
</head>
<body>
<header>
    <div class="logo">
        <img src="images/logo1.png" alt="ESE-Tech Logo">
        <h1>ESE-Tech Industrial Solutions Corporation</h1>
    </div>
    
    <!-- Right-side Icons and Profile -->
    <div class="header-right">
        <i class="fas fa-bell bell-btn"></i>  <!-- Notifications Icon -->
        <div class="profile">
            <strong>
            <?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Guest'; ?>
            </strong>
        </div>
        <a href="./logout" class="logout-button">
            <i class="fas fa-power-off"></i> <!-- Shutdown icon -->
        </a> <!-- Logout button -->
    </div>

    <!-- Notification Dropdown -->
    <div class="notification">
        <h3>Notifications</h3>
        <div class="notifications-content">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="notification-item">
                        <i class="fas fa-file-alt"></i> 
                        <div>
                            <p>Your leave request was <strong><?php echo htmlspecialchars($row['status']); ?></strong></p>
                            <span class="timestamp"><?php echo $row['file_date']; ?></span> 
                        </div>
                    </div>
                <?php endwhile; ?>
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

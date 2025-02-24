<?php include('server.php') ?>
<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize error message
$error = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Define query array for all tables
    $queries = [
        [
            "query" => "SELECT id, username, password, first_name, last_name, user_type, is_archived FROM admin WHERE username = ? AND status = 1 AND is_archived = 0",
            "hashed" => true // For hashed passwords (admin table)
        ],
        [
            "query" => "SELECT id, employee_id, username, password, first_name, last_name, gender, user_type, employment_status, is_archived FROM employees WHERE username = ? AND e_status = 1 AND is_archived = 0",
            "hashed" => true // For plain-text passwords (employees table)
        ],
    ];

    // Loop through queries to find the user
    foreach ($queries as $entry) {
        $stmt = mysqli_prepare($conn, $entry['query']);
        mysqli_stmt_bind_param($stmt, 's', $username); // Bind the username parameter
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            // User found
            $row = mysqli_fetch_assoc($result);

            // Check if password is hashed or plain-text
            if ($entry['hashed']) {
                // For hashed passwords (admin table)
                if (password_verify($password, $row['password'])) {
                    $authenticated = true;
                } elseif ($password === $row['password']) {
                    $authenticated = true;
                } else {
                    $authenticated = false;
                }
            } 
            

            // If authenticated, start session and redirect
            if ($authenticated) {
                session_start();
                $_SESSION['username'] = $row['username'];
                $_SESSION['user_type'] = $row['user_type'];
                $_SESSION['fullname'] = $row['first_name'] . ' ' . $row['last_name'];
                $_SESSION['gender'] = $row['gender'] ?? ''; // If present
                $_SESSION['employee_id'] = $row['employee_id'] ?? ''; // If present
                $_SESSION['employment_status'] = $row['employment_status'] ?? ''; // If present
                if ($row['user_type'] == 1) {
                    $_SESSION['admin'] = true;
                } elseif ($row['user_type'] == 2) {
                    $_SESSION['staff'] = true;
                } elseif ($row['user_type'] == 3) {
                    $_SESSION['superadmin'] = true;
                } else {
                    $_SESSION['admin'] = false;
                    $_SESSION['staff'] = false;
                    $_SESSION['superadmin'] = false;
                }
                

                // Redirect based on user type
                switch ($row['user_type']) {
                    case 1: // Admin
                        header("Location: ./admin/dashboard");
                        break;
                    case 2: // Staff
                        header("Location: ./employee/user_leave");
                        break;
                    case 3: // Super Admin or Regular User
                        header("Location: ./superadmin/dashboard");
                        break;
                    default:
                        header("Location: ./index"); // Default redirection
                        break;
                }
                exit();
            } else {
                $error = "Invalid Username or Password.";
            }
        }
    }

    // If no user found or password doesn't match
    if (!$authenticated) {
        $error = "Invalid Username or Password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="shorcut icon" type="x-icon" href="images/icon1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Your existing code... -->


    <!-- bootstrap -->
<div class="header">
    POWER - DRIVES - INSTRUMENTATIONS - AUTOMATION

    <title>ESE-Tech Industrial Solutions Corporation - Login</title>
</div>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
        }
        .header {
            background-color: #cc0000;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 18px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .container {
            display: flex;
            height: 100%;
            padding-top: 40px; /* Add padding to account for fixed header */
        }
        .left-section {
            flex: 1;
            background-image: url('images/bg1.png');
            background-size: cover;
            background-position: center;
            position: relative;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .expertise {
            font-size: 36px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            text-align: center;
        }
        .right-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .logo {
            max-width: 300px;
            margin-bottom: 20px;
        }
        .welcome {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .login-form {
            width: 100%;
            max-width: 300px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .remember-me {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .sign-in-btn {
            width: 100%;
            padding: 10px;
            background-color: #cc0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        POWER - DRIVES - INSTRUMENTATIONS - AUTOMATION
    </div>
    <div class="container">
        <div class="left-section">
            <div class="expertise">Drives and<br>Automation Expert.</div>
        </div>
        <div class="right-section">
            <img src="images/logo.png" alt="ESE-Tech Logo" class="logo">
            <div class="welcome">Welcome back!</div>
            <p>Login to your account</p>
            <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
            <form class="login-form" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your Username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input" style="position: relative;">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required style="padding-right: 30px;">
                        <i id="eye-icon" class="fas fa-eye" onclick="togglePassword()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>
                </div>
                <button type="submit" class="sign-in-btn">Log In</button>
                <a href="./index" style="margin-top: 15px; display: inline-block;">Go to Attendance's Page</a>
            </form>
      
        </div>
    </div>

    
<!-- javascript -->
<script type="text/javascript" src="assets/custom/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="assets/custom/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="assets/toastr/js/toastr.min.js"></script>
    <script type="text/javascript" src="assets/mycustom/js/login.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            // Toggle the input type and the eye icon
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
<?php include('server.php') ?>
<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Initialize an error variable
$error = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prevent SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // SQL query to check if user exists
    $sql = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['username'] = $row['username'];
        $_SESSION['a_name'] = $row['a_name'];
        $_SESSION['user_type'] = $row['user_type'];

        // Redirect based on user type
        if ($row['user_type'] == 1) {
            header("Location: ../admin/dashboard");
        }
        exit();
    } else {
        $error = "Invalid username or password";
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


    <title>ESE-Tech Industrial Solutions Corporation - Login</title>
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
    <div class="welcome">Welcome back, Admin!</div>
    <p>Login to your account</p>
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
        <a href="../employee/login" style="margin-top: 15px; display: inline-block;"> Go to Employee's Page</a>
        <a href="../" style="margin-top: 15px; display: inline-block;"> Go to Attendance's Page</a>
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
} function toggleRememberMe() {
    const rememberCheckbox = document.getElementById('remember');
    const rememberIcon = document.getElementById('remember-icon');
    
    // Change the icon based on the checkbox state
    if (rememberCheckbox.checked) {
        rememberIcon.classList.remove('fa-square');
        rememberIcon.classList.add('fa-check-square');
    } else {
        rememberIcon.classList.remove('fa-check-square');
        rememberIcon.classList.add('fa-square');
    }
}
</script>
<?php if (!empty($error)): ?>
<script>
    // Show error message as a pop-up
    alert("<?php echo $error; ?>");
</script>
<?php endif; ?>
</body>
</html>     
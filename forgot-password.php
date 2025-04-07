<?php 
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    require 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/PHPMailer-6.9.3/PHPMailer-6.9.3/src/Exception.php';
    require 'vendor/PHPMailer-6.9.3/PHPMailer-6.9.3/src/PHPMailer.php';
    require 'vendor/PHPMailer-6.9.3/PHPMailer-6.9.3/src/SMTP.php';

    date_default_timezone_set('Asia/Manila'); // Adjust as per your timezone
    // Current date and time (defined globally)
    $current_day = date('l'); // Day of the week (e.g., Sunday)
    $cdate = date('F j, Y'); // Full date (e.g., November 24, 2024)
    $current_date = date('Y-m-d'); // YYYY-MM-DD format

    $email = '';
$error = '';
$success = '';

function sendResetPasswordLink($conn, $email, $token_hash, $token_expiry) {
    $email = mysqli_real_escape_string($conn, $email);

    $sql = "UPDATE employees SET 
            reset_token_hash = '$token_hash', 
            reset_token_expires_at = '$token_expiry'
            WHERE email = '$email'";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        return true;
    } else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    $token_expiry = date('Y-m-d H:i:s', time() + 3600);

    $sql = "SELECT * FROM employees WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        $error = 'Email address not found.';
    } else {
        if (sendResetPasswordLink($conn, $email, $token_hash, $token_expiry)) {
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; 
                    $mail->SMTPAuth = true;
                    $mail->Username = 'rroquero26@gmail.com'; 
                    $mail->Password = 'plxj aziw yqbo wkbs';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                                
                    $mail->setFrom('no-reply@yourwebsite.com', 'ESE-Tech Industrial Solutions Corporation System'); // Corrected "From" email
                    $mail->addAddress($email); 
                    $mail->Subject = 'Reset Password - ESE-Tech Industrial Solutions Corporation System';

                    $message = "Click <a href='http://localhost/ese1/reset-password?token=$token_hash'>here</a> to reset your password. <br>";

                    $mail->isHTML(true);
                    $mail->Body = $message;

                    if ($mail->send()) {
                        $success = 'A reset password link has been sent to your email address.';
                        unset($_POST['email']);
                    } else {
                        $_SESSION['error_message'] = "An error occurred: " . $stmt->error;
                    }
                } catch (Exception $e) {
                    $_SESSION['error_message'] = "Mailer Error: " . $mail->ErrorInfo;
                }
            
        } else {
            $error = 'There was an error updating your password reset token.';
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="shortcut icon" type="x-icon" href="images/icon1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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

.input-and-msg {
    display: flex;
    gap: 5px;
    flex-direction: column;
    align-items: center;
}

#message {
    color: green;
}

.reset_password {
    background-color: #cc0000;
    color: white;
    cursor: pointer;
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
    <h1>Forgot Password</h1>
    <form method="POST">
        <div class="form-group input-and-msg">
            <!-- <label for="employee_id">Employee ID</label> -->
            <input type="email" id="email" name="email" placeholder="Enter your Email" autofocusz >
            <span id="message"></span>
            <input type="submit" value="Reset Password" name="submit" class="btn reset_password">
        </div>
  
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <?php if (!empty($success)) echo "<div class='message'>$success</div>"; ?>
    </form>
</div>
</body>
</html>
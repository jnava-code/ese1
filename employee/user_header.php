<?php
include('header.php');

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ./login");
    exit();
}

// Get employee details
$username = $_SESSION['username'];
$query = "SELECT username, password FROM admin WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username); // 's' for string parameter
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
} else {
    echo "Employee not found.";
    exit();
}

// Oragonini*09

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Flag for errors
    $error_message = '';

    // // Update username validation
    // if (!empty($new_username)) {
    //     if (!preg_match('/^[a-zA-Z0-9]{8,9}$/', $new_username)) {
    //         $error_message = "Username must be 8-9 alphanumeric characters.";
    //     }
    // }

    // Update password validation
    if (!empty($new_password)) {
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{9,12}$/', $new_password)) {
            $error_message = "Password must be 9-12 characters long, include uppercase and lowercase letters, numbers, and special symbols.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        }
    }

    // If no errors, proceed with the update
    if (empty($error_message)) {
        // Prepare update query
        $update_query = "UPDATE admin SET username = COALESCE(?, username), password = COALESCE(?, password) WHERE username = ?";
        $update_stmt = $conn->prepare($update_query);

        // Hash the password if provided
        $hashed_password = !empty($new_password) ? password_hash($new_password, PASSWORD_DEFAULT) : null;

        // Bind parameters
        $update_stmt->bind_param('sss', $new_username, $hashed_password, $username);

        // Execute and check the result
        if ($update_stmt->execute()) {
            $success_message = "Details updated successfully!";
            if (!empty($new_username)) {
                // Update session variable
                $_SESSION['username'] = $new_username;
            }
        } else {
            $error_message = "Error updating details. Please try again.";
        }
    }
}

?>

<body>

<?php include('includes/sideBar.php'); ?>

<main class="main-content">
    <section id="dashboard">
        <h2>EDIT ADMIN PROFILE</h2>

        <!-- Main Form -->
        <form method="POST" class="evaluation-form">
                <!-- Display success or error messages -->
            <?php if (isset($success_message)): ?>
                <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
            <?php elseif (isset($error_message)): ?>
                <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <!-- Employee Username Section -->
            <div class="form-group">
                <label for="new_username">New Username:</label>
                <input type="text" id="new_username" name="new_username" 
                       value="<?php echo htmlspecialchars($employee['username']); ?>" 
                       required minlength="5" maxlength="9">
            </div>

            <!-- Password Update Section -->
            <div class="form-group password-form">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password">             
            </div>
            <div class="form-group password-form">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password">             
            </div>
            <button type="submit" class="btn">Update Username or Password</button>
        </form>
    </section>
</main>


<style>
    #dashboard h2{
        margin-left: 250px;
    }
    .header-title {
        text-align: center;
        margin-bottom: 30px;
    }

    .header-title h2 {
        font-size: 32px;
        color: #333;
    }

    .employee-info {
        background-color: #f5f5f5;
        padding: 15px;
        margin-bottom: 25px;
        border-radius: 5px;
        font-size: 18px;
        color: #333;
    }

    .password-form .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 8px;
        display: block;
    }

    .form-group input {
        width: 98.5%;
        padding: 10px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #fff;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        border-color: #6E7DFF;
        box-shadow: 0 0 5px rgba(110, 125, 255, 0.5);
    }

    .form-group button {
        background-color: #6E7DFF;
        color: #fff;
        padding: 12px 20px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .form-group button:hover {
        background-color: #5b6fc9;
    }

    .evaluation-form {
        display: flex;
        gap: 15px;
        flex-direction: column;
        justify-content: center;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        border-radius: 8px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .evaluation-form .form-group {
        margin-bottom: 15px;
    }

    .evaluation-form label {
        font-weight: bold;
        font-size: 14px;
        color: #333;
    }

    .success, .error {
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        margin-top: 20px;
    }

    .success {
        color: #4CAF50;
    }

    .error {
        color: #FF6B6B;
    }

.evaluation-form {
        max-width: 1500px;
        margin: 0 auto;
        padding: 20px;
        border-radius: 8px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .evaluation-form .form-group {
        margin-bottom: 15px;
    }

    .evaluation-form label {
        font-weight: bold;
        font-size: 14px;
        color: #333;
    }

    .evaluation-form input {
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.evaluation-form input:focus {
    border-color: #6E7DFF;
    box-shadow: 0 0 5px rgba(110, 125, 255, 0.5);
}

    .evaluation-form button {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }

    .evaluation-form button:hover {
        background-color: #0056b3;
    }

</style>
</body>
</html>

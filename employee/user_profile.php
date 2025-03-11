<?php
include('user_header.php');

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ./login");
    exit();
}

// Get employee details
$username = $_SESSION['username'];
$query = "SELECT username, password FROM employees WHERE username = ?";
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

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Flag for errors
    $error_message = '';

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
        $update_query = "UPDATE employees SET username = COALESCE(?, username), password = COALESCE(?, password) WHERE username = ?";
        $update_stmt = $conn->prepare($update_query);

        // Hash the password if provided
        $hashed_password = !empty($new_password) ? password_hash($new_password, PASSWORD_DEFAULT) : null;

        // Bind parameters
        $update_stmt->bind_param('sss', $new_username, $hashed_password, $username);

        // Execute and check the result
        if ($update_stmt->execute()) {
            $success_message = "Details updated successfully!";
            
            // Update session variable and username for display
            if (!empty($new_username)) {
                $_SESSION['username'] = $new_username;
                $username = $new_username; // Update $username for display
            }
        } else {
            $error_message = "Error updating details. Please try again.";
        }
    }
}
?>

<body>
<style>
    /* CSS styling */
    #dashboard h2 {
        margin-left: 600px;
    }
    .header-title {
        text-align: center;
        margin-bottom: 30px;
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
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 600px;
        margin: 0 auto;
        padding: 20px;
        border-radius: 8px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        /* width: 100%; */
    }
    .evaluation-form .form-group {
        width: 100%;
        margin-bottom: 15px;
    }
    .evaluation-form label {
        font-weight: bold;
        font-size: 14px;
        color: #333;
    }
    .evaluation-form input {
        width: 97%;
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

    @media (max-width: 768px) {
        #dashboard h2 {
        margin-left: 0px;
    }

    .evaluation-form {
        width: 300px;
    }
    }
</style>
<?php include('includes/sideBar.php'); ?>

<main class="main-content">
    <section id="dashboard">
        <h2>EDIT USER PROFILE</h2>

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
                       value="<?php echo htmlspecialchars($username); ?>" 
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


</body>
</html>

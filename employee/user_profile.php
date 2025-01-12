<?php
include('user_header.php');

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ./");
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

// Oragonini*09

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate username (8-9 characters)
    if (preg_match('/^[a-zA-Z0-9]{8,9}$/', $new_username)) {
        // Validate password strength
        if (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{9,12}$/', $new_password)) {
            if ($new_password === $confirm_password) {
                // Directly use the password (no hashing)
                $plain_password = $new_password;

                // Update the username and password in the database
                $update_query = "UPDATE employees SET username = ?, password = ? WHERE username = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param('sss', $new_username, $plain_password, $username);

                if ($update_stmt->execute()) {
                    $success_message = "Username and password updated successfully!";
                    // Update session variable
                    $_SESSION['username'] = $new_username;
                } else {
                    $error_message = "Error updating details. Please try again.";
                }
            } else {
                $error_message = "Passwords do not match.";
            }
        } else {
            $error_message = "Password must be 9-12 characters long, include uppercase and lowercase letters, numbers, and special symbols.";
        }
    } else {
        $error_message = "Username must be 8-9 alphanumeric characters.";
    }
};
?>

<body>

<?php include('includes/sideBar.php'); ?>

<main class="main-content">
    <section id="dashboard">
        <h2>EDIT USER PROFILE</h2>

        <!-- Main Form -->
        <form method="POST" class="evaluation-form">
            <!-- Employee Username Section -->
            <div class="form-group">
                <label for="new_username">New Username:</label>
                <br>
                <br>
                <input type="text" id="new_username" name="new_username" 
                       value="<?php echo htmlspecialchars($employee['username']); ?>" 
                       required minlength="8" maxlength="9">
            </div>

            <!-- Password Update Section -->
            <div class="form-group password-form">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">Update to system</button>
        </form>

        <!-- Display success or error messages -->
        <?php if (isset($success_message)): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php elseif (isset($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </section>
</main>


<style>
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

    .password-form label {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 8px;
        display: block;
    }

    .password-form input {
        width: 100%;
        max-width: 300px; /* Adjusted max-width for smaller input fields */
        padding: 10px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #fff;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .password-form input:focus {
        border-color: #6E7DFF;
        box-shadow: 0 0 5px rgba(110, 125, 255, 0.5);
    }

    .password-form button {
        background-color: #6E7DFF;
        color: #fff;
        padding: 12px 20px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .password-form button:hover {
        background-color: #5b6fc9;
    }

    .evaluation-form {
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
    width: 100%;
    max-width: 300px; /* Adjusted max-width to match the password field */
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
<script>
document.querySelector("form").addEventListener("submit", function(event) {
    const username = document.getElementById("new_username").value;
    const password = document.getElementById("new_password").value;
    const confirmPassword = document.getElementById("confirm_password").value;

    // Username validation (8-9 alphanumeric characters)
    const usernameRegex = /^[a-zA-Z0-9]{8,9}$/;
    if (!usernameRegex.test(username)) {
        event.preventDefault();
        alert("Username must be 8-9 alphanumeric characters.");
        return false;
    }

    // Password validation
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{9,12}$/;
    if (!passwordRegex.test(password)) {
        event.preventDefault();
        alert("Password must be 9-12 characters long, include uppercase and lowercase letters, numbers, and special symbols.");
        return false;
    }

    // Password match validation
    if (password !== confirmPassword) {
        event.preventDefault();
        alert("Passwords do not match.");
        return false;
    }
});
</script>
</body>
</html>
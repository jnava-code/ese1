<?php 
// Starting the session
// session_start();
  
// Declaring and hoisting the variables
$e_username = "";
$a_name = "";
$errors = array(); 
$_SESSION['success'] = "";
  
// DBMS connection
$db = mysqli_connect('localhost', 'root', '', 'esetech');

// User login
if (isset($_POST['login_user'])) {
     
    // Data sanitization to prevent SQL injection
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    $is_archived = mysqli_real_escape_string($db, $_POST['is_archived']);
  
    // Error message if the input field is left blank
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }
  
    // Checking for the errors
    if (count($errors) == 0) {
         
        // Password matching
        $password = md5($password);
         
        $query = "SELECT employee_id, username FROM employees 
                  WHERE username='$username' AND password='$password' AND is_archived=`0`";
        $results = mysqli_query($db, $query);
  
        // If one user is found
        if (mysqli_num_rows($results) == 1) {
            $user = mysqli_fetch_assoc($results);

            // Storing user information in session variables
            $_SESSION['username'] = $user['username'];
            $_SESSION['employee_id'] = $user['employee_id']; // Fetching from DB
             
            // Welcome message
            $_SESSION['success'] = "You have logged in!";
             
            // Redirect to another page after successful login
            header('location: ../employee/leave');
        } else {
            // If the username and password don't match
            array_push($errors, "Username or password incorrect"); 
        }
    }
}
?>

<?php 
 
// Starting the session, necessary
// for using session variables
session_start();
  
// Declaring and hoisting the variables
$username = "";
$a_name    = "";
$errors = array(); 
$_SESSION['success'] = "";
  
// DBMS connection code -> hostname,
// username, password, database name
$db = mysqli_connect('localhost', 'root', '', 'esetech');


// User login
if (isset($_POST['login_user'])) {
     
    // Data sanitization to prevent SQL injection
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    $a_name = mysqli_real_escape_string($db, $_POST['a_name']);
  
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
         
        $query = "SELECT * FROM users WHERE username=
                '$username' AND password='$password'";
        $results = mysqli_query($db, $query);
  
        // $results = 1 means that one user with the
        // entered employee id exists
        if (mysqli_num_rows($results) == 1) {
             
            // Storing username in session variable
            $_SESSION['username'] = $username;
            $_SESSION['a_name'] = $a_name;
             
            // Welcome message
            $_SESSION['success'] = "You have logged in!";
             
            // Page on which the user is sent
            // to after logging in
            header('location: ../admin/dashboard');
        }
        else {
             
            // If the username and password doesn't match
            array_push($errors, "Username or password incorrect"); 
        }
    }
}
  
?>
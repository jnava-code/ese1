<?php include('header.php'); ?>
    <?php
    // Database connection
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Fetch employee details for editing
    $employee = null;
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM employees WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $employee = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }   
?>

<nav class="sidebar">
    <ul>
        <li><a href="./dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="./employees"><i class="fas fa-user-friends"></i> Employees Profile</a></li>
        <li class="dropdown">
            <a href="#attendance-dropdown" class="dropdown-toggle"><i class="fas fa-calendar-check"></i> Attendance Management</a>
            <ul class="dropdown-menu" id="attendance-dropdown">
                <li><a href="./daily-attendance">Daily Attendance</a></li>
                <li><a href="./monthly-attendance">Monthly Attendance</a></li>
            </ul>
        </li>
        <li><a href="./leave"><i class="fas fa-paper-plane"></i> Request Leave</a></li>
        <li><a href="./predict"><i class="fas fa-chart-line"></i> Prediction</a></li>
        <li><a href="./reports"><i class="fas fa-file-alt"></i> Reports</a></li>
        <li><a href="./performance-evaluation"><i class="fas fa-trophy"></i> Performance</a></li>
        <li><a href="./satisfaction"><i class="fas fa-smile"></i> Satisfaction</a></li>
    </ul>
</nav>



    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    </head>

    <body>
        <main class="main-content">
                <?php if ($employee): ?>
                <div class="title-and-btn">
                    <h2>RESUME</h2> 
                    <a href="./view_employee?id=<?php echo $employee['id']?>" class="btn btn-cancel">Back</a>
                </div>
                
                <div class="file_content">
                        <?php
                        // Assuming $employee['file'] contains the binary data of the file (either image or PDF)
                        $file_path = "../files/" . $employee['resume'];
                        $file_type = $employee['resume_type'];

                        if (!empty($employee['resume']) && file_exists($file_path)) {
                            // Check file type
                            if (strpos($file_type, 'image/') === 0) {
                                echo '<img src="' . $file_path . '" alt="Employee Resume File" />';
                            } elseif ($file_type === 'application/pdf') {
                                echo '<iframe class="file" src="' . $file_path . '" width="600" height="500" frameborder="0">
                                        This browser does not support PDFs. <a href="' . $file_path . '">Download the PDF</a>.
                                      </iframe>';
                            } else {
                                echo 'Unsupported file type: ' . htmlspecialchars($file_type);
                            }
                        } else {
                            echo 'No file data available or file not found.<br>';
                        }
                        
                    ?>
                    <?php else: ?>
                        <p class="text-danger">Employee not found.</p>
                    <?php endif; ?>
                </div>
            </section>           
        </main>

        


<style>
.title-and-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.file_content {
    display: flex;
    justify-content: center;
}
.file {
    width: 100%;
    height: 1000px;
}
.btn {
    margin-top: 0px !important;
}
        /* Sidebar Dropdown */
.sidebar ul .dropdown {
    position: relative;
}

.sidebar ul .dropdown .dropdown-toggle {
    cursor: pointer;
}

.sidebar ul .dropdown .dropdown-menu {
    display: none; /* Hide by default */
    list-style: none;
    padding: 0;
    margin: 0;
    background-color: #a83a3a;
}

.sidebar ul .dropdown .dropdown-menu li a {
    padding-left: 2rem; /* Indent for dropdown items */
    display: block;
    color: #fff;
}

.sidebar ul .dropdown .dropdown-menu li a:hover {
    background-color: #c45b5b;
}

/* Show dropdown menu when the parent is active */
.sidebar ul .dropdown.active .dropdown-menu {
    display: block; /* Show the dropdown */
}

/* Optional styling for active links */
.sidebar ul li a.active {
    background-color: #c45b5b;
}
</style>

<script>
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function (event) {
        const parent = this.parentElement;

        // Prevent the link's default behavior
        event.preventDefault();

        // Toggle the active class
        parent.classList.toggle('active');
    });
});

</script>
        <?php include('footer.php'); ?>
    </body>
    </html>

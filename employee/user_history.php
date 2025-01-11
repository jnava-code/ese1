<?php include('user_header.php'); ?>

<nav class="sidebar">
    <ul>
        <li><a href="./user_leave"><i class="fas fa-paper-plane"></i> Leave Application</a></li> <!-- Paper plane icon for leave application -->
        <li><a href="./user_satisfaction"><i class="fas fa-smile"></i> Satisfaction</a></li> <!-- Smile icon for satisfaction -->
    </ul>
</nav>

<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>HISTORY LOGS</h2>

    </section>
</main>

<?php
// Close the database connection
mysqli_close($conn);  
include('user_footer.php');
?>

<?php include('header.php'); ?>


<nav class="sidebar">
    <ul>
        <li><a href="./dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="./employees"><i class="fas fa-user-friends"></i> Employees Profile</a></li>
        
        <li class="dropdown">
            <a href="javascript:void(0);" class="dropdown-toggle">
                <i class="fas fa-calendar-check"></i> Attendance Management <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="dropdown-menu">
                <li><a href="./daily-attendance">Daily Attendance</a></li>
                <li><a href="./monthly-attendance">Monthly Attendance</a></li>
            </ul>
        </li>

        <li><a href="./leave"><i class="fas fa-paper-plane"></i> Request Leave</a></li>
        <li><a href="./predict"><i class="fas fa-chart-line"></i> Prediction</a></li>
        <li><a href="./recommendation"><i class="fas fa-lightbulb"></i> Recommendation</a></li>
        <li><a href="./reports"><i class="fas fa-file-alt"></i> Reports</a></li>
        <li><a href="./performance-evaluation"><i class="fas fa-trophy"></i> Performance</a></li>
        <li><a href="./satisfaction"><i class="fas fa-smile"></i> Satisfaction</a></li>

    </ul>
</nav>




<!-- Main Content Area -->
<main class="main-content">
        <section id="dashboard">
            <h2>RECOMMENDATION</h2>
            <!-- Content for the Dashboard -->
        </section>
    </main>
</div>
<style>
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

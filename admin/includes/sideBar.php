<?php
?>
<!-- ITO NA YUNG SIDEBAR PANEL SA ADMIN SIDE PAGE. explanation bakit nakabukod at nakainclude para di madami yung code nyo para sa iisang function lang naman. redundancy -->
<div class="hover-area"></div>
<nav class="sidebar">
    <div class="sidebar-content">
        <div class="logo">
            <img src="images/logo1.png" alt="ESE-Tech Logo">
        </div>
        <ul>

            <li><a href="./dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

            <?php if (isset($_SESSION['superadmin'])): ?>
                <li class="dropdown">
                    <a href="javascript:void(0);" class="dropdown-toggle">
                        <i class="fas fa-user-cog"></i> Manage Admin <i class="fas fa-chevron-down toggle-icon"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="./superadmin">Admins Profile</a></li>
                        <li><a href="./admin_archive">Archive</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle">
                    <i class="fas fa-user-friends"></i> Employees Profile<i class="fas fa-chevron-down toggle-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="./employees">Employees Profile</a></li>
                    <li><a href="./archive">Archive</a></li>
                    <li><a href="./department">Department</a></li>
                </ul>
            </li>
            
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle">
                    <i class="fas fa-calendar-check"></i>Attendance Monitoring<i class="fas fa-chevron-down toggle-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="./daily-attendance">Daily Attendance</a></li>
                    <li><a href="./weekly-attendance">Weekly Attendance</a></li>
                    <li><a href="./monthly-attendance">Monthly Attendance</a></li>
                    <li><a href="./report-attendance">Attendance Report</a></li>
                </ul>
            </li>
            
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle">
                        <i class="fas fa-paper-plane"></i> Leave Monitoring<i class="fas fa-chevron-down toggle-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="./leave">Request Leave</a></li>
                    <li><a href="./remaining_leave">Remaining Leave</a></li>
                </ul>
            </li>
            
            <li><a href="./predict"><i class="fas fa-chart-line"></i> Prediction</a></li>
            <li><a href="./reports"><i class="fas fa-file-alt"></i> Reports</a></li>
            <li><a href="./performance-evaluation"><i class="fas fa-trophy"></i> Performance</a></li>
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle">
                    <i class="fas fa-smile"></i> Satisfaction<i class="fas fa-chevron-down toggle-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="./satisfaction">Satisfaction Monitoring</a></li>
                    <li><a href="./edit_satisfaction_criteria">Edit Satisfaction Criteria</a></li>
                </ul>
                <li><a href="./user_profile"><i class="fas fa-history"></i> User Profile</a></li>
            </li>            
            

        </ul>
    </div>
</nav>
<?php
?>
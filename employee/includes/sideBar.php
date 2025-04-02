<?php
?>
<div class="hover-area"></div>
<nav class="sidebar">
    <div class="sidebar-content">
        <!-- Logo Section -->
        <div class="logo">
            <img src="images/logo1.png" alt="ESE-Tech Logo">
            <i id="closeSideBar" class="fa-solid fa-xmark"></i>
        </div>
        <!-- Navigation Links -->
        <ul>
            <li><a href="./user_leave"><i class="fas fa-paper-plane"></i> Leave Application</a></li>
            <li><a href="./user_satisfaction"><i class="fas fa-smile"></i> Satisfaction</a></li>
            <li><a href="./user_profile"><i class="fas fa-user"></i> Manage Profile</a></li>
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle">
                        <i class="fas fa-clock"></i> History<i class="fas fa-chevron-down toggle-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="./user_history">Leave</a></li>
                    <li><a href="./performance_history">Performance</a></li>
                     <li><a href="./satisfaction_history">Satisfaction</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<script>
    
    const barBtn = document.getElementById('barBtn');
    const closeSideBar = document.getElementById('closeSideBar');
    
    barBtn.onclick = function() {
        document.querySelector('.sidebar').classList.add('show');
    }

    closeSideBar.onclick = function() {
        document.querySelector('.sidebar').classList.remove('show');
    }
</script>
<?php
?>
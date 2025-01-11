<?php
include('header.php');
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

?>


<nav class="sidebar">
    <ul>
        <li><a href="./dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="./superadmin"><i class="fas fa-user-friends"></i> Manage Admins</a></li>
    </ul>
</nav>

    <style>
    /* Dashboard cards */
    .dashboard-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .dashboard-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        flex: 1;
        text-align: center;
        min-width: 200px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .dashboard-card h3 {
        font-size: 24px;
        margin-bottom: 10px;
    }

    .dashboard-card p {
        font-size: 18px;
        color: #666;
    }

    .dashboard-card .icon {
        font-size: 36px;
        margin-bottom: 10px;
        color:#9c1111;
    }
</style>



<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>DASHBOARD</h2>

    </section>
</main>
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

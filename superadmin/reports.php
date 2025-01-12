<?php include('header.php'); ?>



<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>



<!-- Main Content Area -->
<main class="main-content">
        <section id="dashboard">
            <h2>GENERATE REPORTS</h2>
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

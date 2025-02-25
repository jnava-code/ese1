<?php include('header.php'); ?>


<?php include('includes/sideBar.php'); ?>




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

<?php include('header.php'); ?>

<!-- Sidebar Panel -->
<?php include('includes/sideBar.php'); ?>

<?php
    // Database connection
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />

<main class="main-content">
    <section id="dashboard">
        <div class="performance-and-button">
            <h2>EMPLOYEE SATISFACTION MONITORING</h2>
            <a href="satisfaction" class="btn btn-danger">BACK</a>
        </div>

        <div class="evaluation-form">
            <div class="form-group">
                <?php 
                    $id = $_GET['id'];

                    $satisfaction_sql = "
                        SELECT 
                            jss.*,
                            e.*
                        FROM job_satisfaction_surveys jss
                        LEFT JOIN employees e ON jss.employee_id = e.employee_id
                        WHERE survey_id = '$id'";
                    $satisfaction_result = mysqli_query($conn, $satisfaction_sql);
                    if($satisfaction_result) {
                        while($row = mysqli_fetch_assoc($satisfaction_result)) {
                            // Decode the JSON string into an associative array
                            $questions = json_decode($row['questions'], true);  
                ?>       

                <div class="form-row">
                    <div class="col-md-6">
                        <label>Employee Name</label>
                        <input type="text" class="form-control" value="<?php echo $row['middle_name'] == "" ? $row['first_name'] . ' '. $row['last_name'] : $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']; ?>" readonly>
                    </div>
                </div>

                <!-- Loop through each question dynamically -->
                <div class="form-row">
                    <?php
                        // Loop through the questions and dynamically generate the input fields
                        foreach ($questions as $question => $score) {
                            // Extract question name (remove "question_" prefix)
                            $question_label = str_replace('question_', '', $question);
                    ?>

                        
                            <div class="col-md-6">
                                <label><?php echo ucfirst(str_replace('_', ' ', $question_label)); ?></label>  <!-- Format the label -->
                                <input type="text" class="form-control" value="<?php echo $score; ?>" readonly>
                            </div>
                        

                    <?php
                        }
                    ?>
                </div>
                <div class="form-row">
                    <div class="col-md-6">
                        <label>Survey Date</label>
                        <input type="text" class="form-control" value="<?php echo $row['survey_date']?>" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Rating</label>
                        <input type="text" class="form-control" value="<?php echo $row['overall_rating']?>" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Feedback</label>
                        <input type="text" class="form-control" value="<?php echo $row['rating_description']?>" readonly>
                    </div>
                </div>

                <?php       
                        }
                    }
                ?>
            </div>
        </div>
    </section>
</main>

<style>
    .evaluation-form {
        max-width: 1500px;
        margin: 0 auto;
        padding: 20px;
        border-radius: 8px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .evaluation-form .form-group {
        margin-bottom: 15px;
    }

    .evaluation-form label {
        font-weight: bold;
        font-size: 14px;
        color: #333;
    }

    .evaluation-form input,
    .evaluation-form select,
    .evaluation-form textarea {
        width: 100%;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .evaluation-form input[type="number"] {
        width: 60px;
    }

    .evaluation-form button {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }

    .evaluation-form button:hover {
        background-color: #0056b3;
    }
  
    /* Table Styling */
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
    }

    td {
        color: #555;
    }

    tr:hover {
        background-color: #f9f9f9;
    }

    .notification {
        margin: 20px auto;
        padding: 15px;
        text-align: center;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        max-width: 800px;
    }

    .notification.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .notification.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .notification.status-open {
        background-color: #e3f7df;
        color: #0f5132;
        border: 1px solid #d1e7dd;
    }

    .notification.status-closed {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }
</style>

<script>
        // Toggle dropdown on click
        $('.dropdown-toggle').click(function (event) {
            event.preventDefault();
            $(this).parent().toggleClass('active');
        });

        $(document).ready(function () {
            // Initialize DataTable
            $('#myTable').DataTable();
        });
</script>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<?php include('footer.php'); ?>

<?php
    ob_start();

        // Database connection
        $conn = mysqli_connect('localhost', 'root', '', 'esetech'); // Update with actual credentials

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        include('header.php');
        // Fetch employees for the evaluation form
        $sql = "SELECT * FROM employees";
        $employees_result = mysqli_query($conn, $sql);

        // Fetch evaluation data
        $query = "SELECT 
            performance_evaluations.evaluation_date, 
            performance_evaluations.status, 
            performance_evaluations.overall_score, 
            performance_evaluations.comments, 
            employees.employee_id, 
            employees.first_name, 
            employees.last_name, 
            performance_evaluations.remarks,
            (performance_evaluations.overall_score / 5) AS performance_score
            FROM performance_evaluations 
            JOIN employees ON performance_evaluations.employee_id = employees.employee_id 
            ORDER BY performance_evaluations.evaluation_date DESC";

        $result = mysqli_query($conn, $query); // Execute the query

        if (!$result) {
            echo "<p>Error fetching evaluations: " . mysqli_error($conn) . "</p>";
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $employee_id = $_POST['employee_id'];
            $evaluation_date = $_POST['evaluation_date'];
            $admin_id = $_SESSION['id'];
            $comments = mysqli_real_escape_string($conn, $_POST['comments']);

            // Fetch performance criteria from the database
            $performance_sql = "SELECT * FROM performance_criteria WHERE is_archived = 0";
            $performance_result = mysqli_query($conn, $performance_sql);
        
            if (!$performance_result) {
                die("Error fetching performance criteria: " . mysqli_error($conn));
            }
        
            // Initialize an array to store criteria ratings
            $criteria = [];
        
            // Loop through the criteria to collect submitted ratings
            while ($row = mysqli_fetch_assoc($performance_result)) {
                $name_attribute = strtolower(str_replace(' ', '_', $row['description']));
                if (isset($_POST[$name_attribute])) {
                    $criteria[$row['description']] = (int)$_POST[$name_attribute];
                }
            }

            // Calculate overall score
            $overall_score = array_sum($criteria) / count($criteria);

            // Determine remarks based on decimal overall score
            if ($overall_score >= 4.51 && $overall_score <= 5) {
                $remarks = "Very Effective";
            } elseif ($overall_score >= 3.51 && $overall_score <= 4.50) {
                $remarks = "Effective";
            } elseif ($overall_score >= 2.51 && $overall_score <= 3.50) {
                $remarks = "Satisfactory";
            } elseif ($overall_score >= 1.51 && $overall_score <= 2.50) {
                $remarks = "Low";
            } elseif ($overall_score >= 1.00 && $overall_score <= 1.50) {
                $remarks = "Need Guidance";
            } else {
                $remarks = "Unspecified"; // In case of an unexpected score
            }              

            // Insert evaluation data into the database
            $insert_sql = "INSERT INTO performance_evaluations (
                employee_id, 
                admin_id, 
                evaluation_date, 
                criteria, 
                comments, 
                overall_score, 
                status, 
                remarks
            ) VALUES (
                '$employee_id', 
                '$admin_id', 
                '$evaluation_date', 
                '" . mysqli_real_escape_string($conn, json_encode($criteria)) . "', 
                '$comments', 
                '$overall_score', 
                'Completed', 
                '$remarks'
            )";

            if (mysqli_query($conn, $insert_sql)) {
                $_SESSION['evaluation_message'] = "<p style='color:green;'>Evaluation submitted successfully!</p>";
                header("Location: performance-evaluation");
                exit;
            } else {
                echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
            }
        }
        
        $admin_id = $_SESSION['id'];

        // Fetch employees eligible for evaluation
        $eligible_employees_sql = "
            SELECT e.*
            FROM employees e
            LEFT JOIN (
                SELECT employee_id, MAX(evaluation_date) AS last_eval_date 
                FROM performance_evaluations 
                WHERE admin_id = '$admin_id'
                GROUP BY employee_id
            ) pe ON e.employee_id = pe.employee_id
            WHERE pe.last_eval_date IS NULL OR pe.last_eval_date <= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        ";

        $eligible_employees_result = mysqli_query($conn, $eligible_employees_sql);

        ?>
<?php

    include('includes/sideBar.php');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<main class="main-content">
    <section id="dashboard">
        <div class="performance-and-button">
            <h2>PERFORMANCE EVALUATION</h2>
            <a href="edit_performance_criteria" class="btn btn-danger">Edit Performance Criteria</a>
        </div>      
        <?php if (isset($_SESSION['evaluation_message'])): ?>

        <?php echo $_SESSION['evaluation_message']; ?>
            <?php unset($_SESSION['evaluation_message']); ?>
        <?php endif; ?>

        <form method="POST" class="evaluation-form">
            <div class="form-group">
                <label for="employee_id">Employee:</label>
                <select name="employee_id" required class="form-control select2">
                    <?php 
                    while ($row = mysqli_fetch_assoc($eligible_employees_result)) { 
                    ?>
                        <option value="<?php echo $row['employee_id']; ?>">
                            <?php echo $row['first_name'] . " " . $row['last_name']; ?>
                        </option>
                    <?php 
                    } 
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="evaluation_date">Evaluation Date:</label>
                <input type="date" name="evaluation_date" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <h3>Performance Criteria</h3>

            <!-- Performance Criteria (looping through rows) -->
            <div class="form-group">
            <?php
    // Query to get performance criteria
    $performance_sql = "SELECT * FROM performance_criteria WHERE is_archived = 0";
    $performance_result = mysqli_query($conn, $performance_sql);

    if ($performance_result) {
        $counter = 1;  // Initialize counter to 1

        // Loop through each row of the query result
        while ($row = mysqli_fetch_assoc($performance_result)) {
            // Convert description to lowercase and replace spaces with underscores for the name attribute
            $name_attribute = strtolower(str_replace(' ', '_', $row['description']));
                ?>
                    <!-- Display the label with the counter (e.g., "1. Job Knowledge (1-5)") -->
                    <label for="<?php echo $name_attribute; ?>">
                        <?php echo $counter . '. ' . $row['title']; ?> (1-5): <br>
                    </label>
                    <span class="evaluation_description">- <?php echo $row['description']; ?></span>
                    <div class="radio-choices">
                        <div class="radio-choice">
                            <label class="radio-label">
                                <input type="radio" name="<?php echo $name_attribute; ?>" value="5">
                                5. Very Effective
                            </label>
                        </div>
                        <div class="radio-choice">
                            <label class="radio-label">
                                <input type="radio" name="<?php echo $name_attribute; ?>" value="4">
                                4. Effective
                            </label>
                        </div>
                        <div class="radio-choice">
                            <label class="radio-label">
                                <input type="radio" name="<?php echo $name_attribute; ?>" value="3">
                                3. Satisfactory
                            </label>
                        </div>
                        <div class="radio-choice">
                            <label class="radio-label">
                                <input type="radio" name="<?php echo $name_attribute; ?>" value="2">
                                2. Low
                            </label>
                        </div>
                        <div class="radio-choice">
                            <label class="radio-label">
                                <input type="radio" name="<?php echo $name_attribute; ?>" value="1">
                                1. Need Guidance
                            </label>
                        </div>
                    </div>

                <?php
                            // Increment the counter for the next row
                            $counter++;
                        }
                    }
                ?>

            </div>

            <div class="form-group">
                <label>Overall Score:</label>
                <div id="overallScore">0</div>
            </div>

            <div class="form-group">
                <label for="comments">Comments:</label>
                <textarea name="comments" rows="4" placeholder="Enter any additional comments" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Evaluation</button>
        </form> 
        <br>
        <table id="myTable" class="evaluation-table">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Full Name</th>
                    <th>Evaluation Date</th>
                    <th>Overall Score</th>
                    <th>Percentage</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Remarks</th> <!-- Remarks Column -->
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['employee_id'] . "</td>";
                        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                        echo "<td>" . $row['evaluation_date'] . "</td>";
                        echo "<td>" . $row['overall_score'] . "</td>";
                        echo "<td>" . $row['performance_score'] * 100 . "%" . "</td>";
                        echo "<td>" . $row['comments'] . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>" . (isset($row['remarks']) ? $row['remarks'] : 'No Remarks') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No evaluations found.</td></tr>"; 
                }
                ?>
            </tbody>
        </table>
    </section>
</main>

<?php
    include('footer.php');
    mysqli_close($conn); // Move mysqli_close to the end of the script
?>


<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

$(document).ready(function () {
    if ($('#myTable thead th').length === $('#myTable tbody tr:first-child td').length) {
        $('#myTable').DataTable({
            "ordering": false 
        });
    } else {
        console.error('Column count mismatch between <thead> and <tbody>');
    }

    // Initialize Select2 on the employee dropdown
    $('.select2').select2({
        placeholder: 'Select an employee',
        allowClear: true
    });
});


    $(document).ready(function () {
        // Initialize Select2 on the employee dropdown
        $('.select2').select2({
            placeholder: 'Select an employee',
            allowClear: true,
            width: '100%' // Ensure the dropdown is responsive
        });
    });

    // SELECT THE CLASS NAME
    const employeeDisplay = document.querySelectorAll(".employee_display");

    employeeDisplay.forEach(display => {
        // Apply format: 00-000
        display.textContent = display.textContent.slice(0, 2) + '-' + display.textContent.slice(2, 5);
    });

    // Calculate overall score
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    const overallScoreDisplay = document.getElementById('overallScore');

    function updateOverallScore() {
        let total = 0;
        let count = 0;
        
        radioButtons.forEach(radio => {
            if (radio.checked) {
                total += parseInt(radio.value);
                count++;
            }
        });

        const average = count > 0 ? (total / count).toFixed(2) : 0;
        overallScoreDisplay.textContent = average;
    }

    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateOverallScore);
    });
</script>
<style>
    .evaluation-form {
        max-width: 100%; /* Ensure the form does not exceed the container width */
        overflow: hidden; /* Prevent overflow */
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-control {
        width: 100%; /* Make the form control elements adjustable */
    }
    .radio-choices {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .radio-choice {
        flex: 1 1 100px; /* Adjust the width of each radio choice */
    }
    .evaluation-form .radio-content {
        margin-top: 25px;
    }
    
    #overallScore {
        font-size: 1.2em;
        font-weight: bold;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 4px;
        display: inline-block;
    }
    .form-group {
        margin-bottom: 15px;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        font-size: 1rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .form-control:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    .select2-container .select2-selection--single {
        height: 35px; /* Match the height of the input field */
        padding: 5px 10px;
        font-size: 1rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px; /* Center the text vertically */
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px; /* Match the height of the input field */
    }
</style>
<?php
    include('header.php');
    include('includes/sideBar.php');
?>

<main class="main-content">
    <section id="dashboard">
        <div class="performance-and-button">
            <h2>PERFORMANCE EVALUATION</h2>
            <a href="edit_performance_criteria.php" class="btn btn-danger">Edit Performance Criteria</a>
        </div>      
        
        <?php
        // Database connection
        $conn = mysqli_connect('localhost', 'root', '', 'esetech'); // Update with actual credentials

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Fetch employees for the evaluation form
        $sql = "SELECT * FROM employees";
        $employees_result = mysqli_query($conn, $sql);

        // Fetch evaluation data
        $query = "SELECT 
            performance_evaluations.evaluation_date, 
            performance_evaluations.status, 
            performance_evaluations.overall_score, 
            employees.employee_id, 
            employees.first_name, 
            employees.last_name, 
            performance_evaluations.remarks
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
            $admin_id = 1; // Replace with actual admin ID
            $comments = mysqli_real_escape_string($conn, $_POST['comments']);

            // Collect criteria ratings
            $criteria = [
                "job_knowledge" => $_POST['job_knowledge'],
                "quality_of_work" => $_POST['quality_of_work'],
                "work_ethic" => $_POST['work_ethic'],
                "communication_skills" => $_POST['communication_skills'],
                "punctuality" => $_POST['punctuality'],
                "goals_achievements" => $_POST['goals_achievements']
            ];

            // Calculate overall score
            $overall_score = array_sum($criteria) / count($criteria);

            // Determine remarks based on decimal overall score
            if ($overall_score <= 1.5) {
                $remarks = "Need Guidance";
            } elseif ($overall_score > 1.5 && $overall_score <= 2.5) {
                $remarks = "Low";
            } elseif ($overall_score > 2.5 && $overall_score < 4.5) {
                $remarks = "Effective";
            } elseif ($overall_score >= 4.5) {
                $remarks = "Very Effective";
            } else {
                $remarks = "Unspecified"; // Fallback
            }    

            // Insert evaluation data into the database
            $insert_sql = "INSERT INTO performance_evaluations (employee_id, admin_id, evaluation_date, criteria, comments, overall_score, status, remarks)
                    VALUES ('$employee_id', '$admin_id', '$evaluation_date', '" . json_encode($criteria) . "', '$comments', '$overall_score', 'Completed', '$remarks')";

            if (mysqli_query($conn, $insert_sql)) {
                // Redirect to prevent form resubmission
                header("Location: performance-evaluation");
                exit;
            } else {
                echo "<p>Error: " . mysqli_error($conn) . "</p>";
            }
        }
        
        // Removed mysqli_close($conn) here, move it to the end

        ?>

        <form method="POST" class="evaluation-form">
            <div class="form-group">
                <label for="employee_id">Employee:</label>
                <select name="employee_id" required class="form-control">
                    <?php while ($row = mysqli_fetch_assoc($employees_result)) { ?>
                        <option value="<?php echo $row['employee_id']; ?>">
                            <?php echo $row['first_name'] . " " . $row['last_name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="evaluation_date">Evaluation Date:</label>
                <input type="date" name="evaluation_date" required class="form-control">
            </div>

            <h3>Performance Criteria</h3>

            <!-- Performance Criteria (looping through rows) -->
            <div class="form-group">
            <?php
    // Query to get performance criteria
    $performance_sql = "SELECT * FROM performance_criteria";
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
                        <?php echo $counter . '. ' . $row['description']; ?> (1-5):
                    </label>

                    <div class="radio-choices">
                        <div class="radio-choice">
                            <input type="radio" name="<?php echo $name_attribute; ?>" value="1">
                            <label class="radio-label">1. Need Guidance</label>
                        </div>
                        <div class="radio-choice">
                            <input type="radio" name="<?php echo $name_attribute; ?>" value="2">
                            <label class="radio-label">2. Low</label>
                        </div>
                        <div class="radio-choice">
                            <input type="radio" name="<?php echo $name_attribute; ?>" value="3">
                            <label class="radio-label">3. Satisfactory</label>
                        </div>
                        <div class="radio-choice">
                            <input type="radio" name="<?php echo $name_attribute; ?>" value="4">
                            <label class="radio-label">4. Effective</label>
                        </div>
                        <div class="radio-choice">
                            <input type="radio" name="<?php echo $name_attribute; ?>" value="5">
                            <label class="radio-label">5. Very Effective</label>
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
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>" . (isset($row['remarks']) ? $row['remarks'] : 'No Remarks') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No evaluations found.</td></tr>";
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

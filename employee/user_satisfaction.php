<?php 
include('user_header.php'); 
?>

<nav class="sidebar">
    <ul>
        <li><a href="./user_leave"><i class="fas fa-paper-plane"></i> Leave Application</a></li>
        <li><a href="./user_satisfaction"><i class="fas fa-smile"></i> Satisfaction</a></li>
        <li><a href="./user_profile"><i class="fas fa-user"></i> Manage Profile</a></li>
    </ul>
</nav>
    
<main class="main-content">
    <section id="dashboard">
        <h2>JOB SATISFACTION SURVEY</h2>

        <?php
        $conn = mysqli_connect('localhost', 'root', '', 'esetech');

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
        $current_year = date('Y');
        $employee_id = $_SESSION['employee_id'];
        echo "Session Employee ID: " . $_SESSION['employee_id'];
        $check_sql = "SELECT * FROM job_satisfaction_surveys 
                      WHERE employee_id = '$employee_id' 
                      AND YEAR(survey_date) = '$current_year'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            echo "<p>You have already submitted your satisfaction survey for this year ($current_year). Thank you!</p>";
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $questions = [
                    'clarity_of_responsibilities' => $_POST['clarity_of_responsibilities'],
                    'work_environment' => $_POST['work_environment'],
                    'work_life_balance' => $_POST['work_life_balance'],
                    'manager_support' => $_POST['manager_support'],
                    'team_collaboration' => $_POST['team_collaboration'],
                    'compensation' => $_POST['compensation'],
                    'career_growth' => $_POST['career_growth']
                ];

                $survey_date = date('Y-m-d');
                $questions_json = json_encode($questions);
                $overall_rating = array_sum($questions) / count($questions);

                $employee_check = "SELECT * FROM employees WHERE employee_id = '$employee_id'";
                $employee_result = mysqli_query($conn, $employee_check);

                if (mysqli_num_rows($employee_result) == 0) {
                    die("Error: Employee ID does not exist in the employees table.");
                }

                $sql = "INSERT INTO job_satisfaction_surveys (employee_id, survey_date, questions, overall_rating)
                        VALUES ('$employee_id', '$survey_date', '$questions_json', '$overall_rating')";

                if (mysqli_query($conn, $sql)) {
                    echo "<p style='color: green;'>Survey submitted successfully!</p>";
                } else {
                    echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
                }
            }
        ?>

        <form method="POST">
            <label for="clarity_of_responsibilities">1. Clarity of job responsibilities</label>
            <select id="clarity_of_responsibilities" name="clarity_of_responsibilities" required>
                <option value="1">Very Dissatisfied</option>
                <option value="2">Dissatisfied</option>
                <option value="3">Neutral</option>
                <option value="4">Satisfied</option>
                <option value="5">Very Satisfied</option>
            </select>
            <br><br>

            <label for="work_environment">2. Physical work environment</label>
            <select id="work_environment" name="work_environment" required>
                <option value="1">Very Dissatisfied</option>
                <option value="2">Dissatisfied</option>
                <option value="3">Neutral</option>
                <option value="4">Satisfied</option>
                <option value="5">Very Satisfied</option>
            </select>
            <br><br>

            <label for="work_life_balance">3. Work-life balance</label>
            <select id="work_life_balance" name="work_life_balance" required>
                <option value="1">Very Dissatisfied</option>
                <option value="2">Dissatisfied</option>
                <option value="3">Neutral</option>
                <option value="4">Satisfied</option>
                <option value="5">Very Satisfied</option>
            </select>
            <br><br>

            <label for="manager_support">4. Support from manager</label>
            <select id="manager_support" name="manager_support" required>
                <option value="1">Very Dissatisfied</option>
                <option value="2">Dissatisfied</option>
                <option value="3">Neutral</option>
                <option value="4">Satisfied</option>
                <option value="5">Very Satisfied</option>
            </select>
            <br><br>

            <label for="team_collaboration">5. Team collaboration</label>
            <select id="team_collaboration" name="team_collaboration" required>
                <option value="1">Very Dissatisfied</option>
                <option value="2">Dissatisfied</option>
                <option value="3">Neutral</option>
                <option value="4">Satisfied</option>
                <option value="5">Very Satisfied</option>
            </select>
            <br><br>

            <label for="compensation">6. Compensation</label>
            <select id="compensation" name="compensation" required>
                <option value="1">Very Dissatisfied</option>
                <option value="2">Dissatisfied</option>
                <option value="3">Neutral</option>
                <option value="4">Satisfied</option>
                <option value="5">Very Satisfied</option>
            </select>
            <br><br>

            <label for="career_growth">7. Career growth</label>
            <select id="career_growth" name="career_growth" required>
                <option value="1">Very Dissatisfied</option>
                <option value="2">Dissatisfied</option>
                <option value="3">Neutral</option>
                <option value="4">Satisfied</option>
                <option value="5">Very Satisfied</option>
            </select>
            <br><br>

            <button type="submit">Submit</button>
        </form>

        <?php
        }
        mysqli_close($conn);
        ?>

    </section>
</main>

<?php include('user_footer.php'); ?>

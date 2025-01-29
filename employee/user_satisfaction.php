<?php
include('user_header.php');
include('includes/sideBar.php');
?>

<main class="main-content">
    <section id="dashboard">
        <h2>JOB SATISFACTION SURVEY</h2>

        <?php
        $conn = mysqli_connect('localhost', 'root', '', 'esetech');

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Fetch form status
        $status_sql = "SELECT status FROM job_satisfaction_form_status WHERE status_id = 1";
        $status_result = mysqli_query($conn, $status_sql);
        $form_status = 'Closed'; // Default to closed if no entry is found
        if ($status_result && mysqli_num_rows($status_result) > 0) {
            $status_row = mysqli_fetch_assoc($status_result);
            $form_status = $status_row['status'];
        }

        // Check if the form is open
        if ($form_status === 'Open') {
            $current_year = date('Y');
            $employee_id = $_SESSION['employee_id'];

            // Check if the employee has already submitted the survey
            $check_sql = "SELECT * FROM job_satisfaction_surveys 
                          WHERE employee_id = '$employee_id' 
                          AND YEAR(survey_date) = '$current_year'";
            $check_result = mysqli_query($conn, $check_sql);

            if (mysqli_num_rows($check_result) > 0) {
                echo "<p>You have already submitted your satisfaction survey for this year ($current_year). Thank you!</p>";
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Handle form submission
                    $questions = [
                        'clarity_of_responsibilities' => '1. Clarity of job responsibilities',
                        'work_environment' => '2. Physical work environment',
                        'work_life_balance' => '3. Work-life balance',
                        'manager_support' => '4. Support from manager',
                        'team_collaboration' => '5. Team collaboration',
                        'compensation' => '6. Compensation',
                        'career_growth' => '7. Career Growth',

                    ];

                    $responses = [];
                    foreach ($questions as $key => $question) {
                        $responses[$key] = $_POST[$key];
                    }

                    $survey_date = date('Y-m-d');
                    $questions_json = json_encode($responses);
                    $overall_rating = array_sum($responses) / count($responses);

                    $sql = "INSERT INTO job_satisfaction_surveys (employee_id, survey_date, questions, overall_rating)
                            VALUES ('$employee_id', '$survey_date', '$questions_json', '$overall_rating')";

                    if (mysqli_query($conn, $sql)) {
                        echo "<p style='color: green;'>Survey submitted successfully!</p>";
                    } else {
                        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
                    }
                }

                // Display the form
                include('survey_form.php');
            }
        } else {
            // Show a message when the form is closed
            echo "<p style='color: red;'>The Job Satisfaction Survey is currently closed. Please check back later.</p>";
        }

        mysqli_close($conn);
        ?>
    </section>
</main>
<?php include('user_footer.php'); ?>
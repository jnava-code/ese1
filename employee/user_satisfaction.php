
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        color: #333;
    }
    .main-content {
        padding: 30px;
        max-width: 1200px;
        margin: auto;
        background-color: #fff;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        margin-top: 20px; /* Add this line to add space on top of the main content */   
    }
    table {
        border: 1px solid #ddd;
        border-collapse: collapse;
        width: 100%;
        margin: 20px 0;
    }
    th, td {
        text-align: left;
        padding: 8px;
        border: 1px solid #ddd;
    }
    th {
        background-color: #f4f4f4;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    @media (max-width: 768px) {
        .rating span {
            font-size: 24px;
        }

        h2 {
            font-size: 1rem;
        }

    }

</style>
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
                    $responses = [];
                    foreach ($_POST as $key => $value) {
                        if (strpos($key, 'question_') === 0) { // Only collect question-related inputs
                            $responses[$key] = intval($value); // Store integer responses
                        }
                    }
                
                    if (!empty($responses)) {
                        $survey_date = date('Y-m-d');
                        $questions_json = json_encode($responses);
                        $overall_rating = array_sum($responses) / count($responses);
                
                        $sql = "INSERT INTO job_satisfaction_surveys (employee_id, survey_date, questions, overall_rating)
                                VALUES ('$employee_id', '$survey_date', '$questions_json', '$overall_rating')";
                
                        if (mysqli_query($conn, $sql)) {
                            header("Location: user_satisfaction");
                            exit;
                        } else {
                            echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
                        }
                    } else {
                        echo "<p style='color: red;'>Please answer all questions before submitting.</p>";
                    }
                }
                
                ?>

                <!-- Survey Form -->
                <form method="POST" class="survey-form" id="surveyForm">
                    <?php 
                        $sql = "SELECT * FROM job_satisfaction_criteria";
                        $result = mysqli_query($conn, $sql);

                        if ($result) {
                            $count = 0;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $count++;
                                $question_name = "question_" . $row['title']; // Unique name for each question
                    ?>
                                <label for="<?= $question_name; ?>"><?= $count . '. ' . $row['title']; ?></label>
                                <p><?= $row['description']; ?></p>
                                <div class="rating" data-question="<?= $question_name; ?>">
                                    <span title="Very Satisfied" class="emoji" onclick="selectEmoji(event, '<?= $question_name; ?>', 5)">😃</span>
                                    <span title="Satisfied" class="emoji" onclick="selectEmoji(event, '<?= $question_name; ?>', 4)">🙂</span>
                                    <span title="Neutral" class="emoji" onclick="selectEmoji(event, '<?= $question_name; ?>', 3)">😐</span>
                                    <span title="Dissatisfied" class="emoji" onclick="selectEmoji(event, '<?= $question_name; ?>', 2)">🙁</span>
                                    <span title="Very Dissatisfied" class="emoji" onclick="selectEmoji(event, '<?= $question_name; ?>', 1)">😞</span>
                                </div>
                                <input type="hidden" id="<?= $question_name; ?>" name="<?= $question_name; ?>">
                                <br><br>
                    <?php 
                            }
                        }
                    ?>
                    <input type="submit" class="btn" value="Submit Survey" onclick="handleSubmit(event)">
                </form>

                <script>
                function selectEmoji(event, question, rating) {
                    event.preventDefault(); // Prevent the default action

                    // Get all emoji elements for the given question
                    const emojis = document.querySelectorAll(`span[title][onclick*="${question}"]`);

                    // Remove the 'selected' class from all emojis
                    emojis.forEach(emoji => {
                        emoji.classList.remove('selected');
                    });

                    // Add 'selected' class to the clicked emoji
                    const selectedEmoji = document.querySelector(`span[onclick*="${question}"][onclick*="${rating}"]`);
                    selectedEmoji.classList.add('selected');

                    // Set the corresponding rating value for the question
                    document.getElementById(question).value = rating; // This will update the hidden input value
                }

                function handleSubmit(event) {
                    event.preventDefault(); // Prevent the default form submission

                    // Perform any additional validation or processing here

                    // Submit the form programmatically
                    document.getElementById('surveyForm').submit();
                }
                </script>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f9;
                        margin: 0;
                        padding: 0;
                    }

                    .survey-form {
                        max-width: 800px;
                        margin: 15px auto;
                        padding: 20px;
                        background-color: #fff;
                        border-radius: 10px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }

                    .survey-form label {
                        font-size: 1.5rem;
                        color: #333;
                    }

                    .survey-form p {
                        font-size: 1rem;
                        color: #666;
                    }

                    .rating {
                        display: flex;
                        justify-content: space-around;
                        align-items: center;
                        padding: 20px;
                        font-size: 2rem;
                    }

                    .emoji {
                        font-size: 3rem;
                        cursor: pointer;
                        transition: transform 0.2s;
                    }

                    .emoji:hover {
                        transform: scale(1.2);
                    }

                    .emoji.selected {
                        border: 2px solid #4CAF50; /* Green border */
                        border-radius: 50%;      /* Round border */
                        padding: 5px;            /* Add padding for visual appeal */
                        background-color: #E8F5E9; /* Light green background */
                    }

                    .btn {
                        display: block;
                        width: 100%;
                        padding: 15px;
                        font-size: 1.2rem;
                        color: #fff;
                        background-color: #4CAF50;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        transition: background-color 0.3s;
                    }

                    .btn:hover {
                        background-color: #45a049;
                    }
                </style>

                <?php
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
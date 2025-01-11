        <?php include('header.php'); ?>

        <!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
        <?php include('includes/sideBar.php'); ?>


        <!-- Main Content Area -->
        <main class="main-content">
            <section id="dashboard">
                <h2>PERFORMANCE EVALUATION</h2>
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

                mysqli_close($conn); // Close connection
                ?>

                <?php if (!empty($success_message)) : ?>
                    <div id="success-notification" class="notification">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

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

                    <div class="form-group">
                        <label for="job_knowledge">1. Job Knowledge (1-5):</label>
                        <!-- <input type="number" name="job_knowledge" min="1" max="5" required class="form-control"> -->
                        <div class="radio-choices">
                            <div class="radio-choice">
                                <input type="radio" name="job_knowledge" value="1">
                                <label class="radio-label">1. Need Guidance</label>
                            </div>
                            
                            <div class="radio-choice">
                                <input type="radio" name="job_knowledge" value="2">
                                <label class="radio-label">2. Low</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="job_knowledge" value="3">
                                <label class="radio-label">3. Satisfactory</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="job_knowledge" value="4">
                                <label class="radio-label">4. Effective</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="job_knowledge" value="5">
                                <label class="radio-label">5. Very Effective</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="quality_of_work">2. Quality of Work (1-5):</label>
                        <!-- <input type="number" name="quality_of_work" min="1" max="5" required class="form-control"> -->
                        <div class="radio-choices">
                            <div class="radio-choice">
                                <input type="radio" name="quality_of_work" value="1">
                                <label class="radio-label">1. Need Guidance</label>
                            </div>
                            
                            <div class="radio-choice">
                                <input type="radio" name="quality_of_work" value="2">
                                <label class="radio-label">2. Low</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="quality_of_work" value="3">
                                <label class="radio-label">3. Satisfactory</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="quality_of_work" value="4">
                                <label class="radio-label">4. Effective</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="quality_of_work" value="5">
                                <label class="radio-label">5. Very Effective</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="work_ethic">3. Work Ethic (1-5):</label>
                        <!-- <input type="number" name="work_ethic" min="1" max="5" required class="form-control"> -->
                        <div class="radio-choices">
                            <div class="radio-choice">
                                <input type="radio" name="work_ethic" value="1">
                                <label class="radio-label">1. Need Guidance</label>
                            </div>
                            
                            <div class="radio-choice">
                                <input type="radio" name="work_ethic" value="2">
                                <label class="radio-label">2. Low</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="work_ethic" value="3">
                                <label class="radio-label">3. Satisfactory</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="work_ethic" value="4">
                                <label class="radio-label">4. Effective</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="work_ethic" value="5">
                                <label class="radio-label">5. Very Effective</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="communication_skills">4. Communication Skills (1-5):</label>
                        <!-- <input type="number" name="communication_skills" min="1" max="5" required class="form-control"> -->
                        <div class="radio-choices">
                            <div class="radio-choice">
                                <input type="radio" name="communication_skills" value="1">
                                <label class="radio-label">1. Need Guidance</label>
                            </div>
                            
                            <div class="radio-choice">
                                <input type="radio" name="communication_skills" value="2">
                                <label class="radio-label">2. Low</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="communication_skills" value="3">
                                <label class="radio-label">3. Satisfactory</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="communication_skills" value="4">
                                <label class="radio-label">4. Effective</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="communication_skills" value="5">
                                <label class="radio-label">5. Very Effective</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="punctuality">5. Punctuality (1-5):</label>
                        <!-- <input type="number" name="punctuality" min="1" max="5" required class="form-control"> -->
                        <div class="radio-choices">
                            <div class="radio-choice">
                                <input type="radio" name="punctuality" value="1">
                                <label class="radio-label">1. Need Guidance</label>
                            </div>
                            
                            <div class="radio-choice">
                                <input type="radio" name="punctuality" value="2">
                                <label class="radio-label">2. Low</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="punctuality" value="3">
                                <label class="radio-label">3. Satisfactory</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="punctuality" value="4">
                                <label class="radio-label">4. Effective</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="punctuality" value="5">
                                <label class="radio-label">5. Very Effective</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="goals_achievements">6. Goals and Achievements (1-5):</label>
                        <!-- <input type="number" name="goals_achievements" min="1" max="5" required class="form-control"> -->
                        <div class="radio-choices">
                            <div class="radio-choice">
                                <input type="radio" name="goals_achievements" value="1">
                                <label class="radio-label">1. Need Guidance</label>
                            </div>
                            
                            <div class="radio-choice">
                                <input type="radio" name="goals_achievements" value="2">
                                <label class="radio-label">2. Low</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="goals_achievements" value="3">
                                <label class="radio-label">3. Satisfactory</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="goals_achievements" value="4">
                                <label class="radio-label">4. Effective</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="goals_achievements" value="5">
                                <label class="radio-label">5. Very Effective</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="attendance">7. Attendance (1-5):</label>
                        <!-- <input type="number" name="attendance" min="1" max="5" required class="form-control"> -->
                        <div class="radio-choices">
                            <div class="radio-choice">
                                <input type="radio" name="attendance" value="1">
                                <label class="radio-label">1. Need Guidance</label>
                            </div>
                            
                            <div class="radio-choice">
                                <input type="radio" name="attendance" value="2">
                                <label class="radio-label">2. Low</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="attendance" value="3">
                                <label class="radio-label">3. Satisfactory</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="attendance" value="4">
                                <label class="radio-label">4. Effective</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="attendance" value="5">
                                <label class="radio-label">5. Very Effective</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="housekeeping">8. Housekeeping (1-5):</label>
                        <!-- <input type="number" name="housekeeping" min="1" max="5" required class="form-control"> -->
                        <div class="radio-choices">
                            <div class="radio-choice">
                                <input type="radio" name="housekeeping" value="1">
                                <label class="radio-label">1. Need Guidance</label>
                            </div>
                            
                            <div class="radio-choice">
                                <input type="radio" name="housekeeping" value="2">
                                <label class="radio-label">2. Low</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="housekeeping" value="3">
                                <label class="radio-label">3. Satisfactory</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="housekeeping" value="4">
                                <label class="radio-label">4. Effective</label>
                            </div>

                            <div class="radio-choice">
                                <input type="radio" name="housekeeping" value="5">
                                <label class="radio-label">5. Very Effective</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="comments">Comments:</label>
                        <textarea name="comments" rows="4" placeholder="Enter any additional comments" class="form-control"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Evaluation</button>
                </form>
                <br>
                <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
            
                <form method="POST" class="evaluation-form">
                    <div class="form-group">
                <!-- Evaluation Table -->
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
            </div>
            </form>

            </section>
        </main>

        <?php include('footer.php'); ?>

        <style>
            .notification {
                padding: 15px;
                background-color: #4CAF50; /* Green */
                color: white;
                text-align: center;
                border-radius: 5px;
                margin: 10px 0;
                font-size: 16px;
            }

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
            .evaluation-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            .evaluation-table th,
            .evaluation-table td {
                padding: 10px;
                border: 1px solid #ddd;
                text-align: left;
            }

            .evaluation-table th {
                background-color: #f4f4f4;
                font-weight: bold;
            }

            .evaluation-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .evaluation-table tr:hover {
                background-color: #f1f1f1;
            }
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
            // Automatically hide the success notification after 3 seconds
            setTimeout(() => {
                const notification = document.getElementById('success-notification');
                if (notification) {
                    notification.style.display = 'none';
                }
            }, 3000); // 3 seconds
        </script>
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
        <script>
        $(document).ready( function () {
            $('#myTable').DataTable();
        });
        
        </script>
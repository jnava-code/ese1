<?php include('header.php'); ?>
<?php include('includes/sideBar.php'); ?>

<?php
    // Database connection
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
            
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    require '../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../vendor/PHPMailer-6.9.3/PHPMailer-6.9.3/src/Exception.php';
    require '../vendor/PHPMailer-6.9.3/PHPMailer-6.9.3/src/PHPMailer.php';
    require '../vendor/PHPMailer-6.9.3/PHPMailer-6.9.3/src/SMTP.php';

    $succMsg = '';
    $errMsg = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['approve']) || isset($_POST['reject'])) {
            $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
            $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
            $recommendation = mysqli_real_escape_string($conn, $_POST['recommendation']);
            $gender = mysqli_real_escape_string($conn, $_POST['gender']);
            $maritalStatus = mysqli_real_escape_string($conn, $_POST['civil_status']);
            $reason = isset($_POST['approve']) ? 'Approved' : 'Rejected';

            $sql = "INSERT INTO `e_recommendations` (`employee_id`, `recommendation_type`, `reason`, `effective_date`) 
                    VALUES ('$employee_id', '$recommendation', '$reason', NOW())";

            $result = mysqli_query($conn, $sql);

            if ($result) {
                $mail = new PHPMailer(true);

                try {
                    // SMTP Settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'rroquero26@gmail.com';
                    $mail->Password = 'plxj aziw yqbo wkbs';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                                
                    // Email Headers
                    $mail->setFrom('no-reply@yourwebsite.com', 'ESE-Tech Industrial Solutions Corporation System');
                    $mail->addAddress($email);
                    $mail->Subject = 'ESE-Tech Industrial Solutions Corporation System';

                    // Prepare the email message
                    $situation = $reason == 'Approved' ? 'You will remain in your current position.' : '';
                    $honorifics = ($gender == 'Male') ? 'Mr.' : ($maritalStatus == 'Married' ? 'Mrs.' : 'Ms.');
                    $messageContent = '';

                    if($recommendation == 'Promotion') {
                        if($reason == 'Approved') {
                            $messageContent = 'We are pleased to inform you that your recommendation for promotion has been reviewed and approved by the management.
                            <br><br>We commend your performance and dedication, and we look forward to your continued contributions in your new role. Further details regarding the transition and next steps will be communicated to you shortly.
                            <br><br>Congratulations on this well-deserved recognition.';
                        } else {
                            $messageContent = 'We would like to inform you that your recommendation for promotion has been reviewed by the management and, after careful consideration, has not been approved at this time.
                            <br><br>We encourage you to continue demonstrating your skills and commitment, and we remain confident in your potential for future growth within the company.
                            <br><br>Thank you for your continued dedication.';
                        }
                    } elseif ($recommendation == 'Demotion') {
                        if($reason == 'Approved') {
                            $messageContent = 'We would like to inform you that your recommendation for demotion has been reviewed and approved by the management.
                            <br><br>Please be assured that this decision was made after thorough evaluation and consideration. Further details regarding the transition to your new role, including responsibilities and effective date, will be communicated to you shortly.
                            <br><br>Should you have any concerns or require further clarification, you are welcome to reach out.';
                        } else {
                            $messageContent = 'We would like to inform you that your recommendation for demotion has been reviewed and was not approved by the management.
                            <br><br>As such, you will remain in your current position. We appreciate your continued commitment and encourage you to maintain a high standard of performance.
                            <br><br>Should you have any questions or need further clarification, please feel free to contact us.';
                        }
                    } elseif ($recommendation == 'Retrenchment') {
                        if($reason == 'Approved') {
                            $messageContent = 'We would like to inform you that your recommendation for retrenchment has been reviewed and approved by the management.
                            <br><br>Please be assured that all necessary procedures will be handled with due diligence and professionalism. Should you have any questions or require further clarification, feel free to reach out.
                            <br><br>Thank you for your understanding and continued support.';
                        } else {
                            $messageContent = 'We would like to inform you that your recommendation for retrenchment has been carefully reviewed and has not been approved.
                            <br><br>As a result, you will continue to serve in your current position.
                            <br><br>Thank you for your continued dedication and service to the company.';
                        }
                    } else {
                        if($reason == 'Approved') {
                            $messageContent = "We would like to inform you that your recommendation to remain in your current position has been reviewed and approved by the management.
                            <br><br>This decision reflects the company's confidence in your performance and the value you continue to bring to your role. We look forward to your continued contributions and dedication.
                            <br><br>Should you have any questions or need further information, please feel free to reach out.";
                        } else {
                            $messageContent = 'We would like to inform you that your recommendation to remain in your current position has been reviewed by the management but was not approved.
                            <br><br>A change in your current employment status has been decided upon. Further details regarding this change and the next steps will be communicated to you directly.
                            <br><br>Should you have any concerns or require clarification, please don’t hesitate to contact us.';
                        }
                    }

                    $message = "Dear $honorifics $firstname $lastname,
                        <br><br>$messageContent
                        <br><br>Best regards,
                        <br>ESE-Tech HR Team";

                    // Set email format to plain text
                    $mail->isHTML(true);
                    $mail->Body = $message;

                    // Send the email
                    if ($mail->send()) {               
                        $succMsg = $firstname . ' ' . $lastname . ':' . ' ' . $recommendation . ' has been ' . $reason;
                    } else {
                        $errmsg = "An error occurred: " . $stmt->error;
                    }
                } catch (Exception $e) {
                    $errmsg = "Mailer Error: " . $mail->ErrorInfo;
                }
            } else {
                $errMsg = 'Error ' . ($reason == 'Approved' ? 'approving' : 'rejecting') . ' ' . $recommendation;
            }
        }
    }
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />

<main class="main-content">
    <section id="dashboard">
        <h2>ATTRITION PREDICTION</h2>
        <?php 
            $color = (isset($reason) && $reason == 'Approved') ? '#51cf66' : '#ff6b6b'; 
            $backgroundColor = (isset($reason) && $reason == 'Approved') ? '#ebfbee' : '#fff5f5';
        ?>

            <?php if (!empty($succMsg)): ?>
                <div class="bg-green-500 text-white p-3 rounded mb-4">
                    <!-- Corrected inline style for dynamic color -->
                    <span style="color: <?php echo $color; ?>; background-color: <?php echo $backgroundColor; ?>; padding: 12px 24px; border: 1px solid <?php echo $color; ?>"><?php echo $succMsg; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errMsg): ?>
                <div class="bg-red-500 text-white p-3 rounded mb-4">
                    <?php echo $errMsg; ?>
                </div>
            <?php endif; ?>

        <?php
        // Function to calculate attrition risk score using linear regression
        function calculateAttritionRisk($attendance_score, $satisfaction_score, $performance_score, $years_of_service) {
            // Weights for each factor (total should be 1)
            $attendance_weight = 0.25;    // 25% weight
            $satisfaction_weight = 0.25;  // 25% weight
            $performance_weight = 0.25;   // 25% weight
            $years_weight = 0.25;        // 25% weight

            // Normalize years of service (assuming max 30 years)
            $normalized_years = min($years_of_service / 30, 1);
            
            // Calculate risk score (inverse relationship - higher scores mean lower risk)
            $risk_score = 1 - (
                ($attendance_score * $attendance_weight) +
                ($satisfaction_score * $satisfaction_weight) +
                ($performance_score * $performance_weight) +
                ($normalized_years * $years_weight)
            );

            return max(0, min(1, $risk_score)); // Ensure score is between 0 and 1
        }

        // Fetch employee data and calculate attrition risk
        // $query = "SELECT 
        //     e.employee_id,
        //     e.first_name,
        //     e.last_name,
        //     e.hire_date,
        //     er.recommendation_id,
        //     er.recommendation_type,
        //     er.reason,
        //     COALESCE(AVG(CASE 
        //         WHEN a.status = 'On Time' OR a.status = 'Present' OR a.status = 'Over Time' THEN 1
        //         WHEN a.status = 'Late' OR a.status = 'Under Time' THEN 0.5
        //         ELSE 0 
        //     END), 0) as attendance_score,
        //     COALESCE(AVG(js.overall_rating) / 5, 0) as satisfaction_score,
        //     COALESCE(AVG(pe.overall_score) / 5, 0) as performance_score,
        //     DATEDIFF(CURRENT_DATE, e.hire_date) / 365 as years_of_service
        //     FROM employees e
        //     LEFT JOIN attendance a ON e.employee_id = a.employee_id
        //     LEFT JOIN job_satisfaction_surveys js ON e.employee_id = js.employee_id
        //     LEFT JOIN performance_evaluations pe ON e.employee_id = pe.employee_id
        //     LEFT JOIN e_recommendations er ON e.employee_id = er.employee_id
        //     WHERE e.is_archived = 0
        //     GROUP BY e.employee_id, e.first_name, e.last_name, e.hire_date";

        $query = "WITH working_days AS (
                    SELECT 
                        e.employee_id,
                        COUNT(*) AS total_working_days
                    FROM employees e
                    JOIN (
                        SELECT 
                            d.date, e.employee_id
                        FROM (
                            SELECT CURDATE() - INTERVAL n DAY AS date
                            FROM (
                                SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL 
                                SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL 
                                SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL 
                                SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL 
                                SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL 
                                SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL 
                                SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL 
                                SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL 
                                SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL 
                                SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL 
                                SELECT 30
                            ) AS numbers
                        ) AS d
                        JOIN employees e ON d.date >= e.hire_date AND d.date <= CURDATE()
                        WHERE WEEKDAY(d.date) < 5  -- Exclude Saturdays (5) and Sundays (6)
                    ) wd ON wd.employee_id = e.employee_id
                    GROUP BY e.employee_id
                ), 

                absences AS (
                    SELECT 
                        e.employee_id,
                        COUNT(*) AS total_absences
                    FROM employees e
                    JOIN (
                        SELECT 
                            d.date, e.employee_id
                        FROM (
                            SELECT CURDATE() - INTERVAL n DAY AS date
                            FROM (
                                SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL 
                                SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL 
                                SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL 
                                SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL 
                                SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL 
                                SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL 
                                SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL 
                                SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL 
                                SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL 
                                SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL 
                                SELECT 30
                            ) AS numbers
                        ) AS d
                        JOIN employees e ON d.date >= e.hire_date AND d.date <= CURDATE()
                        LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.date = d.date
                        WHERE WEEKDAY(d.date) < 5  -- Exclude weekends
                        AND a.employee_id IS NULL  -- Employee has no attendance record
                    ) ab ON ab.employee_id = e.employee_id
                    GROUP BY e.employee_id
                )

                SELECT 
                    e.employee_id,
                    e.first_name,
                    e.last_name,
                    e.hire_date,
                    e.gender,
                    e.civil_status,
                    e.email,
                    er.recommendation_id,
                    er.recommendation_type,
                    er.reason,
                    COALESCE(1 - (a.total_absences / wd.total_working_days), 1) AS attendance_score,  
                    COALESCE(AVG(js.overall_rating) / 5, 0) AS satisfaction_score,
                    COALESCE(AVG(pe.overall_score) / 5, 0) AS performance_score,
                    DATEDIFF(CURRENT_DATE, e.hire_date) / 365 AS years_of_service
                FROM employees e
                LEFT JOIN working_days wd ON e.employee_id = wd.employee_id
                LEFT JOIN absences a ON e.employee_id = a.employee_id
                LEFT JOIN job_satisfaction_surveys js ON e.employee_id = js.employee_id
                LEFT JOIN performance_evaluations pe ON e.employee_id = pe.employee_id
                LEFT JOIN e_recommendations er ON e.employee_id = er.employee_id
                WHERE e.is_archived = 0
                GROUP BY e.employee_id, e.first_name, e.last_name, e.hire_date";
        $result = mysqli_query($conn, $query);

        if ($result) {
            // Prepare arrays for chart data
            $labels = [];
            $riskData = [];
            $attendanceData = [];
            $satisfactionData = [];
            $backgroundColor = [];

            // First pass to collect data for the chart
            $allRows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $allRows[] = $row;
                
                $attendance_score = floatval($row['attendance_score']);
                $satisfaction_score = floatval($row['satisfaction_score']);
                $performance_score = floatval($row['performance_score']);
                $years_of_service = floatval($row['years_of_service']);

                $risk_score = calculateAttritionRisk(
                    $attendance_score,
                    $satisfaction_score,
                    $performance_score,
                    $years_of_service
                );

                // Collect data for charts
                $labels[] = $row['first_name'] . ' ' . $row['last_name'];
                $riskData[] = round($risk_score * 100, 1);
                $attendanceData[] = round($attendance_score * 100, 1);
                $satisfactionData[] = round($satisfaction_score * 100, 1);
                
                
                // Set color based on risk level
                if ($risk_score <= 0.3) {
                    $backgroundColor[] = 'rgba(40, 167, 69, 0.5)'; // green
                } elseif ($risk_score <= 0.6) {
                    $backgroundColor[] = 'rgba(255, 193, 7, 0.5)'; // yellow
                } else {
                    $backgroundColor[] = 'rgba(220, 53, 69, 0.5)'; // red
                }
            }

            // Display only the regression chart
            echo '<div class="charts-container" style="display: flex; justify-content: center; align-items: center; height: 500px;">
            <div class="chart-wrapper" style="width: 90%; height: 90%; display: flex; justify-content: center; align-items: center;">
                <canvas id="regressionChart"></canvas>
            </div>
            </div>';

            // Display the table
            echo '<div class="attrition-table-container">';
            echo '<table id="attritionTable" class="display">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Years of Service</th>
                            <th>Attendance Score</th>
                            <th>Satisfaction Score</th>
                            <th>Performance Score</th>
                            <th>Attrition Risk</th>
                            <th>Risk Level</th>                         
                            <th>Recommendations</th>
                            <th>Justification</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

            // Second pass to display table data
            foreach ($allRows as $row) {
                $attendance_score = floatval($row['attendance_score']);
                $satisfaction_score = floatval($row['satisfaction_score']) * 100; // Convert to percentage
                $performance_score = floatval($row['performance_score']);
                $years_of_service = floatval($row['years_of_service']);

                $risk_score = calculateAttritionRisk(
                    $attendance_score,
                    $satisfaction_score, // Convert back to 0-1 scale for risk calculation
                    $performance_score,
                    $years_of_service
                );

                // Determine risk level and generate justification based on real-time data
                $justifications = [];
                if ($risk_score <= 0.3) {
                    $risk_level = '<span class="low-risk">Low Risk</span>';
                    $recommendation = 'Promotion';
                } elseif ($risk_score <= 0.6) {
                    $risk_level = '<span class="medium-risk">Medium Risk</span>';
                    $recommendation = 'Demotion';
                } else {
                    $risk_level = '<span class="high-risk">High Risk</span>';
                    $recommendation = 'Retrenchment';
                }

                // Generate justification based on real-time data
                if ($performance_score < 0.60) {
                    $justifications[] = 'Consistently Poor Performance - Below 60% in multiple evaluation periods.';
                } elseif ($performance_score >= 0.60 && $performance_score < 0.80) {
                    $justifications[] = 'Performance between 60% and 80% - Needs improvement.';
                } elseif ($performance_score >= 0.90) {
                    $justifications[] = 'High Performance - Exceptional results exceeding expectations.';
                }

                if ($attendance_score < 0.50) {
                    $justifications[] = 'High Absenteeism - Below 50% attendance rate significantly affecting productivity.';
                } elseif ($attendance_score >= 0.50 && $attendance_score < 0.70) {
                    $justifications[] = 'Attendance between 50% and 70% - Needs improvement.';
                } elseif ($attendance_score >= 0.90) {
                    $justifications[] = 'Excellent Attendance - Above 90%, showing commitment and reliability.';
                }

                if ($satisfaction_score < 0.40) {
                    $justifications[] = 'Job Dissatisfaction - Reports strong discontent with workload and environment.';
                } elseif ($satisfaction_score >= 0.40 && $satisfaction_score < 0.60) {
                    $justifications[] = 'Job Satisfaction between 40% and 60% - Moderate contentment but needs monitoring.';
                } elseif ($satisfaction_score >= 0.80) {
                    $justifications[] = 'High Job Satisfaction - Demonstrates strong engagement and positivity.';
                }

                if ($risk_score > 0.60) {
                    $justifications[] = 'High Attrition Risk - Likely to leave the company.';
                } elseif ($risk_score > 0.30 && $risk_score <= 0.60) {
                    $justifications[] = 'Medium Attrition Risk - Potential risk of leaving the company.';
                } elseif ($risk_score <= 0.30) {
                    $justifications[] = 'Low Attrition Risk - Likely to stay with the company.';
                }

                // Format justifications as a list
                $justification_list = '<ul>';
                foreach ($justifications as $jus) {
                    $justification_list .= "<li>$jus</li>";
                }
                $justification_list .= '</ul>';

                // Store prediction in database
                $store_prediction = "INSERT INTO attrition_forecasting (
                    employee_id,
                    prediction_date,
                    attrition_probability,
                    factors
                ) VALUES (
                    '{$row['employee_id']}',
                    CURRENT_DATE,
                    $risk_score,
                    '" . json_encode([
                        'attendance_score' => $attendance_score,
                        'satisfaction_score' => $satisfaction_score,
                        'performance_score' => $performance_score,
                        'years_of_service' => $years_of_service
                    ]) . "'
                ) ON DUPLICATE KEY UPDATE 
                    attrition_probability = $risk_score,
                    factors = '" . json_encode([
                        'attendance_score' => $attendance_score,
                        'satisfaction_score' => $satisfaction_score,
                        'performance_score' => $performance_score,
                        'years_of_service' => $years_of_service
                    ]) . "'";

                mysqli_query($conn, $store_prediction);

                if ($row['recommendation_id']) {
                    $action = $row['reason'];
                
                    if ($action == 'Approved' && $row['recommendation_type'] == 'Promotion') {
                        $action = 'PROMOTE';
                    } elseif ($action == 'Approved' && $row['recommendation_type'] == 'Demotion') {
                        $action = 'DEMOTE';
                    } elseif ($action == 'Approved' && $row['recommendation_type'] == 'Retrenchment') {
                        $action = 'RETRENCH';
                    } elseif ($action == 'Rejected') {
                        $action = 'REMAIN';
                    }
                } else {
                    $action = "<form class='action-buttons' method='POST'>
                                <input type='hidden' name='employee_id' value='{$row['employee_id']}'/>
                                <input type='hidden' name='recommendation' value='{$recommendation}'/>
                                <input id='approved' data-email='{$row['email']}' data-civil_status='{$row['civil_status']}'data-gender='{$row['gender']}' data-firstname='{$row['first_name']}' data-lastname='{$row['last_name']}' data-employee_id='{$row['employee_id']}' data-recommendation='{$recommendation}' style='background-color: green !important' class='btn btn-warning' type='button' value='Approve'/>
                                <input id='rejected' data-email='{$row['email']}' data-civil_status='{$row['civil_status']}'data-gender='{$row['gender']}' data-firstname='{$row['first_name']}' data-lastname='{$row['last_name']}' data-employee_id='{$row['employee_id']}' data-recommendation='{$recommendation}' class='btn btn-danger' type='button' value='Reject'/>
                            </form>";
                }
                echo "<tr>
                        <td>{$row['employee_id']}</td>
                        <td>{$row['first_name']} {$row['last_name']}</td>
                        <td>" . number_format($years_of_service, 1) . "</td>
                        <td>" . number_format($attendance_score * 100, 1) . "%</td>
                        <td>" . number_format($satisfaction_score, 1) . "%</td>
                        <td>" . number_format($performance_score * 100, 1) . "%</td>
                        <td>" . number_format($risk_score * 100, 1) . "%</td>
                        <td>$risk_level</td>
                        <td>$recommendation</td>
                        <td>$justification_list</td>
                        <td>$action</td>
                    </tr>";

            }

            echo '</tbody></table></div>';
        }
        mysqli_close($conn);
        ?>
    </section>
</main>

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const approved = document.querySelectorAll('#approved');
    const rejected = document.querySelectorAll('#rejected');
    
    [...approved, ...rejected].forEach((button) => {
        button.addEventListener('click', function(e) {
            e.preventDefault();            
            const firstname = this.getAttribute('data-firstname');
            const lastname = this.getAttribute('data-lastname'); 
            const employeeId = this.getAttribute('data-employee_id');
            const recommendation = this.getAttribute('data-recommendation');
            const gender = this.getAttribute('data-gender');
            const civil_status = this.getAttribute('data-civil_status');
            const email = this.getAttribute('data-email');
            approvedOrRejectedModal(firstname, lastname, gender, civil_status, email, button.value, employeeId, recommendation);        
        });
    });

    function approvedOrRejectedModal(firstname, lastname, gender, civil_status, email, buttonValue, employeeId, recommendation) {
        const modal = `<div class="approved-or-rejected-modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Name: ${firstname} ${lastname}</h3>
                <p>Recommendation: ${recommendation}</p>
                <p>Are you sure you want to ${buttonValue == 'Approve' ? 'APPROVE' : 'REJECT'} this recommendation?</p>
                <form method="POST">
                    <input type="hidden" name="firstname" value="${firstname}"/>
                    <input type="hidden" name="lastname" value="${lastname}"/>
                    <input type="hidden" name="employee_id" value="${employeeId}"/>
                    <input type="hidden" name="recommendation" value="${recommendation}"/>
                    <input type="hidden" name="gender" value="${gender}" />
                    <input type="hidden" name="civil_status" value="${civil_status}" />
                    <input type="hidden" name="email" value="${email}" />
                    <input type="submit" style="background-color: green" class="btn btn-success" name="${buttonValue == 'Approve' ? 'approve' : 'reject'}" value="${buttonValue}" />
                    <input type="button" id="cancel-btn" class="btn btn-danger close-modal" value="Cancel" />
                </form>
            </div>     
        </div>`;

        document.body.insertAdjacentHTML('beforeend', modal);

        const closeModal = document.querySelector('.close');
        const cancelBtn = document.querySelector('#cancel-btn');

        [closeModal, cancelBtn].forEach((btn) => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('.approved-or-rejected-modal').remove();
            });
        });
    }

function linearRegression(x, y) {
    const n = x.length;
    let sum_x = 0;
    let sum_y = 0;
    let sum_xy = 0;
    let sum_xx = 0;
    
    for (let i = 0; i < n; i++) {
        sum_x += x[i];
        sum_y += y[i];
        sum_xy += x[i] * y[i];
        sum_xx += x[i] * x[i];
    }
    
    const slope = (n * sum_xy - sum_x * sum_y) / (n * sum_xx - sum_x * sum_x);
    const intercept = (sum_y - slope * sum_x) / n;
    
    return {slope, intercept};
}

function generateRegressionLine(x, slope, intercept) {
    return x.map(val => slope * val + intercept);
}

// Chart initialization
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for regression
    const yearsData = <?php echo json_encode(array_map('floatval', array_column($allRows, 'years_of_service'))); ?>;
    const riskData = <?php echo json_encode($riskData); ?>.map(val => val / 100); // Convert to 0-1 scale
    
    // Create scatter plot data
    const scatterData = yearsData.map((year, index) => ({
        x: year,
        y: riskData[index]
    }));
    
    // Calculate regression line
    const regression = linearRegression(yearsData, riskData);
    const minYear = Math.min(...yearsData);
    const maxYear = Math.max(...yearsData);
    const regressionX = [minYear, maxYear];
    const regressionY = regressionX.map(x => regression.slope * x + regression.intercept);

    // Regression Chart
    new Chart(document.getElementById('regressionChart'), {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Employee Data',
                data: scatterData,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                pointRadius: 8,
                pointHoverRadius: 10
            }, {
                label: 'Regression Line',
                data: regressionX.map((x, i) => ({x: x, y: regressionY[i]})),
                type: 'line',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: false,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Attrition Risk vs Years of Service (Linear Regression)',
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const point = context.raw;
                            return `Years: ${point.x.toFixed(1)}, Risk: ${(point.y * 100).toFixed(1)}%`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Years of Service'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Attrition Risk'
                    },
                    min: 0,
                    max: 1,
                    ticks: {
                        callback: function(value) {
                            return (value * 100) + '%';
                        }
                    }
                }
            }
        }
    });
});
</script>

<style>
/* Style for Table */
#attritionTable {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

#attritionTable th,
#attritionTable td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

#attritionTable th {
    background-color:rgb(148, 38, 38);
    color: white;
    font-weight: bold;
}

#attritionTable tr:nth-child(even) {
    background-color: #f2f2f2;
}

#attritionTable tr:hover {
    background-color: #ddd;
}

.low-risk {
    color: #28a745;
    font-weight: bold;
}

.medium-risk {
    color: #ffc107;
    font-weight: bold;
}

.high-risk {
    color: #dc3545;
    font-weight: bold;
}

.charts-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.chart-wrapper {
    flex: 1;
    min-width: 300px;
    height: 400px;
    padding: 15px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin: 10px;
}

.full-width {
    flex: 0 0 100% !important;
    height: 500px !important;
}

.attrition-table-container {
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 20px;
}

.dataTables_wrapper .dataTables_length {
    float: left;
}

.dataTables_wrapper .dataTables_filter {
    float: right;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-direction: column;
}

.approved-or-rejected-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

.approved-or-rejected-modal span {
    display: flex;
    justify-content: flex-end;

    font-size: 32px;
    cursor: pointer;
}

.approved-or-rejected-modal form {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
</style>


<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/docx/7.1.0/docx.min.js"></script>
<script>
  $(document).ready( function () {
    $('#attritionTable').DataTable();
  });

</script>
<?php include('footer.php'); ?>
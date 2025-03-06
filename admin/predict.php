<?php include('header.php'); ?>
<?php include('includes/sideBar.php'); ?>

<?php
    // Database connection
    $conn = mysqli_connect('localhost', 'root', '', 'esetech');
            
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $succMsg = '';
    $errMsg = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['approve']) || isset($_POST['reject'])) {
            $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
            $recommendation = mysqli_real_escape_string($conn, $_POST['recommendation']);
            $reason = isset($_POST['approve']) ? 'Approved' : 'Rejected';

            $sql = "INSERT INTO `e_recommendations` (`employee_id`, `recommendation_type`, `reason`, `effective_date`) 
                    VALUES ('$employee_id', '$recommendation', '$reason', NOW())";

            $result = mysqli_query($conn, $sql);

            if ($result) {
                $succMsg = $recommendation . ' has been ' . $reason;
            } else {
                $errMsg = 'Error ' . ($reason == 'Approved' ? 'approving' : 'rejecting') . ' ' . $recommendation;
            }
        }
    }
?>
<main class="main-content">
    <section id="dashboard">
        <h2>ATTRITION PREDICTION</h2>
            <?php if ($succMsg): ?>
                <div class="bg-green-500 text-white p-3 rounded mb-4"> <?php echo $succMsg; ?> </div>
            <?php endif; ?>
            
            <?php if ($errMsg): ?>
                <div class="bg-red-500 text-white p-3 rounded mb-4"> <?php echo $errMsg; ?> </div>
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
        $query = "SELECT 
            e.employee_id,
            e.first_name,
            e.last_name,
            e.hire_date,
            er.recommendation_id,
            er.recommendation_type,
            er.reason,
            COALESCE(AVG(CASE 
                WHEN a.status = 'On Time' THEN 1
                WHEN a.status = 'Late' THEN 0.5
                ELSE 0 
            END), 0) as attendance_score,
            COALESCE(AVG(js.overall_rating) / 5, 0) as satisfaction_score,
            COALESCE(AVG(pe.overall_score) / 100, 0) as performance_score,
            DATEDIFF(CURRENT_DATE, e.hire_date) / 365 as years_of_service
            FROM employees e
            LEFT JOIN attendance a ON e.employee_id = a.employee_id
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
                $satisfaction_score = floatval($row['satisfaction_score']) * 20; // Convert to percentage
                $performance_score = floatval($row['performance_score']);
                $years_of_service = floatval($row['years_of_service']);

                $risk_score = calculateAttritionRisk(
                    $attendance_score,
                    $satisfaction_score / 100, // Convert back to 0-1 scale for risk calculation
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
                                <input class='btn btn-warning' type='submit' name='approve' value='Approve'/>
                                <input class='btn btn-danger' type='submit' name='reject' value='Reject'/>
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
// Linear Regression calculation functions
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
.action-buttons {
    display: flex;
    gap: 5px;
    flex-direction: column;
}

.attrition-table-container {
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

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
    background-color: #f5f5f5;
    font-weight: bold;
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
</style>

<script>
$(document).ready(function() {
    $('#attritionTable').DataTable({
        order: [[5, 'desc']], // Sort by attrition risk by default
        pageLength:
        responsive: true
    });
});
</script>

<?php include('footer.php'); ?>
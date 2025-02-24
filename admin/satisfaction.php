<?php include('header.php'); ?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>


<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<main class="main-content">
    <section id="dashboard">
        <h2>SATISFACTION MONITORING</h2>
        <?php
        // Database connection
        $conn = mysqli_connect('localhost', 'root', '', 'esetech');
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Handle form open/close action
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $status = $_POST['status']; // 'Open' or 'Closed'

            // Update the form status in the database
            $update_sql = "UPDATE job_satisfaction_form_status SET status = '$status' WHERE status_id = 1";
            
            if (mysqli_query($conn, $update_sql)) {
                // Refresh the status after successful update
                echo "Form status updated to $status.";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }

        // Fetch current form status
        $status_sql = "SELECT status FROM job_satisfaction_form_status WHERE status_id = 1";
        $status_result = mysqli_query($conn, $status_sql);
        if ($status_result && mysqli_num_rows($status_result) > 0) {
            $status_row = mysqli_fetch_assoc($status_result);
            $current_status = $status_row['status'];
        } else {
            $current_status = 'Closed'; // Default status if no entry is found
        }
        ?>

        <h2>Job Satisfaction Survey Control</h2>

        <form method="POST" class="evaluation-form">
            <div class="form-group">
            <label for="status">Survey Status:</label>
            <select name="status" id="status">
                <option value="Open" <?php echo ($current_status == 'Open') ? 'selected' : ''; ?>>Open</option>
                <option value="Closed" <?php echo ($current_status == 'Closed') ? 'selected' : ''; ?>>Closed</option>
            </select>
            <br><br>
            <button type="submit">Update Survey Status</button>
        </form>
        <p>Current Form Status: <?php echo $current_status; ?></p>
    </div>
    </form>
    
    <br>

        <form method="POST" class="evaluation-form">
            <div class="form-group">
        <?php
        // Fetch employees who have already answered the survey
        $query = "
            SELECT 
    e.employee_id, 
    CONCAT(e.first_name, ' ', e.middle_name, ' ', e.last_name) AS full_name, 
    ss.survey_id,
    ss.survey_date, 
    ss.overall_rating, 
    ss.rating_description
FROM employees e
LEFT JOIN job_satisfaction_surveys ss 
ON e.employee_id = ss.employee_id
ORDER BY ss.survey_date DESC;
        ";

        $result = mysqli_query($conn, $query);
        if ($result) {
            echo '<table id="myTable">';
            echo '<thead><tr><th>Employee Name</th><th>Survey Date</th><th>Rating</th><th>Feedback</th><th>Action</th></tr></thead>';
            echo '<tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';
                echo '<td>' . ($row['survey_date'] ? $row['survey_date'] : 'Not Answered') . '</td>';
                echo '<td>' . ($row['overall_rating'] ? $row['overall_rating'] : 'No Rating') . '</td>';
                echo '<td>' . ($row['rating_description'] ? $row['rating_description'] : 'No Feedback') . '</td>';
                // Fixing the if condition by adding the missing closing parenthesis and ensuring proper logic
                if ($row['survey_date'] != null || $row['overall_rating'] != null || $row['rating_description'] != null) {
                    echo '<td>' 
                        . '<div class="action-buttons">'
                            . '<a href="view_satisfaction?id=' . $row['survey_id'] . '" class="btn">View</a>'
                        . '</div>'
                    . '</td>';
                } else {
                    echo '<td>-----</td>';
                }
        
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
        

        mysqli_close($conn); // Close connection after all queries
        ?>
        </div>
    </form>
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
            $('#myTable').DataTable({
                "paging": true,  
                "ordering": false, 
                "info": true  
            });
        });

    //     $(document).ready(function () {
    //     // Initialize DataTable
    //     $('#myTable').DataTable();
    // });
</script>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<?php include('footer.php'); ?>

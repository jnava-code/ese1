<?php
include('user_header.php');
include('includes/sideBar.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_SESSION['employee_id'];
    
    $work_life_balance = $_POST['work_life_balance'];
    $job_security = $_POST['job_security'];
    $compensation = $_POST['compensation'];
    $work_environment = $_POST['work_environment'];
    $career_growth = $_POST['career_growth'];
    $relationship_with_management = $_POST['relationship_with_management'];
    $overall_rating = $_POST['overall_rating'];
    $comments = mysqli_real_escape_string($conn, $_POST['comments']);

    $sql = "INSERT INTO job_satisfaction_surveys (
        employee_id, survey_date, work_life_balance, job_security,
        compensation, work_environment, career_growth,
        relationship_with_management, overall_rating, comments
    ) VALUES (?, CURRENT_DATE, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiiiiiis", 
        $employee_id, $work_life_balance, $job_security,
        $compensation, $work_environment, $career_growth,
        $relationship_with_management, $overall_rating, $comments
    );

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Thank you for submitting your satisfaction survey!";
        header("Location: " . preg_replace('/\.php$/', '', $_SERVER['REQUEST_URI']));
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting survey: " . $stmt->error;
    }
}
?>

<main class="main-content">
    <section id="dashboard">
        <h2>Job Satisfaction Survey</h2>
        
        <form method="POST" class="satisfaction-form">
            <div class="form-group">
                <label>Work-Life Balance</label>
                <input type="range" name="work_life_balance" min="1" max="5" class="range-input" required>
                <span class="range-value">3</span>
            </div>

            <div class="form-group">
                <label>Job Security</label>
                <input type="range" name="job_security" min="1" max="5" class="range-input" required>
                <span class="range-value">3</span>
            </div>

            <div class="form-group">
                <label>Compensation</label>
                <input type="range" name="compensation" min="1" max="5" class="range-input" required>
                <span class="range-value">3</span>
            </div>

            <div class="form-group">
                <label>Work Environment</label>
                <input type="range" name="work_environment" min="1" max="5" class="range-input" required>
                <span class="range-value">3</span>
            </div>

            <div class="form-group">
                <label>Career Growth</label>
                <input type="range" name="career_growth" min="1" max="5" class="range-input" required>
                <span class="range-value">3</span>
            </div>

            <div class="form-group">
                <label>Relationship with Management</label>
                <input type="range" name="relationship_with_management" min="1" max="5" class="range-input" required>
                <span class="range-value">3</span>
            </div>

            <div class="form-group">
                <label>Overall Satisfaction</label>
                <input type="range" name="overall_rating" min="1" max="5" class="range-input" required>
                <span class="range-value">3</span>
            </div>

            <div class="form-group">
                <label>Additional Comments</label>
                <textarea name="comments" rows="4"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Survey</button>
        </form>
    </section>
</main>

<style>
.satisfaction-form {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.range-input {
    width: 100%;
    margin-right: 10px;
}

.range-value {
    font-weight: bold;
}

textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<script>
// Update range input values
document.querySelectorAll('.range-input').forEach(input => {
    input.addEventListener('input', function() {
        this.nextElementSibling.textContent = this.value;
    });
});
</script>

<?php include('user_footer.php'); ?> 
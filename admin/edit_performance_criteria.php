<?php
    include('header.php');
    include('includes/sideBar.php');

    if (isset($_POST['delete_performance'])) {
        // Get the criteria ID from the hidden input field
        $criteria_id = $_POST['criteria_id'];
    
        // Delete query
        $deleteSql = "DELETE FROM performance_criteria WHERE id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $criteria_id);
    
        if ($stmt->execute()) {
            // If deletion is successful, show a success message
            $message = '<p style="color: green;">Performance Criteria successfully deleted.</p>';
        } else {
            // If there's an error in deletion, show an error message
            $message = '<p style="color: red;">Error deleting performance criteria.</p>';
        }
    }

    if (isset($_POST['archive_performance'])) {
        // Get the criteria ID from the hidden input field
        $criteria_id = $_POST['criteria_id'];

        // Update query to archive the performance criteria
        $archiveSql = "UPDATE performance_criteria SET is_archived = 1 WHERE id = ?";
        $stmt = $conn->prepare($archiveSql);
        $stmt->bind_param('i', $criteria_id);

        if ($stmt->execute()) {
            // If archiving is successful, show a success message
            $message = '<p style="color: green;">Performance Criteria has been archived successfully.</p>';
        } else {
            // If there's an error in archiving, show an error message
            $message = '<p style="color: red;">Error archiving performance criteria.</p>';
        }
    }

    // Check if the restore action is triggered
if (isset($_POST['restore_performance'])) {
    // Get the performance criterion ID from the hidden input field
    $criteria_id = $_POST['criteria_id'];

    // Update query to restore the performance criterion
    $restoreSql = "UPDATE performance_criteria SET is_archived = 0 WHERE id = ?";
    $stmt = $conn->prepare($restoreSql);
    $stmt->bind_param('i', $criteria_id);

    // Execute the query
    if ($stmt->execute()) {
        // If restoring is successful, show a success message
        $message = '<p style="color: green;">Performance criterion has been restored successfully.</p>';
    } else {
        // If there's an error in restoring, show an error message
        $message = '<p style="color: red;">Error restoring performance criterion.</p>';
    }
}

if (isset($_POST['add_criteria'])) {
    $criteria_name = $_POST['criteria_name'];
    $is_archived = 0;

    // Use prepared statement to avoid SQL injection
    $selectCriteriaSql = "SELECT * FROM performance_criteria WHERE description = ?";
    $stmt = $conn->prepare($selectCriteriaSql);
    $stmt->bind_param('s', $criteria_name);
    $stmt->execute();
    $criteriaSelectResult = $stmt->get_result();

    if ($criteriaSelectResult->num_rows > 0) {
        // Department already exists
        $message = '<p style="color: red;">The performance criteria already exists.</p>';
    } else {
        // Insert new department
        $sql = "INSERT INTO performance_criteria (description, is_archived) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $criteria_name, $is_archived);  // 's' for string, 'i' for integer
        $criteriaSelectResult = $stmt->execute();

        if ($criteriaSelectResult) {
            // Department successfully added
            $message = '<p style="color: green;">The performance criteria has been successfully added.</p>';
        } else {
            // Error in insertion
            $message = '<p style="color: red;">Error adding performance criteria.</p>';
        }
    }
}

if (isset($_POST['update_criteria'])) {
    $criteria_id = $_POST['criteria_id'];
    $criteria_name = $_POST['criteria_name'];

    $updateSql = "UPDATE performance_criteria SET description = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('si', $criteria_name, $criteria_id);
    if ($stmt->execute()) {
        $message = '<p style="color: green;">Performance Criteria successfully updated.</p>';
    } else {
        $message = '<p style="color: red;">Error updating performance criteria.</p>';
    }
}
?>

<main class="main-content">
    <section id="dashboard">
        <div class="performance-and-button">
            <h2>EDIT PERFORMANCE CRITERIA</h2>

            <div>
                <a href="#" id="add_performance_criteria_btn" class="btn btn-danger">ADD PERFORMANCE CRITERIA</a>
                <a href="performance-evaluation" class="btn btn-danger">BACK</a>
            </div>
        </div>      
        <div class="add-criteria-content">
            <h3>ADD PERFORMANCE CRITERIA</h3>
            <form method="POST" class="label-and-input">
                <label for="criteria_name">Performance Criteria: </label>
                <input id="criteria_name" name="criteria_name" type="text" value="" required>

                <div class="action-buttons">
                    <input class="btn" type="submit" name="add_criteria" value="Add">
                </div>         
            </form>
        </div>
        <div class="add-criteria-background"></div>

        <div class="card">
            <h3>Performance Criteria</h3>
            <?php if (!empty($message)) echo $message; ?>
            
            <!-- Performance Criteria Table -->
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead> 
                <tbody>
                    <?php
                        // Query to get performance criteria
                        $performance_sql = "SELECT * FROM performance_criteria";
                        $performance_result = mysqli_query($conn, $performance_sql);
                        if ($performance_result) {
                            // Loop through each row of the query result
                            while ($row = mysqli_fetch_assoc($performance_result)) {
                    ?>

                                <div class="edit-criteria-content">
                                    <h3>EDIT PERFORMANCE CRITERIA</h3>
                                    <form method="POST" class="label-and-input" id="edit-criteria-form">
                                        <label for="criteria_name">Performance Criteria: </label>
                                        <input type="hidden" name="criteria_id" value="<?php echo $row['id']; ?>" />
                                        <input type="text" id="criteria_name" name="criteria_name" value="<?php echo $row['description'] ?>" required>

                                        <div class="action-buttons">
                                            <input class="btn" type="submit" name="update_criteria" value="Update">
                                        </div>
                                    </form>
                                </div>
                        <tr>
                            <td><?php echo $row['description']; ?>:</td>
                            <td class="action-buttons">                               
                                <!-- Edit Button -->
                                <form method="POST" style="display:inline;">
                                    <button id="edit_performance_criteria_btn" class="btn btn-warning" type="button">Edit</button>
                                </form>
                                                                                   
                                <div class="edit-criteria-background"></div>

                                <!-- Delete form -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="criteria_id" value="<?php echo $row['id']; ?>" />
                                    <button type="submit" name="delete_performance" class="btn btn-danger" onclick='return confirm("Are you sure you want to delete this performance criteria?");'>Delete</button>
                                </form>
                                <!-- Archive or Restore Buttons -->
                                <?php 
                                    if ($row['is_archived'] == 0) { 
                                ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="criteria_id" value="<?php echo $row['id']; ?>" />
                                        <button type="submit" name="archive_performance" class="btn btn-danger">Archive</button>
                                    </form>
                                <?php 
                                    } else {
                                ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="criteria_id" value="<?php echo $row['id']; ?>" />
                                        <button type="submit" name="restore_performance" class="btn btn-restore">Restore</button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php
                            }
                        }
                    ?>
                </tbody>                
            </table>
        </div>
    </section>
</main>

<script>
    const addCriteriaBtn = document.getElementById("add_performance_criteria_btn");
    const addCriteriaCont = document.querySelector(".add-criteria-content");
    const criteriaBack = document.querySelector(".add-criteria-background");

    function addOrRemove(decision) {
        if (decision === "add") {
            addCriteriaCont.classList.add("show");
            criteriaBack.classList.add("show");
        } else {
            addCriteriaCont.classList.remove("show");
            criteriaBack.classList.remove("show");
        }
    }

    addCriteriaBtn.addEventListener("click", e => {
        e.preventDefault();
        addOrRemove("add"); 
    });

    criteriaBack.addEventListener("click", e => {
        e.preventDefault();
        addOrRemove("remove");
    })

    // Select all edit buttons and the edit form container
    const editCriteriaBtns = document.querySelectorAll("#edit_performance_criteria_btn");
    const editForms = document.querySelectorAll('.edit-criteria-content');
    const editCriteriaBacks = document.querySelectorAll(".edit-criteria-background");

    // Loop through all the edit buttons and add the click event listeners
    editCriteriaBtns.forEach((editCriteriaBtn, index) => {
        editCriteriaBtn.addEventListener("click", e => {
            e.preventDefault();
            // Show the respective form and background overlay
            editForms[index].classList.add("show");
            editCriteriaBacks[index].classList.add("show");
        });
    });

    // Add event listener to all background elements to hide the forms when clicked
    editCriteriaBacks.forEach((editCriteriaBack, index) => {
        editCriteriaBack.addEventListener("click", e => {
            e.preventDefault();
            // Hide the respective form and background overlay
            editForms[index].classList.remove("show");
            editCriteriaBack.classList.remove("show");
        });
    });

    
// Function to toggle the edit form and populate it with the current criteria data
function editCriteria(criteriaId, criteriaName) {
    const editForm = document.querySelector('.edit-criteria-content');

    if (editForm.style.display === 'block') {
        editForm.style.display = 'none';
    } else {
        // Populate the input with the current data
        document.getElementById('criteria_name').value = criteriaName;

        // Add the criteria ID as a hidden input for submission
        const form = document.getElementById('edit-criteria-form');
        
        // Remove any existing criteria_id input before appending a new one (important for toggle behavior)
        const existingCriteriaIdInput = form.querySelector('input[name="criteria_id"]');
        if (existingCriteriaIdInput) {
            existingCriteriaIdInput.remove();
        }

        // Create a new hidden input for the criteria ID
        let criteriaIdInput = document.createElement("input");
        criteriaIdInput.type = "hidden";
        criteriaIdInput.name = "criteria_id";  // Make sure this is named "criteria_id"
        criteriaIdInput.value = criteriaId;
        form.appendChild(criteriaIdInput);

        // Show the edit form
        editForm.style.display = 'block';
    }
}

// Handle the form submission (update logic)
document.getElementById('edit-criteria-form').addEventListener('submit', function(event) {
    event.preventDefault();

    const criteriaName = document.getElementById('criteria_name').value;
    const criteriaId = document.querySelector('input[name="criteria_id"]').value;

    // Send the updated data to the server (AJAX or form submission)
    const formData = new FormData();
    formData.append("criteria_id", criteriaId);
    formData.append("criteria_name", criteriaName);
    formData.append("update_criteria", true);

    fetch(window.location.href, {
        method: "POST",
        body: formData,
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        // Optionally, close the form and update the table
        document.querySelector('.edit-criteria-content').style.display = 'none';
        location.reload();
    })
    .catch(error => console.error('Error:', error));
});

</script>
<?php
    include('footer.php');
    mysqli_close($conn); // Close the connection at the end
?>

<?php
include('header.php'); // Admin header file

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Build the SQL query with JOIN
$sql = "SELECT 
            id,
            CONCAT(first_name, ' ', last_name) AS employee_name,
            sick_leave,
            vacation_leave,
            maternity_leave,
            paternity_leave,
            sick_availed,
            vacation_availed,
            maternity_availed,
            paternity_availed,
            gender
            FROM employees
            WHERE employment_status = 'Regular'";

$result = mysqli_query($conn, $sql);

$query_string = $_SERVER['QUERY_STRING'];

// if (isset($_POST['update'])) {
//     $id = $_POST['id'];
//     $gender = $_POST['gender'];
//     $sick_leave = $_POST['sick_leave'];
//     $vacation_leave = $_POST['vacation_leave'];
    
//     // Ensure only male employees have paternity leave and only female employees have maternity leave
//     $paternity_leave = ($gender == 'Male') ? $_POST['paternity_leave'] : 0;
//     $maternity_leave = ($gender == 'Female') ? $_POST['maternity_leave'] : 0;

//     // Update the database
//     $sql_update = "UPDATE `employees` 
//                    SET `sick_leave`='$sick_leave', 
//                        `vacation_leave`='$vacation_leave', 
//                        `paternity_leave`='$paternity_leave', 
//                        `maternity_leave`='$maternity_leave' 
//                    WHERE id='$id'";

//     $update_result = mysqli_query($conn, $sql_update);

//     if ($update_result) {
//         echo "<script>alert('Leave updated successfully!'); window.location.href='remaining_leave';</script>"; // Success message
//     } else {
//         echo "<script>alert('Error updating leave! Please try again.');</script>"; // Error message
//     }
// }

?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<!-- CSS -->
<style>
/* Reuse the same styles from the Attendance table for consistency */
.report_btn {
        display: flex;
        align-items: center;

        margin-bottom: 15px;
    }

    .report_btn button {
        border-radius: 0px;
        cursor: pointer;
    }
    
/* Style for Table */
#attendance-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

#attendance-table th, #attendance-table td {
    padding: 12px;
    /* text-align: left; */
}

#attendance-table th {
    background-color: #4CAF50;
    color: white;
}

#attendance-table tr:nth-child(even) {
    background-color: #f2f2f2;
}

#attendance-table tr:hover {
    background-color: #ddd;
}

/* Buttons inside the table */
button {
    padding: 8px 12px;
    font-size: 14px;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button[name="action"][value="approve"] {
    background-color: #4CAF50;
}

button[name="action"][value="reject"] {
    background-color: #e74c3c;
}

button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

/* Filter Buttons */
.filter-buttons {
    margin-bottom: 20px;
}

.filter-buttons a {
    padding: 8px 16px;
    text-decoration: none;
    color: white;
    background-color: #b01515;
    border-radius: 4px;
    margin-right: 10px;
}

.filter-buttons a:hover {
    background-color: #bd2828;
}

.filter-buttons a.active {
    background-color: #de0d0d;
}

@media print {
        header,
        .main-content h2,
        .filter-buttons,
        .report_btn,
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        th.sorting::before,
        th.sorting_asc::before,
        th.sorting_desc::before,
        th.sorting::after,
        th.sorting_asc::after,
        th.sorting_desc::after {
            content: none !important;
            display: none !important;
        }

        table th,
        table tr {
            font-size: 12px;
            padding: 5px;
        }

        table.dataTable thead>tr>th.sorting {
            padding-right: 0px;
        }
    }

    .leave_modal {
        display: none;
    }

    .leave_modal.show {
        display: flex;
        gap: 5px;
        flex-direction: column;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        width: 350px;
        padding: 25px;
        background-color: #fff;
    }

    .leave_content {
        display: flex;
        gap: 5px;
        flex-direction: column;
    }

    .leave_content input {
        padding: 5px;
    }

    input[type="submit"],
    button[type="button"] {
        padding: 12px 20px;
        border: none;
        border-radius: 5px;
        background-color: #007bff;
        color: #fff;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 5px;
    }

    input[type="submit"]:hover,
    button[type="button"]:hover {
        background-color: #0056b3;
    }

    .title-and-x {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .title-and-x span {
        font-size: 24px;

        cursor: pointer;
    }

    .styled-table {
        width: 100%;
        border-collapse: collapse;
    }

    .styled-table th, 
    .styled-table td {
        border: 1px solid black; /* Adds border */
        padding: 8px;
        text-align: center;
    }

    .styled-table th {
        background-color: #f2f2f2; /* Light gray background for headers */
    }

</style>

<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>REMAINING DAYS OF LEAVE</h2>
        <!-- Styled Leave Requests Table -->
        <table id="myTable" class="styled-table">
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <?php 
            $total_remaining = $row['sick_leave'] + $row['vacation_leave'] + ($row['maternity_leave'] ?? 0) + ($row['paternity_leave'] ?? 0);
            $total_availed = $row['sick_availed'] + $row['vacation_availed'] + ($row['maternity_availed'] ?? 0) + ($row['paternity_availed'] ?? 0);
        ?>    

        <thead>
            <tr>
                <th rowspan="2" style="text-align: center;">Employee Name</th>
                <th colspan="2" style="text-align: center;">Sick Leave: <?php echo htmlspecialchars($row['sick_leave']); ?></th>
                <th colspan="2" style="text-align: center;">Vacation Leave: <?php echo htmlspecialchars($row['vacation_leave']); ?></th>
                <th colspan="2" style="text-align: center;">Maternity Leave: <?php echo strtolower($row['gender']) == 'female' ? htmlspecialchars($row['maternity_leave']) : '--'; ?></th>           
                <th colspan="2" style="text-align: center;">Paternity Leave: <?php echo strtolower($row['gender']) == 'male' ? htmlspecialchars($row['paternity_leave']) : '--'; ?></th>           
                <th colspan="2" style="text-align: center;">Total Days of Leave</th>
            </tr>
            <tr>
                <th>Remaining</th>
                <th>Availed</th>
                <th>Remaining</th>
                <th>Availed</th>
                <th>Remaining</th>
                <th>Availed</th>
                <th>Remaining</th>
                <th>Availed</th>
                <th>Remaining</th>
                <th>Availed</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                <td><?php echo htmlspecialchars($row['sick_leave'] - $row['sick_availed']); ?></td>
                <td><?php echo htmlspecialchars($row['sick_availed']); ?></td>
                <td><?php echo htmlspecialchars($row['vacation_leave'] - $row['vacation_availed']); ?></td>
                <td><?php echo htmlspecialchars($row['vacation_availed']); ?></td>
                <td><?php echo strtolower($row['gender']) == 'female' ? htmlspecialchars($row['maternity_leave'] - $row['maternity_availed']) : '--'; ?></td>
                <td><?php echo strtolower($row['gender']) == 'female' ? htmlspecialchars($row['maternity_availed']) : '--'; ?></td>
                <td><?php echo strtolower($row['gender']) == 'male' ? htmlspecialchars($row['paternity_leave'] - $row['paternity_availed']) : '--'; ?></td>
                <td><?php echo strtolower($row['gender']) == 'male' ? htmlspecialchars($row['paternity_availed']) : '--'; ?></td>
                <td><?php echo htmlspecialchars($total_remaining); ?></td>
                <td><?php echo htmlspecialchars($total_availed); ?></td>
            </tr>      
        </tbody>
    <?php } ?>
</table>

    </section>
</main>

<?php
mysqli_close($conn); // Close database connection
include('footer.php'); // Admin footer file
?>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/docx/7.1.0/docx.min.js"></script>
<script>
//   $(document).ready( function () {
//     $('#myTable').DataTable();
//   });

</script>

<!-- <script>
            // Unique variable names for each modal
            const addLeaveButton_<?php echo $row['id']?> = document.getElementById('add_leave_<?php echo $row['id']?>');
            const leaveModal_<?php echo $row['id']?> = document.getElementById('leave_modal_<?php echo $row['id']?>');
            const closeModal_<?php echo $row['id']?> = document.getElementById('close_modal_<?php echo $row['id']?>');

            // Add event listener to show modal
            if(addLeaveButton_<?php echo $row['id']?>) {
                addLeaveButton_<?php echo $row['id']?>.addEventListener('click', function () {
                    leaveModal_<?php echo $row['id']?>.classList.add('show'); // Show the modal
                });
            }

            // Add event listener to close modal
            if(closeModal_<?php echo $row['id']?>) {
                closeModal_<?php echo $row['id']?>.addEventListener('click', function () {
                    leaveModal_<?php echo $row['id']?>.classList.remove('show'); // Hide the modal
                });         
            }
        </script> -->

        <!-- <form id="leave_modal_<?php echo $row['id']?>" class="leave_modal" method="post">
                    <input type="hidden" name="id" id="employee_id_<?php echo $row['id']?>" value="<?php echo $row['id']?>">
                    <input type="hidden" name="gender" value="<?php echo $row['gender']?>">
                    <div class="title-and-x">
                        <h2>Update Leave</h2>
                        <span class="close_modal" id="close_modal_<?php echo $row['id']?>">&#215;</span>
                    </div>
                    <div class="leave_content">
                        <label for="">Sick Leave</label>
                        <input type="number" name="sick_leave" id="sick_leave_<?php echo $row['id']?>" value="<?php echo $row['sick_leave']?>" placeholder="Sick Leave">
                        <label for="">Vacation Leave</label>
                        <input type="number" name="vacation_leave" id="vacation_leave_<?php echo $row['id']?>" value="<?php echo $row['vacation_leave']?>" placeholder="Vacation Leave">
                        <?php if(strtolower($row['gender']) == 'male'): ?>
                            <label for="">Paternity Leave</label>
                            <input type="number" name="paternity_leave" id="paternity_leave_<?php echo $row['id']?>" value="<?php echo $row['paternity_leave']?>" placeholder="Paternity Leave">
                        <?php else: ?>                            
                            <label for="">Maternity Leave</label>
                            <input type="number" name="maternity_leave" id="maternity_leave_<?php echo $row['id']?>" value="<?php echo $row['maternity_leave']?>" placeholder="Maternity Leave">
                        <?php endif ?>
                    </div>
                    <input type="submit" name="update" value="Update Leave">
                </form> -->
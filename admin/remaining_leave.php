<?php
include('header.php'); // Admin header file

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
}

#attendance-table th {
    background-color: #b01515;
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

/* Styled Leave Requests Table */
.styled-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    overflow-x: auto; /* Add horizontal scroll */
}

.styled-table th, 
.styled-table td {
    border: 1px solid black; /* Adds border */
    padding: 12px; /* Increased padding for spacing */
    text-align: center;
}

.styled-table th {
    background-color:rgb(131, 39, 39); /* Updated color */
    color: white;
}

.styled-table tr:nth-child(even) {
    background-color: #f2f2f2;
}

.styled-table tr:hover {
    background-color: #ddd;
}

/* Responsive table */
@media screen and (max-width: 768px) {
    .styled-table thead {
        display: none;
    }

    .styled-table, .styled-table tbody, .styled-table tr, .styled-table td {
        display: block;
        width: 100%;
    }

    .styled-table tr {
        margin-bottom: 15px;
    }

    .styled-table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }

    .styled-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 0;
        width: 50%;
        padding-left: 15px;
        font-weight: bold;
        text-align: left;
    }
}

/* Add this CSS to make the table responsive */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
</style>

<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>REMAINING DAYS OF LEAVE</h2>
        <!-- Styled Leave Requests Table -->
        <div class="table-responsive">
            <table id="myTable" class="styled-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="text-align: center;">Employee Name</th>
                        <th colspan="3" style="text-align: center;">Sick Leave</th>
                        <th colspan="3" style="text-align: center;">Vacation Leave</th>
                        <th colspan="3" style="text-align: center;">Maternity Leave</th>           
                        <th colspan="3" style="text-align: center;">Paternity Leave</th>           
                        <th colspan="3" style="text-align: center;">Days of Leave</th>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <th>Remaining</th>
                        <th>Availed</th>
                        <th>Total</th>
                        <th>Remaining</th>
                        <th>Availed</th>
                        <th>Total</th>
                        <th>Remaining</th>
                        <th>Availed</th>
                        <th>Total</th>
                        <th>Remaining</th>
                        <th>Availed</th>
                        <th>Total</th>
                        <th>Remaining</th>
                        <th>Availed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <?php 
                            $total = $row['sick_leave'] + $row['vacation_leave'] + ($row['maternity_leave'] ?? 0) + ($row['paternity_leave'] ?? 0);
                            $total_remaining = ($row['sick_leave'] - $row['sick_availed']) 
                            + ($row['vacation_leave'] - $row['vacation_availed'])
                            + ($row['maternity_leave'] - $row['maternity_availed'] ?? 0) 
                            + ($row['paternity_leave'] - $row['paternity_availed'] ?? 0);
                            $total_availed = $row['sick_availed'] + $row['vacation_availed'] + ($row['maternity_availed'] ?? 0) + ($row['paternity_availed'] ?? 0);
                        ?>    

                        <tr>
                            <td data-label="Employee Name"><?php echo htmlspecialchars($row['employee_name']); ?></td>
                            <td data-label="Sick Leave Total"><?php echo htmlspecialchars($row['sick_leave']); ?></td>
                            <td data-label="Sick Leave Remaining"><?php echo htmlspecialchars($row['sick_leave'] - $row['sick_availed']); ?></td>
                            <td data-label="Sick Leave Availed"><?php echo htmlspecialchars($row['sick_availed']); ?></td>
                            <td data-label="Vacation Leave Total"><?php echo htmlspecialchars($row['vacation_leave']); ?></td>
                            <td data-label="Vacation Leave Remaining"><?php echo htmlspecialchars($row['vacation_leave'] - $row['vacation_availed']); ?></td>
                            <td data-label="Vacation Leave Availed"><?php echo htmlspecialchars($row['vacation_availed']); ?></td>
                            <td data-label="Maternity Leave Total"><?php echo strtolower($row['gender']) == 'female' ? htmlspecialchars($row['maternity_leave']) : '--'; ?></td>
                            <td data-label="Maternity Leave Remaining"><?php echo strtolower($row['gender']) == 'female' ? htmlspecialchars($row['maternity_leave'] - $row['maternity_availed']) : '--'; ?></td>
                            <td data-label="Maternity Leave Availed"><?php echo strtolower($row['gender']) == 'female' ? htmlspecialchars($row['maternity_availed']) : '--'; ?></td>
                            <td data-label="Paternity Leave Total"><?php echo strtolower($row['gender']) == 'male' ? htmlspecialchars($row['paternity_leave']) : '--'; ?></td>
                            <td data-label="Paternity Leave Remaining"><?php echo strtolower($row['gender']) == 'male' ? htmlspecialchars($row['paternity_leave'] - $row['paternity_availed']) : '--'; ?></td>
                            <td data-label="Paternity Leave Availed"><?php echo strtolower($row['gender']) == 'male' ? htmlspecialchars($row['paternity_availed']) : '--'; ?></td>
                            <td data-label="Total Days of Leave"><?php echo htmlspecialchars($total); ?></td>
                            <td data-label="Remaining Days of Leave"><?php echo htmlspecialchars($total_remaining); ?></td>
                            <td data-label="Availed Days of Leave"><?php echo htmlspecialchars($total_availed); ?></td>
                        </tr>      
                    <?php } ?>
                </tbody>
            </table>
        </div>
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
  $(document).ready( function () {
    $('#myTable').DataTable();
  });
</script>

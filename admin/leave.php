<?php
include('header.php'); // Admin header file

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the filter from the URL or default to 'all'
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the SQL query with JOIN
$sql = "
    SELECT 
        leave_applications.*, 
        employees.department,
        employees.position,
        employees.employment_status,
        CONCAT(employees.first_name, ' ', employees.middle_name, ' ', employees.last_name)  AS employee_name,
        employees.employee_id
    FROM leave_applications 
    LEFT JOIN employees 
    ON leave_applications.employee_id = employees.employee_id";
if ($status_filter != 'all') {
    $sql .= " WHERE leave_applications.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}
$sql .= " ORDER BY leave_applications.file_date DESC"; // Order by file_date in descending order

$result = mysqli_query($conn, $sql);

$query_string = $_SERVER['QUERY_STRING'];
?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<!-- CSS -->
<style>
        /* Dropdown styling */
        .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 120px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown-content a:hover {
        background-color: #f1f1f1;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropdown:hover .export_btn {
        background-color:rgb(33, 59, 173);
    }


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
    text-align: left;
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

.filter-search {
    display: flex;
    gap: 5px;
    margin-top: 10px;
    margin-bottom: 5px;
}

#myTable_filter {
    display: none;
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

.rejection-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

textarea {
    resize: none;
}

button[id="submit-reason"],
button[id="reject_btn"] {
    background-color: #e74c3c;
}

.filter-buttons {
    display: flex;
    justify-content: space-between;
}

.filter-buttons a {
    width: 100%;
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

    .title-and-xbtn {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .title-and-xbtn span {
        font-size:24px;
        cursor: pointer;
    }
</style>

<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>LEAVE APPLICATION REQUESTS</h2>
        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <a href="?status=all" class="<?php echo $status_filter == 'all' ? 'active' : ''; ?>">All</a>
            <a href="?status=Approved" class="<?php echo $status_filter == 'Approved' ? 'active' : ''; ?>">Accepted</a>
            <a href="?status=Pending" class="<?php echo $status_filter == 'Pending' ? 'active' : ''; ?>">Pending</a>
            <a href="?status=Rejected" class="<?php echo $status_filter == 'Rejected' ? 'active' : ''; ?>">Rejected</a>
        </div>

        <div class="filter-search">
            <select id="employee" name="employee" class="form-control">
                <option value="">Select Employee</option>
                <?php 
                    $empSelect = "SELECT first_name, middle_name, last_name FROM employees WHERE is_archived = 0 ORDER BY first_name ASC";
                    $empResult = mysqli_query($conn, $empSelect);

                    if ($empResult) {
                        $department = isset($department) ? htmlspecialchars($department) : ''; 

                        while ($row = mysqli_fetch_assoc($empResult)) {
                            $withMiddleName = htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
                            $noMiddleName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                            $fullName = $row['middle_name'] !== '' ? $withMiddleName : $noMiddleName;
                            ?>
                            <option value="<?php echo htmlspecialchars($fullName); ?>">
                                <?php echo htmlspecialchars($fullName); ?>
                            </option>
                            <?php
                        }
                    } 
                ?>
            </select>

            <select id="department" name="department" class="form-control">
                <option value="">Select Department</option>
                <?php 
                    $deptSelect = "SELECT * FROM departments WHERE is_archived = 0 ORDER BY dept_name ASC";
                    $deptResult = mysqli_query($conn, $deptSelect);

                    if ($deptResult) {
                        $department = isset($department) ? htmlspecialchars($department) : ''; 

                        while ($row = mysqli_fetch_assoc($deptResult)) {
                            if ($row['dept_name'] != "Admin") {
                                ?>
                                <option value="<?php echo htmlspecialchars($row['dept_name']); ?>">
                                    <?php echo htmlspecialchars($row['dept_name']); ?>
                                </option>
                                <?php
                            }
                        }
                    } 
                ?>
            </select>

            <select id="position-dropdown" name="position" class="form-control">
                <option value="">Select Position</option>
                <option value="Accounting Assistant" <?php echo (!empty($errmsg) && htmlspecialchars($position) == "Accounting Assistant") ? 'selected' : ''; ?>>Accounting Assistant</option>
                <option value="Junior Accountant" <?php echo (!empty($errmsg) && htmlspecialchars($position) == "Junior Accountant") ? 'selected' : ''; ?>>Junior Accountant</option>
                <option value="Technical" <?php echo (!empty($errmsg) && htmlspecialchars($position) == "Technical") ? 'selected' : ''; ?>>Technical</option>
                <option value="Electrical Tech" <?php echo (!empty($errmsg) && htmlspecialchars($position) == "Electrical Tech") ? 'selected' : ''; ?>>Electrical Tech</option>
                <option value="Project Engineer" <?php echo (!empty($errmsg) && htmlspecialchars($position) == "Project Engineer") ? 'selected' : ''; ?>>Project Engineer</option>
                <option value="ISC" <?php echo (!empty($errmsg) && htmlspecialchars($position) == "ISC") ? 'selected' : ''; ?>>ISC</option>
                <option value="Other" <?php echo (!empty($errmsg) && htmlspecialchars($position) == "Other") ? 'selected' : ''; ?>>Other</option>
            </select>

            <select id="employment_status" name="employment_status" class="form-control">
                <option value="">Select Employment Status</option>
                <option value="Regular">Regular</option>
                <option value="Probationary">Probationary</option>
            </select>
        </div>

        <!-- Styled Leave Requests Table -->
        <table id="myTable" class="styled-table">
    <thead>
        <tr>
            <th style="display: none"></th>
            <th style="display: none"></th>
            <th style="display: none"></th>
            <th>Employee Name</th>
            <th>Date of File</th>
            <th>Start Date</th>
            <th>End Date</th>           
            <th>Leave Type</th>           
            <th>No. Of Days</th>
            <th>Reason</th>
            <th>Status</th>
            <th class="actions">Action</th>
        </tr>
    </thead>

    <?php 
    echo $query_string == "status=Approved" 
    ? "<div class='report_btn'>
        <!-- Export as Dropdown -->
        <div class='dropdown'>
            <button class='btn export_btn'>Export as</button>
            <div class='dropdown-content'>
                <a href='#' class='pdf_btn'>PDF</a>
                <a href='#' class='excel_btn'>Excel</a>
                <a href='#' class='word_btn'>Word</a>
            </div>
        </div>

        <!-- Print Button -->
        <button class='btn print_btn'>Print</button>
    </div>" 
    : "";
?>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td style="display: none"><?php echo htmlspecialchars($row['department']); ?></td>
                <td style="display: none"><?php echo htmlspecialchars($row['position']); ?></td>
                <td style="display: none"><?php echo htmlspecialchars($row['employment_status']); ?></td>
                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                <td><?php echo htmlspecialchars($row['file_date']); ?></td>
                <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                <td><?php echo htmlspecialchars($row['number_of_days']); ?></td>
                <td>
                    <?php 
                        if (htmlspecialchars($row['status']) === 'Approved') {
                            echo htmlspecialchars($row['reason']);
                        } else {
                            echo htmlspecialchars($row['reason_of_rejection']);
                        }
                    ?>
                </td>

                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td class="actions">
                    <?php
                        if ($row['status'] == 'Pending') {
                            echo '<form action="./process_leave" method="POST">
                                    <input type="hidden" id="employee_id" name="employee_id" value="' . $row['employee_id'] . '">
                                    <input type="hidden" id="leave_id" name="leave_id" value="' . $row['leave_id'] . '">
                                    <input type="hidden" id="leave_type" name="leave_type" value="' . $row['leave_type'] . '">
                                    <input type="hidden" id="number_of_days" name="number_of_days" value="' . $row['number_of_days'] . '">
                                    <button type="submit" name="action" value="approve">
                                        Approve
                                    </button>
                                    <button style="background-color: red" id="reject_btn_' . $row['leave_id'] . ' " type="submit" value="reject">
                                        Reject
                                    </button>
                                </form>';
                        } else {
                            echo '-----';
                        }
                    ?>                 
                </td>
            </tr>
        <?php } ?>
    </tbody>
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
    var table = $('#myTable').DataTable({
        "order": [[1, "desc"]] // Order by the second column (Date of File) in descending order
    });

  const reportBtn = document.querySelector(".report_btn");

if (reportBtn) {
    reportBtn.addEventListener("click", (e) => {
        const clicked = e.target.closest(".btn, .dropdown-content a"); // Include <a> elements

        if (!clicked) return;

        if (clicked.classList.contains("print_btn")) {
            window.print();
        } else if (clicked.classList.contains("pdf_btn")) {
            const element = document.getElementById("myTable");

            const style = document.createElement("style");
            style.innerHTML = `
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
            `;

            document.head.appendChild(style);

            const clonedElement = element.cloneNode(true);

            html2pdf()
                .set({
                    margin: 1,
                    filename: "leave_requests.pdf",
                    image: { type: "jpeg", quality: 0.98 },
                    html2canvas: { dpi: 192, scale: 2, letterRendering: true, useCORS: true },
                    jsPDF: { unit: "mm", format: "a4", orientation: "landscape" }
                })
                .from(clonedElement)
                .toPdf()
                .save()
                .then(() => {
                    document.head.removeChild(style);
                });
        } else if (clicked.classList.contains("excel_btn")) {
            const table = document.getElementById("myTable");

            const rows = [];
            table.querySelectorAll("tr").forEach((row) => {
                const rowData = [];
                row.querySelectorAll("th, td").forEach((cell) => {
                    if (!cell.classList.contains("actions")) {
                        rowData.push(cell.innerText);
                    }
                });
                rows.push(rowData);
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(rows);
            XLSX.utils.book_append_sheet(wb, ws, "Leave Request");
            XLSX.writeFile(wb, "leave_request.xlsx");
        } else if (clicked.classList.contains("word_btn")) {
            const table = document.getElementById("myTable").cloneNode(true);
            table.querySelectorAll("th.actions, td.actions").forEach(cell => cell.remove());

            const htmlContent = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office" 
                      xmlns:w="urn:schemas-microsoft-com:office:word" 
                      xmlns="http://www.w3.org/TR/REC-html40">
                <head>
                    <meta charset="UTF-8">
                    <style>
                        body { margin: 5px; padding: 5px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid black; padding: 5px; text-align: left; }
                        table th,
                        table tr {
                            font-size: 12px;
                            padding: 5px;
                        }
                    </style>
                </head>
                <body>
                    ${table.outerHTML}
                </body>
                </html>`;

            const blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });

            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "leave_request.doc";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });
}
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function (event) {
        const parent = this.parentElement;

        // Prevent the link's default behavior
        event.preventDefault();

        // Toggle the active class
        parent.classList.toggle('active');
    });
});

var filters = [$('#department'), $('#position-dropdown'), $('#employment_status'), $('#employee')];

    var filterValues = {
        department: '',
        position: '',
        employment_status: '',
        employee: ''
    };

    $.each(filters, function(index, filter) {
        filter.on('change', function() {
            if (filter.is('#department')) {
                filterValues.department = $(this).val();
            } else if (filter.is('#position-dropdown')) {
                filterValues.position = $(this).val();
            } else if (filter.is('#employment_status')) {
                filterValues.employment_status = $(this).val();
            } else if (filter.is('#employee')) {
                filterValues.employee = $(this).val();
            }
            table
                .column(0).search(filterValues.department || '').draw()
                .column(1).search(filterValues.position || '').draw()   
                .column(2).search(filterValues.employment_status || '').draw()
                .column(3).search(filterValues.employee || '').draw();
        });
    });

    const leaveId = document.querySelectorAll('#leave_id');

    if(leaveId) {
        leaveId.forEach(id => {
            const rejectButton = document.getElementById('reject_btn_' + id.value);
            if (rejectButton) {
                rejectButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    reasonOfRejection(id.value)
                });
            }
        })
    }

    function reasonOfRejection(leave_id) {
        const rejectionModal = `<div id="rejection-modal_${leave_id}" class="rejection-modal">
                <div class="title-and-xbtn">
                    <h2>Reason for Rejection</h2>
                    <span id="xBtn_${leave_id}">&#215</span>
                </div>
                <form action="./process_leave" method="POST">
                    <input type="hidden" id="leave_id" name="leave_id" value="${leave_id}">
                    <textarea id="reason" rows="6" cols="50" name="reason_of_rejection" required></textarea>
                    <button type="submit" name="action" value="reject">Submit</button>
                </form>
            </div>`;

        document.body.insertAdjacentHTML('beforeend', rejectionModal);

        const xBtn = document.getElementById('xBtn_' + leave_id);

        if(xBtn) {
            xBtn.addEventListener('click', function() {
                const modal = document.getElementById('rejection-modal_' + leave_id);
                if (modal) {
                    modal.remove();
                }
            });
        }
    }

</script>
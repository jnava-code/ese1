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
        CONCAT(employees.first_name, ' ', employees.last_name) AS employee_name,
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

        <!-- Styled Leave Requests Table -->
        <table id="myTable" class="styled-table">
    <thead>
        <tr>
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
                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                <td><?php echo htmlspecialchars($row['file_date']); ?></td>
                <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                <td><?php echo htmlspecialchars($row['number_of_days']); ?></td>
                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td class="actions">
                    <?php
                        if ($row['status'] == 'Pending') {
                            echo '<form action="./process_leave" method="POST">
                                    <input type="hidden" name="leave_id" value="' . $row['leave_id'] . '">
                                    <input type="hidden" name="employee_id" value="' . $row['employee_id'] . '">
                                    <input type="hidden" name="leave_type" value="' . $row['leave_type'] . '">
                                    <input type="hidden" name="number_of_days" value="' . $row['number_of_days'] . '">
                                    <button type="submit" name="action" value="approve">
                                        Approve
                                    </button>
                                    <button type="submit" name="action" value="reject">
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
  $(document).ready( function () {
    $('#myTable').DataTable({
        "order": [[1, "desc"]] // Order by the second column (Date of File) in descending order
    });
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
</script>
<?php include('header.php'); ?>

<!-- ITO NA YUNG SIDEBAR PANEL (file located in "includes" folder) -->
<?php include('includes/sideBar.php'); ?>


<?php


$conn = mysqli_connect('localhost', 'root', '', 'esetech');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$employee_id = $_GET['employee_id'] ?? ''; 
$start_date = $_GET['start_date'] ?? ''; 
$end_date = $_GET['end_date'] ?? ''; 

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; 
$offset = ($page - 1) * $limit;

$conditions = [];
if ($employee_id) {
    $conditions[] = "attendance.employee_id = '" . mysqli_real_escape_string($conn, $employee_id) . "'"; 
}
if ($start_date) {
    $conditions[] = "attendance.date >= '" . mysqli_real_escape_string($conn, $start_date) . "'"; 
}
if ($end_date) {
    $conditions[] = "attendance.date <= '" . mysqli_real_escape_string($conn, $end_date) . "'"; 
}
$where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$today = date("Y-m-d");
$sql = "
    SELECT     
        e.employee_id,
        CONCAT(e.first_name, ' ', e.middle_name, ' ', e.last_name) AS full_name,
        a.date, 
        a.clock_in_time, 
        a.clock_out_time,
        a.total_hours,
        a.status,
        IFNULL(la.status, 'Absent') AS leave_status
    FROM attendance a
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    LEFT JOIN leave_applications la ON a.employee_id = la.employee_id
    WHERE a.date = '$today'
    $where 
    ORDER BY a.date DESC 
    LIMIT $limit OFFSET $offset";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$countQuery = "SELECT COUNT(*) as total FROM attendance a $where";
$countResult = mysqli_query($conn, $countQuery);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $limit);
?>

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

@media print {
        header,
        .main-content h2,
        .report_btn,
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate,
        .actions {
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

        table > thead > tr > th,
        table > tbody > tr > td {
            border-bottom: none !important;
            border: 1px solid #000 !important;
            font-size: 12px;
        }

        .evaluation-form {
            border: none;
            border-radius: none;
            margin-bottom: 0px;
            /* padding: 0px 10px !important; */
            box-shadow: none !important;
            
        }

        .main-content {
            padding: 0px !important;
        }   
    }
</style>

<!-- Main Content Area -->
<main class="main-content">
    <section id="dashboard">
        <h2>ATTENDANCE MONITORING</h2>
        <div class="report_btn">
                <!-- Export as Dropdown -->
                <div class="dropdown">
                    <button class="btn export_btn">Export as</button>
                    <div class="dropdown-content">
                        <a href="#" class="pdf_btn">PDF</a>
                        <a href="#" class="excel_btn">Excel</a>
                        <a href="#" class="word_btn">Word</a>
                    </div>
                </div>

        <!-- Print Button -->
        <button class="btn print_btn">Print</button>
    </div>
        
        <form method="POST" class="evaluation-form">
            <div class="form-group">
                <table id="myTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Employee ID</th> 
                            <th>Employee Name</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Total Hours</th>
                            <th>Status</th>       
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>                   
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td class="employee_display"><?php echo htmlspecialchars($row['employee_id']); ?></td> <!-- Display Employee ID -->
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo $row['date']; ?></td>
                                <td><?php echo $row['clock_in_time'] ? $row['clock_in_time'] : '-'; ?></td>
                                <td><?php echo $row['clock_out_time'] ? $row['clock_out_time'] : '-'; ?></td>
                                <td><?php echo $row['total_hours'] ? $row['total_hours'] : '-'; ?></td>
                                <td><?php echo $row['status']; ?></td> <!-- Use leave_status directly here -->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </section>
</main>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/docx/7.1.0/docx.min.js"></script>

<script>
    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault();
        const params = new URLSearchParams(new FormData(e.target));
        const query = params.toString();
        fetch(`fetch_attendance.php?${query}`)
            .then(response => response.text())
            .then(data => {
                document.querySelector("#attendance-table tbody").innerHTML = data;
            })
            .catch(error => console.error('Error fetching attendance:', error));
    });

    // SELECT THE CLASS NAME
    const employeeDisplay = document.querySelectorAll(".employee_display");

    employeeDisplay.forEach(display => {
        // Apply format: 00-000
        display.textContent = display.textContent.slice(0, 2) + '-' + display.textContent.slice(2, 5);
    });

    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function (event) {
        const parent = this.parentElement;

        // Prevent the link's default behavior
        event.preventDefault();

        // Toggle the active class
        parent.classList.toggle('active');
    });
});

document.addEventListener("DOMContentLoaded", () => {
    // Event listener for the dropdown export buttons
    document.querySelector(".dropdown-content").addEventListener("click", (e) => {
        const clicked = e.target.closest("a"); 
        if (!clicked) return;

        if (clicked.classList.contains("pdf_btn")) {
            const element = document.getElementById("myTable");

            const style = document.createElement("style");
            style.innerHTML = `
                header,
                .main-content h2,
                .report_btn,
                .dataTables_length,
                .dataTables_filter,
                .dataTables_info,
                .dataTables_paginate,
                .actions {
                    display: none !important;
                }
            `;
            document.head.appendChild(style);

            const clonedElement = element.cloneNode(true);

            html2pdf()
                .set({
                    margin: 1,
                    filename: "daily_attendance.pdf",
                    image: { type: "jpeg", quality: 0.98 },
                    html2canvas: { dpi: 192, scale: 2, letterRendering: true, useCORS: true },
                    jsPDF: { unit: "mm", format: "a4", orientation: "landscape" }
                })
                .from(clonedElement)
                .toPdf()
                .save()
                .then(() => document.head.removeChild(style));
        } else if (clicked.classList.contains("excel_btn")) {
            const table = document.getElementById("myTable");
            const rows = [];
            table.querySelectorAll("tr").forEach(row => {
                const rowData = [];
                row.querySelectorAll("th, td").forEach(cell => {
                    if (!cell.classList.contains("actions")) {
                        rowData.push(cell.innerText);
                    }
                });
                rows.push(rowData);
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(rows);
            XLSX.utils.book_append_sheet(wb, ws, "Daily Attendance");
            XLSX.writeFile(wb, "daily_attendance.xlsx");
        } else if (clicked.classList.contains("word_btn")) {
            const table = document.getElementById("myTable").cloneNode(true);
            table.querySelectorAll(".actions").forEach(cell => cell.remove());

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
                    </style>
                </head>
                <body>
                    ${table.outerHTML}
                </body>
                </html>`;

            const blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "daily_attendance.doc";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });

    // Separate event listener for the Print button
    document.querySelector(".print_btn").addEventListener("click", () => {
        window.print();
    });

    // Initialize DataTable
    $("#myTable").DataTable();
});
</script>

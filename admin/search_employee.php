<?php


$conn = mysqli_connect('localhost', 'root', '', 'esetech');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['query'])) {
    $searchTerm = "%" . $_GET['query'] . "%";
    
    $sql = "SELECT id, first_name, middle_name, last_name, 
                   CASE 
                       WHEN middle_name IS NULL OR middle_name = '' 
                       THEN CONCAT(first_name, ' ', last_name) 
                       ELSE CONCAT(first_name, ' ', middle_name, ' ', last_name) 
                   END AS full_name 
            FROM employees 
            WHERE first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? 
               OR CONCAT(first_name, ' ', last_name) LIKE ? 
               OR CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ? 
            LIMIT 10";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<div class="search-item" onclick="selectEmployee(' . $row['id'] . ', \'' . addslashes($row['full_name']) . '\')">'
                . htmlspecialchars($row['full_name']) . '</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
mysqli_close($conn);
?>

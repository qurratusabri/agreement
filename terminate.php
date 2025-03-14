<?php
session_start();
include 'dbconn.php'; 

// Check if the user is logged in
if (!isset($_SESSION['department'])) {
    header("Location: index.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id']; // Ensure this matches the input field in home.php

    // Check if the record exists before terminating
    $query_check = "SELECT * FROM form WHERE id = '$id'";
    $result_check = mysqli_query($connection, $query_check);

    if (mysqli_num_rows($result_check) > 0) {
        // Insert terminated record into the terminate table
        $query_insert = "INSERT INTO terminate (id, department, category, pic, service, company, start, endDate, rent, remarks, termination_date)
                         SELECT id, department, category, pic, service, company, start, endDate, rent, remarks, NOW() 
                         FROM form WHERE id = '$id'";

        if (mysqli_query($connection, $query_insert)) {
            // Optional: Delete from form table after termination
            $query_delete = "DELETE FROM form WHERE id = '$id'";
            mysqli_query($connection, $query_delete);

            echo "Record successfully terminated.";
        } else {
            echo "Error terminating record: " . mysqli_error($connection);
        }
    } else {
        echo "Record not found.";
    }
} else {
    echo "Invalid request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terminated Records</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="shortcut icon" type="x-icon" href="hsptl.png">
    <link rel="stylesheet" href="style.css">
    <script>
        $(document).ready(function () {
            var table = $('#example').DataTable({
                columnDefs: [
                    { targets: 0, visible: false } // Hide the ID column
                ]
            });

            // Filter table based on department selection
            $('#departmentFilter').on('change', function () {
                var selectedDepartment = $(this).val();
                table.column(1).search(selectedDepartment).draw();
            });

            // Toggle sidebar
            $('.toggle-btn').click(function () {
                $('.sidebar').toggleClass('active');
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const table = document.querySelector(".center-table tbody");
            const rows = table.querySelectorAll("tr");

            // Assuming the table is rectangular (each row has the same number of cells)
            const columnCount = rows[0].cells.length;

            for (let colIndex = 0; colIndex < columnCount; colIndex++) {
                for (let rowIndex = 0; rowIndex < rows.length; rowIndex++) {
                    const cell = rows[rowIndex].cells[colIndex]; // Get the cell at the specific column index

                    if (colIndex === 9) { // Adjust this index if the target column position changes
                        const monthsLeft = parseInt(cell.textContent.trim(), 10); // Ensure trimming whitespace

                        if (!isNaN(monthsLeft)) { // Check if the value is a valid number
                            if (monthsLeft < 3 && monthsLeft >= 0) {
                                cell.classList.add("lower3months");
                            } else if (monthsLeft >= 3) {
                                cell.classList.add("higher3months");
                            } else {
                                cell.classList.add("over3months");
                            }
                        } else {
                            console.warn("Invalid monthsLeft value in cell:", cell.textContent);
                        }
                    }
                }
            }
        });

    </script>
</head>
<body>
</div>   
    <nav class="navbar">
    <div class="navdiv">
        <h2>Terminated Records</h2>
        <ul class="breadcrumb">
            <li><a href="#">Records</a></li>
            <li><i class='bx bx-chevron-right' style="color: black;"></i></li>
            <li><a class="active" href="home.php">Dashboard</a></li>
        </ul>
    </div>
</nav>
    <div class="wrapper">
    <div class="sidebar">
        <div class="logo-menu">
            <h2 class="menu" style="color: white;">Menu</h2>
            <i class='bx bx-menu toggle-btn'></i>
        </div>
        <ul class="list">
            <li class="list-item">
                <a href="home.php">
                    <i class='bx bx-home'></i>
                    <span class="link-name" style="--i:1;">Dashboard</span>
                </a>
            </li>
            <li class="list-item">
                <a href="dashboard.php">
                    <i class='bx bx-file'></i>
                    <span class="link-name" style="--i:2;">Others</span>
                </a>
            </li>
            <li class="list-item">
                <a href="department.php">
                    <i class='bx bx-buildings'></i>
                    <span class="link-name" style="--i:3;">Department</span>
                </a>
            </li>
            <li class="list-item active">
                <a href="terminate.php">
                    <i class='bx bx-folder-minus'></i>
                    <span class="link-name" style="--i:4;">Terminate</span>
                </a>
            </li>
            <li class="list-item">
                <a href="logout.php">
                    <i class='bx bx-log-out'></i>
                    <span class="link-name" style="--i:5;">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div>
        <div class="filter-container">
            <label for="departmentFilter" style="color: black;">Department:</label>
            <select id="departmentFilter">
                <option value="">All</option>
                <?php foreach ($dpt as $department): ?>
                    <option value="<?php echo htmlspecialchars($department); ?>"><?php echo htmlspecialchars($department); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="center-table">
            <table id="example" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department</th>
                        <th>Category</th>
                        <th>PIC</th>
                        <th>Service</th>
                        <th>Company/Act</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Rental</th>
                        <th>Remarks</th>
                        <th>Months Left Before End</th>
                        <th>Action</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_terminated = "SELECT id, department, category, pic, service, company, start, endDate, rent, remarks, monthsLeft, filename FROM terminate ORDER BY termination_date DESC";

                    $result_terminated = mysqli_query($connection, $query_terminated);
                    
                    // Debugging - Check if the query fails
                    if (!$result_terminated) {
                        die("Query failed: " . mysqli_error($connection));
                    }
                    
                    // Check if there are results
                    if (mysqli_num_rows($result_terminated) > 0) {
                        while ($row = mysqli_fetch_assoc($result_terminated)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["department"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["category"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["pic"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["service"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["company"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["start"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["endDate"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["rent"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["remarks"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["monthsLeft"]) . "</td>";
                            echo "<td>
                            <form action='view3.php' method='get' style='display:inline;'>
                                <input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>
                                <button type='submit' class='btn'>View</button>
                            </form>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12'>No terminated records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</body>
</html>
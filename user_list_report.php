<?php
require_once('./config.php');
require_once('classes/DBConnection.php');

// ==========================
// SEARCH
// ==========================
$search = isset($_GET['search']) ? $_GET['search'] : '';

// ==========================
// FETCH CUSTOMERS
// ==========================
$qry = $conn->query("
    SELECT * FROM customer_list
    WHERE 
        firstname LIKE '%$search%' OR
        lastname LIKE '%$search%' OR
        email LIKE '%$search%' OR
        contact LIKE '%$search%'
    ORDER BY date_created DESC
");

$customers = [];
while ($row = $qry->fetch_assoc()) {
    $customers[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        .table thead { background:#0d6efd; color:white; }

        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>👥 Customer List Report</h2>

        <!-- PRINT BUTTON -->
        <button onclick="window.print()" class="btn btn-success no-print">
            🖨 Print Report
        </button>
    </div>

    <!-- SEARCH BAR -->
    <form method="GET" class="row g-2 mb-4 no-print">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by name, email, contact..." 
                   value="<?php echo $search; ?>">
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <!-- CUSTOMER TABLE -->
    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Date Created</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                if (empty($customers)) {
                    echo "<tr><td colspan='7' class='text-center py-3'>No Customers Found</td></tr>";
                }

                foreach ($customers as $row):
                    $fullname = $row['firstname'] . ' ' . 
                                (!empty($row['middlename']) ? $row['middlename'].' ' : '') . 
                                $row['lastname'];

                    $avatar = (!empty($row['avatar'])) ? $row['avatar'] : 'assets/img/default-avatar.png';
                ?>

                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $fullname; ?></td>
                    <td><?php echo $row['gender']; ?></td>
                    <td><?php echo $row['contact']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['date_created']; ?></td>
                </tr>

                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>

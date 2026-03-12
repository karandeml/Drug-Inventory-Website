<?php
require_once('./config.php');
require_once('classes/DBConnection.php');

// ==========================
// SEARCH
// ==========================
$search = isset($_GET['search']) ? $_GET['search'] : '';

// ==========================
// FETCH PRODUCTS
// ==========================
$qry = $conn->query("
    SELECT * 
    FROM product_list
    WHERE 
        name LIKE '%$search%' OR
        brand LIKE '%$search%' OR
        description LIKE '%$search%'
    ORDER BY date_created DESC
");

$products = [];
while ($row = $qry->fetch_assoc()) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        .inactive { background:#ffcccc; }
        .table thead { background:#0d6efd; color:white; }

        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">

    <!-- HEADER + PRINT BUTTON -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>💊 Product Report</h2>
        <button onclick="window.print()" class="btn btn-success no-print">
            🖨 Print Report
        </button>
    </div>

    <!-- SEARCH -->
    <form method="GET" class="row g-2 mb-4 no-print">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by name, brand, description..." 
                   value="<?php echo $search; ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <!-- PRODUCT TABLE -->
    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Brand</th>
                        <th>Name</th>
                        <th>Dose</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date Created</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                if (empty($products)) {
                    echo "<tr><td colspan='8' class='text-center py-3'>No Products Found</td></tr>";
                }

                foreach ($products as $row):

                    $status_text = ($row['status'] == 1) ? "ACTIVE" : "INACTIVE";
                    $row_class = ($row['status'] == 0 || $row['delete_flag'] == 1) ? "inactive" : "";

                ?>

                <tr class="<?php echo $row_class; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['brand']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['dose']; ?></td>
                    <td><?php echo number_format($row['price'],2); ?></td>
                    <td><b><?php echo $status_text; ?></b></td>
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

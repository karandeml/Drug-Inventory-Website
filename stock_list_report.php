<?php
require_once('./config.php');
require_once('classes/DBConnection.php');

// =========================
// FILTERS & SEARCH
// =========================
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// =========================
// FETCH STOCK LIST WITH PRODUCT NAME
// =========================
$qry = $conn->query("
    SELECT 
        s.*, 
        p.name AS product_name 
    FROM stock_list s 
    INNER JOIN product_list p ON p.id = s.product_id
    WHERE 
        (p.name LIKE '%$search%' OR s.code LIKE '%$search%')
");

$stocks = [];
while ($row = $qry->fetch_assoc()) {
    $stocks[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stock Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        .expired { background:#ffcccc; }
        .near-expiry { background:#fff3cd; }
        .table thead { background:#0d6efd; color:white; }

        /* Hide elements during print */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
            }
            .table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📦 Stock Report</h2>

        <!-- PRINT BUTTON -->
        <button onclick="window.print()" class="btn btn-success no-print">
            🖨 Print Report
        </button>
    </div>

    <!-- =========================
         FILTERS + SEARCH 
    ==========================-->
    <form method="GET" class="row g-2 mb-4 no-print">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search product or code..." value="<?php echo $search; ?>">
        </div>

        <div class="col-md-3">
            <select name="filter" class="form-select">
                <option value="all" <?= ($filter=='all')?'selected':'' ?>>All</option>
                <option value="active" <?= ($filter=='active')?'selected':'' ?>>Active Stock</option>
                <option value="expired" <?= ($filter=='expired')?'selected':'' ?>>Expired Only</option>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- =========================
         STOCK TABLE
    ==========================-->
    <div class="card shadow">
        <div class="card-body p-0" id="printArea">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Batch Code</th>
                        <th>Quantity</th>
                        <th>Expiration</th>
                        <th>Status</th>
                        <th>Date Created</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                if (empty($stocks)) {
                    echo "<tr><td colspan='7' class='text-center py-3'>No Stock Found</td></tr>";
                }

                foreach ($stocks as $row):
                    
                    $today = date("Y-m-d");
                    $exp = $row['expiration'];

                    // Status checks
                    $status = "ACTIVE";
                    $row_class = "";

                    if (!is_null($exp)) {
                        if ($exp < $today) {
                            $status = "EXPIRED";
                            $row_class = "expired";
                        } elseif ($exp >= $today && $exp <= date("Y-m-d", strtotime("+30 days"))) {
                            $status = "NEAR EXPIRY";
                            $row_class = "near-expiry";
                        }
                    }

                    // Apply filter
                    if ($filter == "expired" && $status != "EXPIRED") continue;
                    if ($filter == "active" && $status == "EXPIRED") continue;
                ?>

                <tr class="<?php echo $row_class; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['product_name']; ?></td>
                    <td><?php echo $row['code']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['expiration'] ?: '—'; ?></td>
                    <td><b><?php echo $status; ?></b></td>
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

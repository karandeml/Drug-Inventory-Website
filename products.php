<?php
// ================================
//  REQUIRE DB CONNECTION
// ================================
require_once('./config.php');
require_once('classes/DBConnection.php');
?>

<?php
// ================================
//  FETCH ALL PRODUCTS
// ================================
$qry = $conn->query("
    SELECT 
        p.*, 
        c.name AS category,
        (
            COALESCE(
                (SELECT SUM(quantity) FROM stock_list 
                 WHERE product_id = p.id 
                 AND (expiration IS NULL 
                 OR DATE(expiration) > '".date("Y-m-d")."')
                ), 0
            )
            -
            COALESCE(
                (SELECT SUM(quantity) FROM order_items 
                 WHERE product_id = p.id
                ), 0
            )
        ) AS available
    FROM product_list p
    INNER JOIN category_list c ON p.category_id = c.id
    WHERE p.delete_flag = 0
    ORDER BY p.id DESC
");
?>

<style>
/* ================================
   GRID FIX
================================ */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 22px;
}

/* ================================
   PRODUCT CARD
================================ */
.product-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e3e3e3;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: .25s ease-in-out;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0px 8px 18px rgba(0,0,0,0.12);
}

/* ================================
   PRODUCT IMAGE FIXED
================================ */
.product-img-holder {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #f7f7f7;
    display: flex;
    justify-content: center;
    align-items: center;
}

.product-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ================================
   PRODUCT BODY
================================ */
.card-body {
    padding: 14px 16px;
}

.card-body h5 {
    font-size: 1.1rem;
    font-weight: bold;
    margin-bottom: 6px;
}

.card-body p {
    font-size: 0.9rem;
    margin-bottom: 4px;
}

/* ================================
   FOOTER BUTTONS
================================ */
.card-footer {
    padding: 12px;
    background: #fff;
    border-top: 1px solid #ddd;
}

.card-footer .btn {
    width: 100%;
}
</style>


<section class="py-4">
    <div class="container">
        <h2 class="mb-4"><b>All Products</b></h2>

        <div class="product-grid">

        <?php if($qry->num_rows > 0): ?>
            <?php while($row = $qry->fetch_assoc()): ?>

                <div class="product-card">

                    <!-- Image -->
                    <div class="product-img-holder">
                        <img src="<?= validate_image($row['image_path']) ?>"
                             class="product-img"
                             alt="<?= $row['name'] ?>">
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <h6 class="text-muted mb-1"><?= $row['brand'] ?></h6>
                        <h5><?= $row['name'] ?></h5>

                        <p><b>Category:</b> <?= $row['category'] ?></p>
                        <p><b>Dose:</b> <?= $row['dose'] ?></p>
                        <p><b>Price:</b> ₹<?= number_format($row['price'], 2) ?></p>

                        <p>
                            <b>Stock:</b>
                            <?php if($row['available'] > 0): ?>
                                <span class="text-success"><?= $row['available'] ?></span>
                            <?php else: ?>
                                <span class="text-danger">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer text-center">
                        <a href="./?p=view_product&id=<?= $row['id'] ?>"
                           class="btn btn-primary btn-sm mb-1">
                            View Details
                        </a>

                        <?php if($_settings->userdata('id') && $_settings->userdata('login_type') == 2): ?>
                            <?php if($row['available'] > 0): ?>
                                <button class="btn btn-success btn-sm addToCartBtn"
                                        data-id="<?= $row['id'] ?>">
                                    Add to Cart
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="./login.php" class="btn btn-success btn-sm">Add to Cart</a>
                        <?php endif; ?>
                    </div>

                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <h4>No products found.</h4>
            </div>
        <?php endif; ?>

        </div>
    </div>
</section>


<!-- Add to cart script -->
<script>
$(document).on('click', '.addToCartBtn', function(){
    let id = $(this).data('id');
    _conf("Add this product to your cart?", "add_to_cart", [id]);
});

function add_to_cart(prod_id){
    start_loader();
    $.ajax({
        url: _base_url_ + "classes/Master.php?f=add_to_card",
        method: "POST",
        data: {product_id: prod_id},
        dataType: "json",
        success: function(resp){
            if(resp.status == "success"){
                location.reload();
            } else {
                alert_toast(resp.msg || "Error", "error");
            }
            end_loader();
        },
        error: function(){
            alert_toast("Connection Error", "error");
            end_loader();
        }
    });
}
</script>

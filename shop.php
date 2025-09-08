<?php include 'header.php'?>
<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">

        <?php
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Trang hiện tại
        $limit = 8; // Số sản phẩm mỗi trang
        $offset = ($page - 1) * $limit;

        $keyword = isset($_GET['keyword']) ? "WHERE sp.TenSP LIKE '%" . $_GET['keyword'] . "%'" : '';

        // Lấy sản phẩm
        $sql = "SELECT sp.*, k.SLTon 
                FROM SanPham sp 
                LEFT JOIN kho k ON sp.MaSP = k.MaSP
                $keyword 
                ORDER BY sp.UpdatedAt
                LIMIT $limit OFFSET $offset";
        $SanPham = Database::GetData($sql);

        // Tổng sản phẩm để tính tổng số trang
        $countSql = "SELECT COUNT(*) as total FROM SanPham sp $keyword";
        $totalResult = Database::GetData($countSql, ['row' => 0]);
        $totalProducts = $totalResult['total'];
        $totalPages = ceil($totalProducts / $limit);
        ?>

        <div class="row">
            <?php foreach ($SanPham as $sp): ?>
            <div class="col-md-3 col-sm-6" style="margin-bottom:20px;"> <!-- Khoảng cách giữa các hàng -->
                <div class="single-shop-product">
                    <div class="product-upper" style="width:100%; height:320px; overflow:hidden; text-align:center;">
                        <img src="<?=$sp['HinhAnh']?>" style="width:100%; height:100%; object-fit:fill;"> <!-- ảnh cố định khung -->
                    </div>
                    <h2><a href="<?='/Salonoto/details.php?id=' . $sp['MaSP']?>"><?=$sp['TenSP']?></a></h2>
                    <div class="product-carousel-price">
                        <ins><?=Helper::Currency($sp['Gia'])?></ins>
                    </div>

                    <div class="product-option-shop">
                        <?php if (isset($_SESSION['MaQuyen']) && $_SESSION['MaQuyen'] == 3) { ?>
                            <a class="add_to_cart_button" href="#" 
                               onclick="return addToCart('<?=$sp['MaSP']?>', '<?=isset($sp['SLTon']) ? $sp['SLTon'] : 0?>')">
                               <i class="fas fa-cart-plus"></i>
                            </a>
                        <?php } ?>
                        <a class="add_to_cart_button" href="<?='/Salonoto/details.php?id=' . $sp['MaSP']?>">Chi tiết</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Phân trang -->
        <div style="text-align:center; margin-top:20px;">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?php 
                    echo isset($_GET['keyword']) ? "keyword=".$_GET['keyword']."&" : "";
                    echo "page=$i"; 
                ?>" 
                   style="margin:0 5px; padding:5px 10px; background:#0077cc; color:#fff; text-decoration:none; border-radius:3px;">
                   <?=$i?>
                </a>
            <?php endfor; ?>
        </div>

    </div>
</div>

<script>
function addToCart(maSP, slTon) {
    if (!slTon || slTon <= 0) {
        alert("Sản phẩm này đã hết hàng!");
        return false;
    } else {
        alert("Thêm vào giỏ hàng thành công!");
        window.location.href = "/Salonoto/cart.php?id=" + maSP;
        return false;
    }
}
</script>

<?php include 'footer.php'?>

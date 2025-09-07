<?php include 'header.php'?>
<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <?php
                $keyword = isset($_GET['keyword']) ? "WHERE sp.TenSP LIKE '%" . $_GET['keyword'] . "%'" : '';
                $sql = "SELECT sp.*, k.SLTon 
                        FROM SanPham sp 
                        LEFT JOIN kho k ON sp.MaSP = k.MaSP
                        $keyword 
                        ORDER BY sp.UpdatedAt";
                $SanPham = Database::GetData($sql);
                foreach ($SanPham as $sp) {
            ?>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="<?=$sp['HinhAnh']?>" style="height: 320px">
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
            <?php } ?>
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

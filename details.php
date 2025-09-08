<?php include 'header.php'?>
<?php
    $maSP = isset($_GET['id']) ? $_GET['id'] : '';

    // Lấy thông tin sản phẩm kèm loại và tồn kho
    $sql = "SELECT sp.*, lsp.TenLoaiSP, k.SLTon 
            FROM SanPham sp 
            LEFT JOIN LoaiSP lsp ON sp.MaLoaiSP = lsp.MaLoaiSP 
            LEFT JOIN kho k ON sp.MaSP = k.MaSP
            WHERE sp.MaSP = '$maSP'";
    $sanPham = Database::GetData($sql, ['row' => 0]);

    // Lấy sản phẩm liên quan (cùng loại, loại trừ chính nó)
    $relatedSql = "SELECT sp.*, k.SLTon 
                   FROM SanPham sp
                   LEFT JOIN kho k ON sp.MaSP = k.MaSP
                   WHERE sp.MaLoaiSP = '{$sanPham['MaLoaiSP']}' 
                     AND sp.MaSP != '{$sanPham['MaSP']}'
                   LIMIT 4"; // lấy 4 sản phẩm
    $relatedProducts = Database::GetData($relatedSql);
?>
<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div style="display: flex;">
            <div style="width: 860px; padding: 0 16px;">
                <img style="height: 320px" src="<?=$sanPham['HinhAnh']?>" alt="<?=$sanPham['TenSP']?>">
            </div>
            <div style="padding: 0 16px;">
                    <h2 class="section-title" style="text-align:left; font-weight:bold; color:#0077cc; font-size:50px;">
                        Thông tin sản phẩm
                    </h2>
                <p><b>Tên sản phẩm: </b><?=$sanPham['TenSP']?></p>
                <p><b>Giá: </b><?=number_format($sanPham['Gia'])?> ₫</p>
                <p><b>Loại sản phẩm: </b>
                    <?php if(isset($sanPham['TenLoaiSP'])): ?>
                        <a href="<?='/Salonoto/category-product.php?MaLoaiSP=' . $sanPham['MaLoaiSP']?>"><?=$sanPham['TenLoaiSP']?></a>
                    <?php else: ?>
                        Chưa phân loại
                    <?php endif; ?>
                </p>
                <p><b>Số lượng trong kho: </b>
                    <?php
                        if(isset($sanPham['SLTon'])) {
                            if($sanPham['SLTon'] > 0){
                                echo '<strong>'.$sanPham['SLTon'].'</strong>';
                            } else {
                                echo '<span style="color:red;font-weight:bold;">Hết hàng</span>';
                            }
                        } else {
                            echo 'Chưa có thông tin';
                        }
                    ?>
                </p>
                
                <!-- Thêm hiển thị thời gian bảo hành -->
                <p><b>Thời gian bảo hành: </b>
                    <?php
                        if(isset($sanPham['ThoiGianBaoHanh']) && $sanPham['ThoiGianBaoHanh'] > 0){
                            echo $sanPham['ThoiGianBaoHanh'] . " năm";
                        } else {
                            echo "Chưa có thông tin";
                        }
                    ?>
                </p>

                <p><b>Thông số sản phẩm: </b></p>
                    <ul>
                        <?php 
                            $lines = explode("\n", $sanPham['ThongSoSanPham']); 
                            foreach ($lines as $line) {
                                $line = trim($line, "- \t\n\r\0\x0B");
                                if (!empty($line)) {
                                    echo "<li>" . htmlspecialchars($line) . "</li>";
                                }
                            }
                        ?>
                    </ul>

                <?php if(isset($sanPham['SLTon']) && $sanPham['SLTon'] > 0): ?>
                    <div class="product-option-shop">
                        <?php if (isset($_SESSION['MaQuyen']) && $_SESSION['MaQuyen'] == 3): ?>
                            <form method="POST" action="cart.php" style="display:inline-block;">
                                <input type="hidden" name="MaSP" value="<?=$sanPham['MaSP']?>">
                                <label>Số lượng: </label>
                                <input type="number" name="SL" value="1" min="1" max="<?=$sanPham['SLTon']?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sản phẩm liên quan -->
<div class="maincontent-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="latest-product">
                    <h2 class="section-title" style="text-align:left; font-weight:bold; color:#0077cc; font-size:30px;">
                        Sản phẩm liên quan
                    </h2>
                    <div class="product-carousel">
                        <?php
                            $relatedSql = "SELECT sp.*, k.SLTon 
                                           FROM SanPham sp
                                           LEFT JOIN kho k ON sp.MaSP = k.MaSP
                                           WHERE sp.MaLoaiSP = '{$sanPham['MaLoaiSP']}'
                                             AND sp.MaSP != '{$sanPham['MaSP']}'
                                           ORDER BY UpdatedAt DESC
                                           LIMIT 8";
                            $relatedProducts = Database::GetData($relatedSql);
                            foreach ($relatedProducts as $sp) {
                        ?>
                        <div class="single-product">
                            <div class="product-f-image">
                                <img src="<?=$sp['HinhAnh']?>" alt="<?=$sp['TenSP']?>">
                                <div class="product-hover">
                                    <?php 
                                        if (isset($_SESSION['MaQuyen']) && $_SESSION['MaQuyen'] == 3) { 
                                            // Lấy tồn kho hiện tại của sản phẩm
                                            $sqlStock = "SELECT SLTon FROM Kho WHERE MaSP = '" . $sp['MaSP'] . "'";
                                            $SLTon = Database::GetData($sqlStock, ['cell' => 'SLTon']);

                                            // Chỉ hiện nút Thêm vào giỏ nếu còn tồn kho > 0
                                            if ($SLTon && $SLTon > 0) { 
                                    ?>
                                                <a href="/Salonoto/cart.php?id=<?= $sp['MaSP'] ?>" class="add-to-cart-link">
                                                    <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                                                </a>
                                    <?php 
                                            } 
                                        } 
                                    ?>
                                    <a href="<?='/Salonoto/details.php?id=' . $sp['MaSP']?>" class="view-details-link">
                                        <i class="fa fa-link"></i> Chi tiết
                                    </a>
                                </div>
                            </div>
                            <h2>
                                <a href="<?='/Salonoto/details.php?id=' . $sp['MaSP']?>"><?=$sp['TenSP']?></a>
                            </h2>
                            <div class="product-carousel-price">
                                <ins><?=Helper::Currency($sp['Gia'])?></ins>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
<?php include 'footer.php'?>

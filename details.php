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

// Lấy sản phẩm liên quan
$relatedSql = "SELECT sp.*, k.SLTon 
               FROM SanPham sp
               LEFT JOIN kho k ON sp.MaSP = k.MaSP
               WHERE sp.MaLoaiSP = '{$sanPham['MaLoaiSP']}' 
                 AND sp.MaSP != '{$sanPham['MaSP']}'
               LIMIT 4";
$relatedProducts = Database::GetData($relatedSql);
?>
<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <!-- 2 cột cố định -->
        <div style="width:100%; display:flex; max-width:1200px; margin:0 auto;">
            
            <!-- Cột ảnh -->
            <div style="width:500px; flex-shrink:0; padding-right:16px;">
                <?php if(!empty($sanPham['HinhAnh'])): ?>
                    <img src="<?=$sanPham['HinhAnh']?>" alt="<?=$sanPham['TenSP']?>" 
                         style="width:100%; height:100%; max-height:600px; object-fit:contain; display:block;">
                <?php else: ?>
                    <div style="width:100%; height:320px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; color:#999;">
                        Chưa có ảnh
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cột thông tin -->
            <div style="width:500px; flex-shrink:0; padding-left:16px;">
                <h2 class="section-title" style="text-align:left; font-weight:bold; color:#0077cc; font-size:50px;">
                    Thông tin sản phẩm
                </h2>
                <p><b>Tên sản phẩm: </b><?=$sanPham['TenSP']?></p>
                <p><b>Giá: </b><?=number_format($sanPham['Gia'])?> ₫</p>
                <p><b>Loại sản phẩm: </b>
                    <?php if(isset($sanPham['TenLoaiSP'])): ?>
                        <a href="<?='/Salonoto/category-product.php?MaLoaiSP=' . $sanPham['MaLoaiSP']?>"><?=$sanPham['TenLoaiSP']?></a>
                    <?php else: ?>Chưa phân loại<?php endif; ?>
                </p>
                <p><b>Số lượng trong kho: </b>
                    <?php
                        if(isset($sanPham['SLTon'])){
                            echo $sanPham['SLTon'] > 0 ? '<strong>'.$sanPham['SLTon'].'</strong>' : '<span style="color:red;font-weight:bold;">Hết hàng</span>';
                        } else { echo 'Chưa có thông tin'; }
                    ?>
                </p>
                <p><b>Thời gian bảo hành: </b>
                    <?= isset($sanPham['ThoiGianBaoHanh']) && $sanPham['ThoiGianBaoHanh']>0 ? $sanPham['ThoiGianBaoHanh'].' năm' : 'Chưa có thông tin'; ?>
                </p>
                <p><b>Thông số sản phẩm: </b></p>
                <ul>
                    <?php 
                    $lines = explode("\n", $sanPham['ThongSoSanPham']);
                    foreach($lines as $line){
                        $line = trim($line, "- \t\n\r\0\x0B");
                        if(!empty($line)) echo "<li>".htmlspecialchars($line)."</li>";
                    }
                    ?>
                </ul>

                <?php if(isset($sanPham['SLTon']) && $sanPham['SLTon'] > 0 && isset($_SESSION['MaQuyen']) && $_SESSION['MaQuyen']==3): ?>
                    <div class="product-option-shop">
                        <form method="POST" action="cart.php" style="display:inline-block;">
                            <input type="hidden" name="MaSP" value="<?=$sanPham['MaSP']?>">
                            <label>Số lượng: </label>
                            <input type="number" name="SL" value="1" min="1" max="<?=$sanPham['SLTon']?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
                                    <?php if(isset($_SESSION['MaQuyen']) && $_SESSION['MaQuyen']==3){ ?>
                                        <a href="<?='/Salonoto/cart.php?id=' . $sp['MaSP']?>" class="add-to-cart-link">
                                            <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                                        </a>
                                    <?php } ?>
                                    <a href="<?='/Salonoto/details.php?id=' . $sp['MaSP']?>" class="view-details-link">
                                        <i class="fa fa-link"></i> Chi tiết
                                    </a>
                                </div>
                            </div>
                            <h2><a href="<?='/Salonoto/details.php?id=' . $sp['MaSP']?>"><?=$sp['TenSP']?></a></h2>
                            <div class="product-carousel-price"><ins><?=Helper::Currency($sp['Gia'])?></ins></div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'?>

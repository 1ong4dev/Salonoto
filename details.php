<?php 
include 'header.php';
$maSP = isset($_GET['id']) ? $_GET['id'] : '';

// Lấy thông tin sản phẩm kèm loại và tồn kho
$sql = "SELECT sp.*, lsp.TenLoaiSP, k.SLTon 
        FROM SanPham sp 
        LEFT JOIN LoaiSP lsp ON sp.MaLoaiSP = lsp.MaLoaiSP 
        LEFT JOIN kho k ON sp.MaSP = k.MaSP
        WHERE sp.MaSP = '$maSP'";
$sanPham = Database::GetData($sql, ['row' => 0]);
?>
<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div style="display: flex;">
            <div style="width: 860px; padding: 0 16px;">
                <img style="height: 320px" src="<?=$sanPham['HinhAnh']?>" alt="<?=$sanPham['TenSP']?>">
            </div>
            <div style="padding: 0 16px;">
                <h3>Thông tin sản phẩm</h3>
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
                <p><b>Thông số sản phẩm: </b></p>
                    <ul>
                        <?php 
                            $lines = explode("\n", $sanPham['ThongSoSanPham']); 
                            foreach ($lines as $line) {
                                $line = trim($line, "- \t\n\r\0\x0B"); // bỏ dấu - đầu dòng
                                if (!empty($line)) {
                                    echo "<li>" . htmlspecialchars($line) . "</li>";
                                }
                            }
                        ?>
                    </ul>

                <?php if(isset($sanPham['SLTon']) && $sanPham['SLTon'] > 0): ?>
                    
                    <?php if(isset($_SESSION['user_id'])): // Đã đăng nhập ?>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="MaSP" value="<?=$sanPham['MaSP']?>">
                            <label>Số lượng: </label>
                            <input type="number" name="SL" value="1" min="1" max="<?=$sanPham['SLTon']?>">
                            <button type="submit" class="btn btn-primary">Thêm vào giỏ hàng</button>
                        </form>
                    <?php else: // Chưa đăng nhập ?>
                        <form onsubmit="return checkLogin()">
                            <label>Số lượng: </label>
                            <input type="number" value="1" min="1" max="<?=$sanPham['SLTon']?>">
                            <button type="submit" class="btn btn-primary">Thêm vào giỏ hàng</button>
                        </form>
                        <script>
                            function checkLogin() {
                                alert("Bạn cần đăng nhập để mua hàng!");
                                window.location.href = "sign.php";
                                return false;
                            }
                        </script>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'?>

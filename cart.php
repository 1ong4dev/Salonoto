<?php include 'header.php' ?>

<?php
if (!isset($_SESSION)) session_start();

// Kiểm tra người dùng đã đăng nhập và quyền
if (!isset($_SESSION['TenTaiKhoan']) || $_SESSION['MaQuyen'] != 3) {
    echo "<script>alert('Bạn cần đăng nhập với quyền khách hàng!'); window.location='sign.php';</script>";
    exit;
}

$username = $_SESSION['TenTaiKhoan'];

// Thêm sản phẩm vào giỏ hàng
if ((isset($_GET['id']) || isset($_POST['MaSP']))) {
    $maSP = isset($_GET['id']) ? $_GET['id'] : $_POST['MaSP'];
    $SL = isset($_POST['SL']) ? intval($_POST['SL']) : 1;

    // Lấy số lượng hiện tại trong SanPham
    $sql = "SELECT SL FROM SanPham WHERE MaSP = '$maSP'";
    $SLSanPham = Database::GetData($sql, ['cell' => 'SL']);

    if ($SLSanPham <= 0) {
        echo "<script>alert('Sản phẩm này đã hết hàng!'); window.location='cart.php';</script>";
        exit;
    }

    // Lấy số lượng hiện tại trong giỏ hàng
    $sql = "SELECT SL FROM GioHang WHERE MaSP = '$maSP' AND TenTaiKhoan = '$username'";
    $currentSL = Database::GetData($sql, ['cell' => 'SL']);

    if ($currentSL) {
        $newSL = min($currentSL + $SL, $SLSanPham);
        $sql = "UPDATE GioHang SET SL = $newSL, UpdatedAt = NOW(3) WHERE MaSP = '$maSP' AND TenTaiKhoan = '$username'";
    } else {
        $SL = min($SL, $SLSanPham);
        $sql = "INSERT INTO GioHang (MaSP, TenTaiKhoan, SL, UpdatedAt) 
                VALUES ('$maSP', '$username', $SL, NOW(3))";
    }
    Database::NonQuery($sql);
}

// Cập nhật số lượng sản phẩm trong giỏ hàng
if (isset($_POST['update_amount'])) {
    $MaSP = $_POST['MaSP'];
    $SL = intval($_POST['SL']);

    $sql = "SELECT SL FROM SanPham WHERE MaSP = '$MaSP'";
    $SLSanPham = Database::GetData($sql, ['cell' => 'SL']);
    $SL = min($SL, $SLSanPham);

    $sql = "UPDATE GioHang SET SL = $SL, UpdatedAt = NOW(3) 
            WHERE MaSP = '$MaSP' AND TenTaiKhoan = '$username'";
    Database::NonQuery($sql);
}

// Xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['del-cart-id'])) {
    $MaSP = $_GET['del-cart-id'];
    $sql = "DELETE FROM GioHang WHERE MaSP = '$MaSP' AND TenTaiKhoan = '$username'";
    Database::NonQuery($sql);
}

// Lấy danh sách sản phẩm trong giỏ hàng
    $sql = "SELECT g.MaSP, s.TenSP, s.HinhAnh, 
                IFNULL(s.GiaKhuyenMai, s.Gia) AS Gia,  
                s.SL AS SLSanPham, g.SL
            FROM GioHang g
            INNER JOIN SanPham s ON g.MaSP = s.MaSP
            WHERE g.TenTaiKhoan = '$username'
            ORDER BY g.UpdatedAt DESC";
    $carts = Database::GetData($sql);

// Tính tổng tiền
$totalMoney = 0;
if ($carts) {
    foreach ($carts as $cart) {
        $totalMoney += $cart['Gia'] * $cart['SL'];
    }
}
?>

<div class="product-big-title-area">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="product-bit-title text-center">
                    <h2>Giỏ hàng của bạn</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="product-content-right">
            <div class="woocommerce">
                <form method="post" action="#">
                    <table cellspacing="0" class="shop_table cart">
                        <thead>
                            <tr>
                                <th>Mã sản phẩm</th>
                                <th>Tên sản phẩm</th>
                                <th>Ảnh</th>
                                <th>Giá</th>
                                <th width="125">Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Xoá</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($carts): ?>
                                <?php foreach ($carts as $cart): ?>
                                    <tr class="cart_item">
                                        <td><?= $cart['MaSP'] ?></td>
                                        <td><?= $cart['TenSP'] ?></td>
                                        <td class="product-thumbnail">
                                            <img class="shop_thumbnail" src="<?= $cart['HinhAnh'] ?>" style="width:80px; height:auto;">
                                        </td>
                                        <td><?= Helper::Currency($cart['Gia']) ?></td>
                                        <td>
                                            <div class="quantity buttons_added">
                                                <input name="MaSP" value="<?= $cart['MaSP'] ?>" hidden>
                                                <input name="SL" type="number" size="4" class="input-text qty text" 
                                                       min="1" max="<?= $cart['SLSanPham'] ?>" 
                                                       value="<?= min($cart['SL'], $cart['SLSanPham']) ?>">
                                            </div>
                                        </td>
                                        <td><?= Helper::Currency($cart['Gia'] * $cart['SL']) ?></td>
                                        <td><a class="remove" href="?del-cart-id=<?= $cart['MaSP'] ?>">×</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="6" class="actions">
                                        <a href="order.php" class="btn btn-lg btn-success">Tạo đơn hàng</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-danger text-center">Giỏ hàng trống!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>

                <div class="cart-collaterals">
                    <div class="cart_totals">
                        <h2>Tổng tiền giỏ hàng</h2>
                        <table cellspacing="0">
                            <tbody>
                                <tr class="cart-subtotal">
                                    <th>Tổng đơn hàng:</th>
                                    <td><span class="amount"><?= number_format($totalMoney) ?> đ</span></td>
                                </tr>
                                <tr class="shipping">
                                    <th>Vận chuyển:</th>
                                    <td>Miễn phí vận chuyển</td>
                                </tr>
                                <tr class="order-total">
                                    <th>Tổng tiền:</th>
                                    <td><strong><span class="amount"><?= number_format($totalMoney) ?> đ</span></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.php' ?>

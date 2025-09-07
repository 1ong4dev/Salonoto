<?php include 'header.php'?>

<?php
if (!isset($_SESSION)) session_start();
if ( (isset($_GET['id']) || isset($_POST['MaSP'])) && isset($_SESSION['TenTaiKhoan']) && $_SESSION['MaQuyen'] == 3 ) {

    $maSP = isset($_GET['id']) ? $_GET['id'] : $_POST['MaSP'];
    $SL = isset($_POST['SL']) ? intval($_POST['SL']) : 1;
    $username = $_SESSION['TenTaiKhoan'];

    // Lấy tồn kho hiện tại từ bảng kho
    $sql = "SELECT SLTon FROM kho WHERE MaSP = '$maSP'";
    $SLTon = Database::GetData($sql, ['cell' => 'SLTon']);

    // Lấy số lượng hiện tại trong giỏ hàng
    $sql = "SELECT SL FROM GioHang WHERE MaSP = '$maSP' AND TenTaiKhoan = '$username'";
    $currentSL = Database::GetData($sql, ['cell' => 'SL']);

    if ($currentSL) {
        $newSL = min($currentSL + $SL, $SLTon); // không vượt quá tồn kho
        $sql = "UPDATE GioHang SET SL = $newSL, UpdatedAt = NOW(3) WHERE MaSP = '$maSP' AND TenTaiKhoan = '$username'";
    } else {
        $SL = min($SL, $SLTon); // đảm bảo không vượt quá tồn kho
        $sql = "INSERT INTO GioHang (MaSP, TenTaiKhoan, SL, UpdatedAt) 
                VALUES ('$maSP', '$username', $SL, NOW(3))";
    }

    Database::NonQuery($sql);
}

// Cập nhật số lượng sản phẩm
if (isset($_POST['update_amount'])) {
    $MaSP = $_POST['MaSP'];
    $SL = intval($_POST['SL']);

    // Lấy tồn kho từ bảng Kho
    $sql = "SELECT SLTon FROM Kho WHERE MaSP = '$MaSP'";
    $SLTon = Database::GetData($sql, ['cell' => 'SLTon']);

    $SL = min($SL, $SLTon); // Giới hạn số lượng <= tồn kho

    $sql = "UPDATE GioHang SET SL = $SL, UpdatedAt = NOW(3) 
            WHERE MaSP = '$MaSP' AND TenTaiKhoan = '" . $_SESSION['TenTaiKhoan'] . "'";
    Database::NonQuery($sql);
}

// Xóa sản phẩm trong giỏ hàng
if (isset($_GET['del-cart-id'])) {
    $MaSP = $_GET['del-cart-id'];

    $sql = "DELETE FROM GioHang WHERE MaSP = '$MaSP' AND TenTaiKhoan = '" . $_SESSION['TenTaiKhoan'] . "'";
    Database::NonQuery($sql);
}

// Lấy danh sách sản phẩm trong giỏ hàng để hiển thị
$carts = [];
if (isset($_SESSION['TenTaiKhoan'])) {
    $username = $_SESSION['TenTaiKhoan'];
    $sql = "SELECT g.MaSP, s.TenSP, s.HinhAnh, s.Gia, k.SLTon, g.SL
            FROM GioHang g
            INNER JOIN SanPham s ON g.MaSP = s.MaSP
            INNER JOIN Kho k ON g.MaSP = k.MaSP
            WHERE g.TenTaiKhoan = '$username'
            ORDER BY g.UpdatedAt DESC";
    $carts = Database::GetData($sql);
}

// Tính tổng tiền
$totalMoney = 0;
if ($carts) {
    foreach ($carts as $cart) {
        $totalMoney += $cart['Gia'] * $cart['SL'];
    }
}

// Thanh toán đơn hàng
if (isset($_GET['type']) && $_GET['type'] == 'payment' && $carts) {
    // Tạo mã đơn hàng
    $orderID = 'Salon' . rand(1000000, 9999999);

    // Thêm vào bảng orders
    $sql = "INSERT INTO orders (OrderID, TotalPrice, PaidPrice, Status, Note, CreatedAt, TenTaiKhoan) 
            VALUES ('$orderID', $totalMoney, $totalMoney, 0, NULL, NOW(3), '" . $_SESSION['TenTaiKhoan'] . "')";
    Database::NonQuery($sql);

    // Thêm chi tiết đơn hàng và trừ tồn kho
    foreach ($carts as $cart) {
        $sql = "INSERT INTO order_details (MaSP, OrderID, SL) 
                VALUES ('" . $cart['MaSP'] . "', '$orderID', " . $cart['SL'] . ")";
        Database::NonQuery($sql);

        // Giảm tồn kho
        $sql = "UPDATE Kho SET SLTon = SLTon - " . $cart['SL'] . " WHERE MaSP = '" . $cart['MaSP'] . "'";
        Database::NonQuery($sql);
    }

    // Xóa giỏ hàng
    $sql = "DELETE FROM GioHang WHERE TenTaiKhoan = '" . $_SESSION['TenTaiKhoan'] . "'";
    Database::NonQuery($sql);
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
                            <?php
                                $carts = [];
                                if (isset($_SESSION['TenTaiKhoan'])) {
                                    $username = $_SESSION['TenTaiKhoan'];
                                    $sql = "SELECT g.MaSP, s.TenSP, s.HinhAnh, s.Gia, k.SLTon, g.SL
                                            FROM GioHang g
                                            INNER JOIN SanPham s ON g.MaSP = s.MaSP
                                            INNER JOIN Kho k ON g.MaSP = k.MaSP
                                            WHERE g.TenTaiKhoan = '$username'
                                            ORDER BY g.UpdatedAt DESC";
                                    $carts = Database::GetData($sql);
                                }

                                if ($carts) {
                                    foreach ($carts as $cart) { ?>
                                        <tr class="cart_item">
                                            <td class="product-name"><?= $cart['MaSP'] ?></td>
                                            <td class="product-name"><?= $cart['TenSP'] ?></td>
                                            <td class="product-thumbnail"><img class="shop_thumbnail" src="<?= $cart['HinhAnh'] ?>"></td>
                                            <td class="product-name"><?= Helper::Currency($cart['Gia']) ?></td>
                                            <td class="product-quantity">
                                                <div class="quantity buttons_added">
                                                    <input name="MaSP" value="<?= $cart['MaSP'] ?>" hidden>
                                                    <input name="SL" type="number" size="4" class="input-text qty text" 
                                                        min="1" 
                                                        max="<?= $cart['SLTon'] ?>" 
                                                        value="<?= min($cart['SL'], $cart['SLTon']) ?>">
                                                </div>
                                            </td>
                                            </td>
                                            <td class="product-name"><?= Helper::Currency($cart['Gia'] * $cart['SL']) ?></td>
                                            <td class="product-remove"><a title="Xoá sản phẩm" class="remove" href="?del-cart-id=<?= $cart['MaSP'] ?>">×</a></td>
                                        </tr>
                                    <?php }
                                }
                            ?>
                            <tr>
                                <td class="actions" colspan="6">
                                    <?php
                                        if (isset($_SESSION['TenTaiKhoan']) && $carts && count($carts) > 0) {
                                            echo '<a href="pay.php" class="btn btn-lg btn-success">Tạo đơn hàng</a>';
                                        } else {
                                            echo '<span class="text-danger">Giỏ hàng trống, không thể tạo đơn hàng.</span>';
                                        }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>

                <div class="cart-collaterals">
                    <div class="cart_totals">
                        <h2>Tổng tiền giỏ hàng</h2>
                        <?php
                            $totalMoney = 0;
                            if (isset($_SESSION['TenTaiKhoan'])) {
                                $sql = "SELECT SUM(GioHang.SL * SanPham.Gia) AS TongTien
                                        FROM GioHang
                                        INNER JOIN SanPham ON GioHang.MaSP = SanPham.MaSP
                                        WHERE GioHang.TenTaiKhoan = '" . $_SESSION['TenTaiKhoan'] . "'";
                                $totalMoney = Database::GetData($sql, ['cell' => 'TongTien']);
                            }
                        ?>
                        <table cellspacing="0">
                            <tbody>
                                <tr class="cart-subtotal">
                                    <th>Tổng đơn hàng: </th>
                                    <td><span class="amount"><?= number_format($totalMoney) ?> đ</span></td>
                                </tr>
                                <tr class="shipping">
                                    <th>Vận chuyển: </th>
                                    <td>Miễn phí vận chuyển</td>
                                </tr>
                                <tr class="order-total">
                                    <th>Tổng tiền: </th>
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

<?php include 'footer.php'?>

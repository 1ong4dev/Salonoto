<?php
include 'config/config.php';
include 'config/database.php';
include 'config/Helper.php';

session_start();

// Kiểm tra khách đã đăng nhập chưa
if (!isset($_SESSION['TenTaiKhoan'])) {
    header("Location: login.php");
    exit;
}

$orderID = isset($_GET['order-id']) ? $_GET['order-id'] : '';

// Lấy thông tin đơn hàng + khách hàng
$sql = "SELECT d.*, u.TenDayDu AS Fullname, u.SDT AS Phone, u.Email, u.DiaChi
        FROM dondathang d
        JOIN users u ON d.TenTaiKhoan = u.TenTaiKhoan
        WHERE d.MaDonDatHang = '$orderID'
        AND d.TenTaiKhoan = '{$_SESSION['TenTaiKhoan']}'";
$orderUser = Database::GetData($sql, ['row' => 0]);

if (!$orderUser) {
    echo "<p class='text-center text-danger'>Đơn hàng không tồn tại hoặc không phải của bạn.</p>";
    exit;
}

// Lấy chi tiết sản phẩm kèm bảo hành
$sql = "SELECT ct.MaSP, sp.TenSP, sp.Gia, ct.SL, ct.NgayBatDauBH, ct.NgayKetThucBH
        FROM chitietdondathang ct
        JOIN sanpham sp ON ct.MaSP = sp.MaSP
        WHERE ct.MaDonDatHang = '$orderID'";
$items = Database::GetData($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .main-logo { width: 322px; height: 100px; }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="text-primary text-center pb-5">
        <h4><b>Cửa hàng bán phụ kiện Thế giới oto</b></h4>
        <img class="main-logo" src="<?='/Salonoto/assets/img/logo.png'?>" alt="Logo">
    </div>

    <h3 class="text-primary text-center pb-4"><b>HOÁ ĐƠN CỦA BẠN</b></h3>

    <!-- Thông tin khách hàng -->
    <div class="pb-4">
        <h5 class="text-primary"><b>THÔNG TIN KHÁCH HÀNG</b></h5>
        <p><b>Họ và tên:</b> <?=$orderUser['Fullname']?></p>
        <p><b>Số điện thoại:</b> <?=$orderUser['Phone']?></p>
        <p><b>Email:</b> <?=$orderUser['Email']?></p>
        <p><b>Địa chỉ:</b> <?=$orderUser['DiaChi']?></p>
        <p><b>Ngày đặt hàng:</b> <?= $orderUser['CreatedAt'] ? Helper::DateTime($orderUser['CreatedAt']) : '-' ?></p>
    </div>

    <!-- Chi tiết sản phẩm -->
    <div class="pb-5">
        <h5 class="text-primary"><b>CHI TIẾT ĐƠN HÀNG</b></h5>
        <table class="table table-hover table-bordered">
            <thead class="table-success">
                <tr>
                    <th>Mã Sản phẩm</th>
                    <th>Tên Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th>Ngày bắt đầu BH</th>
                    <th>Ngày kết thúc BH</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($items) {
                    foreach ($items as $item) {
                        echo '<tr>
                            <th>'.$item['MaSP'].'</th>
                            <td>'.$item['TenSP'].'</td>
                            <td>'.Helper::Currency($item['Gia']).'</td>
                            <td>'.$item['SL'].'</td>
                            <td>'.Helper::Currency($item['Gia'] * $item['SL']).'</td>
                            <td>'.($item['NgayBatDauBH'] ? Helper::DateTime($item['NgayBatDauBH'], true) : '-').'</td>
                            <td>'.($item['NgayKetThucBH'] ? Helper::DateTime($item['NgayKetThucBH'], true) : '-').'</td>
                        </tr>';
                    }
                } else {
                    echo '<tr><td colspan="100%" class="text-center">Không có dữ liệu</td></tr>';
                }
                ?>
            </tbody>
        </table>
           <!-- Mã giảm giá & Giảm giá -->
    <div class="mb-3 text-end">
        <p><b>Mã giảm giá:</b> <?= !empty($orderUser['MaGiamGia']) ? $orderUser['MaGiamGia'] : '-' ?></p>
        <p><b>Giảm giá:</b> <?= !empty($orderUser['GiamGia']) ? Helper::Currency($orderUser['GiamGia']) : '0 ₫' ?></p>
    </div>

        <div class="text-end mb-3">
            <p><b>Tổng tiền:</b> <?=Helper::Currency($orderUser['TongTien'])?></p>
            <p><b>Phí vận chuyển:</b> 0 ₫</p>
        </div>

        <!-- Logo thanh toán nếu đã thanh toán -->
            <div class="mb-3 text-end"> <!-- text-end để căn sang phải -->
                <img style="height: 150px;" src="<?='/Salonoto/assets/img/paid-logo.jpg'?>" alt="Đã thanh toán">
            </div>
    </div>

    <!-- Nút in hóa đơn -->
    <div class="text-center mb-5">
        <button onclick="window.print();" class="btn btn-primary">
            In hoá đơn
        </button>
    </div>
</div>
</body>
</html>

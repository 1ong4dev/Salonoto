<?php
include '../../config/config.php';
include '../../config/database.php';
include '../../config/Helper.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In hoá đơn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
    .main-logo { width: 322px; height: 100px; }
    </style>
</head>

<?php
$orderID = isset($_GET['order-id']) ? $_GET['order-id'] : '';

// Lấy thông tin đơn hàng + người mua
$sql = "SELECT d.*, u.TenDayDu AS Fullname, u.SDT AS Phone, u.Email 
        FROM dondathang d
        JOIN users u ON d.TenTaiKhoan = u.TenTaiKhoan
        WHERE d.MaDonDatHang = '$orderID'";
$orderUser = Database::GetData($sql, ['row' => 0]);

// Lấy chi tiết sản phẩm
$sql = "SELECT ct.MaSP, sp.TenSP, sp.Gia, ct.SL
        FROM chitietdondathang ct
        JOIN sanpham sp ON ct.MaSP = sp.MaSP
        WHERE ct.MaDonDatHang = '$orderID'";
$items = Database::GetData($sql);

// Lấy người thanh toán (nếu có)
$sql = "SELECT t.TenTaiKhoan, u.TenDayDu
        FROM thanhtoan t
        JOIN users u ON t.TenTaiKhoan = u.TenTaiKhoan
        WHERE t.MaDonDatHang = '$orderID'
        LIMIT 1";
$payer = Database::GetData($sql, ['row' => 0]);
?>

<body>
<div class="container">
    <div class="text-primary text-center pb-5">
        <h4><b>Cửa hàng bán phụ kiện Thế giới oto</b></h4>
        <img class="main-logo" src="<?='/Salonoto/assets/img/logo.png'?>" alt="Logo">
    </div>

    <h3 class="text-primary text-center pb-5"><b>HOÁ ĐƠN</b></h3>

    <!-- Thông tin khách hàng -->
    <div class="pb-5">
        <h5 class="text-primary"><b>THÔNG TIN KHÁCH HÀNG</b></h5>
        <p><b>Họ và tên</b>: <?=$orderUser['Fullname']?></p>
        <p><b>Số điện thoại</b>: <?=$orderUser['Phone']?></p>
        <p><b>Email</b>: <?=$orderUser['Email']?></p>
        <p><b>Người lập</b>: <?= $payer ? $payer['TenDayDu'] : '-' ?></p>
        <p><b>Ngày lập</b>: <?= $orderUser['CreatedAt'] ? Helper::DateTime($orderUser['CreatedAt']) : '-' ?></p>
    </div>

    <!-- Chi tiết đơn hàng -->
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
                        </tr>';
                    }
                } else {
                    echo '<tr><td colspan="100%" class="text-center">Không có dữ liệu</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <div class="text-end">
            <p><b>Tổng tiền:</b> <?=Helper::Currency($orderUser['TongTien'])?></p>
            <p><b>Phí vận chuyển:</b> 0 ₫</p>
            <?php if ($orderUser['TrangThai'] == 'DaHoanThanh') { ?>
                <img style="height: 150px;" src="<?='/Salonoto/assets/img/paid-logo.jpg'?>">
            <?php } ?>
        </div>
    </div>

    <button onclick="window.print();" class="btn">
        <b>Link hoá đơn: </b>
        <a href="<?='https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']?>">
            <?=$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']?>
        </a>
    </button>
</div>
</body>
</html>
